<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Services\FxRateService;
use App\Services\ReceiptScannerService;
use App\Services\SandboxCategoryStore;
use App\Services\SandboxSubscriptionStore;
use App\Services\SubscriptionLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    ) {}

    /**
     * Statelessly scan an uploaded receipt image or PDF invoice in RAM without disk persistence.
     */
    public function scan(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->isSandbox()) {
            $request->validate([
                'receipt' => ['nullable', 'file', 'max:4096', 'mimes:jpg,jpeg,png,webp,pdf'],
            ]);

            return response()->json(
                $this->scanner->simulateSandboxScan(
                    $user,
                    $this->sandboxCategories->all($user->id),
                    $this->sandboxSubscriptions->active($user->id)
                )
            );
        }

        if (! $user->hasRealVerifiedEmail()) {
            return response()->json([
                'message' => 'Live AI receipt scanning is reserved for verified accounts.',
            ], 403);
        }

        return $this->executeLiveScan($request, $user);
    }

    private function executeLiveScan(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'receipt' => ['required', 'file', 'max:4096', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

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
    public function applyToExisting(Request $request, Subscription $subscription): RedirectResponse
    {
        $user = auth()->user();
        abort_if($subscription->user_id !== $user->id, 403);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,semi_annual,annual',
            'next_billing_date' => 'required|date',
            'ai_audit_id' => 'nullable|integer',
            'input_source' => 'nullable|in:ai_image,ai_pdf',
        ]);

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
