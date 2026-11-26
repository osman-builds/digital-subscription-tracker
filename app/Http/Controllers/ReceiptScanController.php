<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyReceiptRequest;
use App\Http\Requests\ScanReceiptRequest;
use App\Models\Subscription;
use App\Models\User;
use App\Services\FxRateService;
use App\Services\ReceiptScannerService;
use App\Services\SandboxCategoryStore;
use App\Services\SandboxService;
use App\Services\SandboxSubscriptionStore;
use App\Services\SubscriptionLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReceiptScanController extends Controller
{
    public function __construct(
        private readonly ReceiptScannerService $scanner,
        private readonly SubscriptionLedgerService $ledger,
        private readonly FxRateService $fx,
        private readonly SandboxCategoryStore $sandboxCategories,
        private readonly SandboxSubscriptionStore $sandboxSubscriptions,
        private readonly SandboxService $sandbox,
    ) {}

    /**
     * Statelessly scan an uploaded receipt image or PDF invoice in RAM without disk persistence.
     */
    public function scan(ScanReceiptRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSandbox()) {
            return response()->json(
                $this->scanner->simulateSandboxScan(
                    $user,
                    $this->sandboxCategories->all($user->id),
                    $this->sandboxSubscriptions->active($user->id)
                )
            );
        }

        return $this->executeLiveScan($request, $user);
    }

    private function executeLiveScan(ScanReceiptRequest $request, User $user): JsonResponse
    {
        if (empty(config('services.gemini.key'))) {
            return response()->json([
                'message' => 'AI receipt scanning is not configured on this environment yet.',
            ], 503);
        }

        try {
            $result = $this->scanner->scan($request->file('receipt'), $user);

            return response()->json($result);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('ReceiptScanner failed', [
                'user_id' => $user->id,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to analyze receipt right now. Please verify the file is legible or fill in details manually.',
            ], 503);
        }
    }

    /**
     * Apply human-verified receipt details onto an existing subscription and log ledger/price history.
     */
    public function applyToExisting(ApplyReceiptRequest $request, Subscription $subscription): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $currency = strtoupper($validated['currency']);
        $newAmount = (float) $validated['amount'];
        $inputSource = $validated['input_source'] ?? 'ai_pdf';
        $amountMyr = $this->fx->toMyr($newAmount, $currency);

        if ($user->isSandbox()) {
            $this->sandboxSubscriptions->update($user->id, $subscription, [
                'amount' => $newAmount,
                'currency' => $currency,
                'amount_myr' => $amountMyr,
                'billing_cycle' => $validated['billing_cycle'],
                'next_billing_date' => $validated['next_billing_date'],
                'input_source' => $inputSource,
            ]);

            $oldAmount = (float) $subscription->amount;
            if ($oldAmount > 0 && abs($oldAmount - $newAmount) >= 0.01) {
                $this->sandbox->recordPriceHistory(
                    $user->id,
                    (int) $subscription->id,
                    $oldAmount,
                    $newAmount,
                    $currency,
                    SandboxService::SOURCE_AI_SCAN
                );
            }

            $this->sandbox->recordBillingEvent(
                $user->id,
                (int) $subscription->id,
                $newAmount,
                $currency,
                $validated['billing_cycle'],
                SandboxService::SOURCE_AI_SCAN
            );

            return redirect()->route('subscriptions.show', $subscription)
                ->with('success', "Verified receipt applied to \"{$subscription->name}\" (sandbox session only)!");
        }

        $oldAmount = (float) $subscription->amount;
        $oldCurrency = (string) $subscription->currency;

        $subscription->update([
            'amount' => $newAmount,
            'currency' => $currency,
            'amount_myr' => $amountMyr,
            'billing_cycle' => $validated['billing_cycle'],
            'next_billing_date' => $validated['next_billing_date'],
            'input_source' => $inputSource,
        ]);

        $this->ledger->recordPriceChangeIfChanged(
            $subscription,
            $oldAmount,
            $oldCurrency,
            $newAmount,
            $currency,
            'ai_receipt'
        );
        $this->ledger->recordEvent($subscription, $user, 'ai_receipt', now()->toDateString());
        $this->ledger->linkAudit(
            $user->id,
            isset($validated['ai_audit_id']) ? (int) $validated['ai_audit_id'] : null,
            $subscription->id,
            'applied_update'
        );

        return redirect()->route('subscriptions.show', $subscription)
            ->with('success', "Verified receipt applied to \"{$subscription->name}\" and logged in billing ledger!");
    }
}
