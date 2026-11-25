<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subscription;
use App\Rules\SecurePublicUrl;
use App\Services\BrandIconService;
use App\Services\FxRateService;
use App\Services\ReportExportService;
use App\Services\SandboxCategoryStore;
use App\Services\SandboxService;
use App\Services\SandboxSubscriptionStore;
use App\Services\SubscriptionLedgerService;
use App\Services\SubscriptionUrlNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionController extends Controller
{
    private const ROUTE_SUBSCRIPTIONS_INDEX = 'subscriptions.index';

    public function __construct(
        private FxRateService $fx,
        private BrandIconService $brandIcons,
        private SandboxCategoryStore $sandboxCategories,
        private SandboxSubscriptionStore $sandboxSubscriptions,
        private SandboxService $sandbox,
        private SubscriptionUrlNormalizer $urlNormalizer,
        private SubscriptionLedgerService $ledger,
    ) {}

    /** Resolve the best available brand icon and metadata for a domain. */
    public function resolveIcon(Request $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->isSandbox() || ! $user->hasRealVerifiedEmail()) {
            return response()->json([
                'message' => 'Live brand icon API lookup is reserved for verified accounts.',
            ], 403);
        }

        $domain = (string) $request->query('domain', '');
        $resolved = $this->brandIcons->resolveForDomain($domain);

        if ($resolved === null) {
            return response()->json(['message' => 'Invalid domain.'], 422);
        }

        return response()->json($resolved);
    }

    /** Stream subscriptions as an arranged CSV spreadsheet export */
    public function export(ReportExportService $exporter): StreamedResponse
    {
        $user = auth()->user();
        $baseCurrency = $user->getSettings()->base_currency ?? 'MYR';
        $subscriptions = $user->isSandbox()
            ? $this->sandboxSubscriptions->all($user->id)->sortBy('name')->values()
            : Subscription::with('category')
                ->where('user_id', $user->id)
                ->orderBy('name')
                ->get();

        return $exporter->streamSubscriptionsCsv($user, $subscriptions, $baseCurrency);
    }

    /** Generate printable PDF report view for subscriptions portfolio */
    public function exportPdf(): View
    {
        $user = auth()->user();
        $settings = $user->getSettings();
        $baseCurrency = $settings->base_currency ?? 'MYR';
        $currencySymbol = $this->fx->symbol($baseCurrency);

        $subscriptions = $user->isSandbox()
            ? $this->sandboxSubscriptions->all($user->id)->sortBy('name')->values()
            : Subscription::with('category')
                ->where('user_id', $user->id)
                ->orderBy('name')
                ->get();

        $activeSubs = $subscriptions->where('status', 'active')->values();
        $totalMonthly = (float) $activeSubs->sum(fn ($s) => $s->monthlyAmountIn($baseCurrency));
        $totalYearly = (float) $activeSubs->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency));
        $activeCount = $activeSubs->count();
        $avgMonthly = $activeCount > 0 ? round($totalMonthly / $activeCount, 2) : 0.0;

        $categoryBreakdown = $activeSubs
            ->groupBy(fn ($s) => $s->category?->name ?? 'Uncategorised')
            ->map(function ($group) use ($baseCurrency, $totalMonthly) {
                $monthly = (float) $group->sum(fn ($s) => $s->monthlyAmountIn($baseCurrency));
                $yearly = (float) $group->sum(fn ($s) => $s->yearlyAmountIn($baseCurrency));
                $share = $totalMonthly > 0 ? round(($monthly / $totalMonthly) * 100, 1) : 0.0;

                return [
                    'name' => $group->first()->category?->name ?? 'Uncategorised',
                    'count' => $group->count(),
                    'monthly' => $monthly,
                    'yearly' => $yearly,
                    'share' => $share,
                    'color' => $group->first()->category?->color ?? '#64748b',
                ];
            })
            ->sortByDesc('monthly');

        return view('subscriptions.export_pdf', compact(
            'user', 'subscriptions', 'activeSubs', 'baseCurrency', 'currencySymbol',
            'totalMonthly', 'totalYearly', 'activeCount', 'avgMonthly', 'categoryBreakdown'
        ));
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $settings = $user->getSettings();
        $baseCurrency = $settings->base_currency ?? 'MYR';
        $currencySymbol = $this->fx->symbol($baseCurrency);

        if ($user->isSandbox()) {
            $subscriptions = $this->sandboxIndexSubscriptions($request, $user->id);
            $categories = $this->sandboxCategories->all($user->id);

            return view(self::ROUTE_SUBSCRIPTIONS_INDEX, compact('subscriptions', 'categories', 'baseCurrency', 'currencySymbol'));
        }

        $query = Subscription::with('category')->forUser($user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $escapedSearch = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], (string) $request->search);
            $query->where('name', 'like', "%{$escapedSearch}%");
        }

        $subscriptions = $query->orderBy('next_billing_date')->paginate(12)->withQueryString();
        $categories = Category::forUser($user->id)->orderBy('name')->get();

        return view(self::ROUTE_SUBSCRIPTIONS_INDEX, compact('subscriptions', 'categories', 'baseCurrency', 'currencySymbol'));
    }

    public function create()
    {
        $categories = $this->accessibleCategories();
        $currencies = $this->currencyList();
        $brandCatalog = $this->brandIcons->catalog();

        return view('subscriptions.create', compact('categories', 'currencies', 'brandCatalog'));
    }

    public function store(Request $request)
    {
        $this->urlNormalizer->normalize($request);

        $validated = $request->validate($this->subscriptionRules());
        $auditId = isset($validated['ai_audit_id']) ? (int) $validated['ai_audit_id'] : null;
        unset($validated['ai_audit_id']);

        $validated['user_id'] = auth()->id();
        $validated['is_trial'] = $request->boolean('is_trial');
        $validated['input_source'] = $validated['input_source'] ?? 'manual';
        $validated['amount_myr'] = $this->fx->toMyr((float) $validated['amount'], $validated['currency']);

        if (auth()->user()->isSandbox()) {
            $this->sandboxSubscriptions->create(auth()->id(), $validated);

            return redirect()->route(self::ROUTE_SUBSCRIPTIONS_INDEX)
                ->with('success', "Subscription \"{$validated['name']}\" added (sandbox session only)!");
        }

        $subscription = Subscription::create($validated);
        $this->ledger->recordEvent($subscription, auth()->user(), $validated['input_source'], $validated['start_date']);
        $this->ledger->linkAudit(auth()->id(), $auditId, $subscription->id, 'applied_new');

        return redirect()->route(self::ROUTE_SUBSCRIPTIONS_INDEX)
            ->with('success', "Subscription \"{$validated['name']}\" added!");
    }

    public function show(Subscription $subscription)
    {
        $this->authorize($subscription);
        if (auth()->user()->isSandbox()) {
            $this->sandbox->hydrateRelations($subscription);
        } else {
            $subscription->load([
                'category',
                'priceHistories' => fn ($query) => $query->latest('recorded_at')->limit(10),
                'billingEvents' => fn ($query) => $query->latest('billed_at')->limit(10),
            ]);
        }

        return view('subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $this->authorize($subscription);
        $categories = $this->accessibleCategories();
        $currencies = $this->currencyList();
        $brandCatalog = $this->brandIcons->catalog();

        return view('subscriptions.edit', compact('subscription', 'categories', 'currencies', 'brandCatalog'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $this->authorize($subscription);
        $this->urlNormalizer->normalize($request);

        $validated = $request->validate($this->subscriptionRules());
        unset($validated['ai_audit_id']);

        $oldAmount = (float) $subscription->amount;
        $oldCurrency = (string) $subscription->currency;

        $validated['is_trial'] = $request->boolean('is_trial');
        $validated['amount_myr'] = $this->fx->toMyr((float) $validated['amount'], $validated['currency']);

        if (auth()->user()->isSandbox()) {
            $this->sandboxSubscriptions->update(auth()->id(), $subscription, $validated);

            return redirect()->route(self::ROUTE_SUBSCRIPTIONS_INDEX)
                ->with('success', "Subscription \"{$validated['name']}\" updated (sandbox session only)!");
        }

        $subscription->update($validated);
        $this->ledger->recordPriceChangeIfChanged(
            $subscription,
            $oldAmount,
            $oldCurrency,
            (float) $validated['amount'],
            (string) $validated['currency'],
            'manual_edit'
        );

        return redirect()->route(self::ROUTE_SUBSCRIPTIONS_INDEX)
            ->with('success', "Subscription \"{$subscription->name}\" updated!");
    }

    public function renew(Subscription $subscription)
    {
        $this->authorize($subscription);

        if (auth()->user()->isSandbox()) {
            $this->sandboxSubscriptions->advanceCycle(auth()->id(), $subscription);

            return back()->with('success', "Renewal cycle advanced for \"{$subscription->name}\" (sandbox session only)!");
        }

        $billedDate = $subscription->next_billing_date?->toDateString() ?? now()->toDateString();
        $subscription->advanceBillingCycle();
        $this->ledger->recordEvent($subscription, auth()->user(), 'auto_rollover', $billedDate);

        return back()->with('success', "Billing cycle advanced for \"{$subscription->name}\"! Next billing date is {$subscription->next_billing_date?->format('M d, Y')}.");
    }

    public function destroy(Subscription $subscription)
    {
        $this->authorize($subscription);
        $name = $subscription->name;

        if (auth()->user()->isSandbox()) {
            $this->sandboxSubscriptions->delete(auth()->id(), $subscription);

            return redirect()->route(self::ROUTE_SUBSCRIPTIONS_INDEX)
                ->with('success', "Subscription \"{$name}\" deleted (sandbox session only).");
        }

        $subscription->delete();

        return redirect()->route(self::ROUTE_SUBSCRIPTIONS_INDEX)
            ->with('success', "Subscription \"{$name}\" deleted.");
    }

    // -------------------------------------------------------------------------

    /** Get categories accessible to current user (session or DB) */
    private function accessibleCategories()
    {
        return auth()->user()->isSandbox()
            ? $this->sandboxCategories->all(auth()->id())
            : Category::forUser(auth()->id())->orderBy('name')->get();
    }

    /** Filter and paginate in-session sandbox subscriptions */
    private function sandboxIndexSubscriptions(Request $request, int $userId): LengthAwarePaginator
    {
        $filtered = $this->sandboxSubscriptions->all($userId);

        if ($request->filled('status')) {
            $statusFilter = $request->string('status')->toString();
            $filtered = $filtered->filter(fn (Subscription $sub): bool => $sub->status === $statusFilter);
        }
        if ($request->filled('category_id')) {
            $categoryFilter = $request->integer('category_id');
            $filtered = $filtered->filter(fn (Subscription $sub): bool => (int) $sub->category_id === $categoryFilter);
        }
        if ($request->filled('search')) {
            $needle = strtolower($request->string('search')->toString());
            $filtered = $filtered->filter(fn (Subscription $sub): bool => str_contains(strtolower($sub->name), $needle));
        }

        $sorted = $filtered->sortBy('next_billing_date')->values();
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;

        return (new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url()]
        ))->withQueryString();
    }

    /** Ensure subscription belongs to authenticated user */
    private function authorize(Subscription $subscription): void
    {
        abort_if($subscription->user_id !== auth()->id(), 403);
    }

    /** Shared validation rules for creating and updating subscriptions */
    private function subscriptionRules(): array
    {
        $categoryRule = auth()->user()->isSandbox()
            ? Rule::in($this->sandboxCategories->all(auth()->id())->pluck('id')->all())
            : Rule::exists('categories', 'id')->where(function ($query) {
                $query->whereNull('user_id')->orWhere('user_id', auth()->id());
            });

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3|alpha:ascii',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,semi_annual,annual',
            'start_date' => 'required|date',
            'next_billing_date' => 'required|date',
            'status' => 'required|in:active,inactive,cancelled',
            'is_trial' => 'nullable|boolean',
            'trial_ends_at' => 'nullable|date',
            'category_id' => ['nullable', $categoryRule],
            'website_url' => ['nullable', 'max:255', new SecurePublicUrl],
            'cancellation_url' => ['nullable', 'max:255', new SecurePublicUrl],
            'logo_url' => ['nullable', 'max:255', new SecurePublicUrl],
            'notes' => 'nullable|string|max:500',
            'input_source' => 'nullable|in:manual,ai_image,ai_pdf',
            'ai_audit_id' => 'nullable|integer',
        ];
    }

    /** Supported currencies for the dropdown */
    private function currencyList(): array
    {
        return [
            'MYR' => 'MYR – Malaysian Ringgit',
            'USD' => 'USD – US Dollar',
            'EUR' => 'EUR – Euro',
            'GBP' => 'GBP – British Pound',
            'SGD' => 'SGD – Singapore Dollar',
            'AUD' => 'AUD – Australian Dollar',
            'JPY' => 'JPY – Japanese Yen',
            'CNY' => 'CNY – Chinese Yuan',
            'HKD' => 'HKD – Hong Kong Dollar',
            'INR' => 'INR – Indian Rupee',
            'CAD' => 'CAD – Canadian Dollar',
            'CHF' => 'CHF – Swiss Franc',
            'KRW' => 'KRW – South Korean Won',
            'TWD' => 'TWD – New Taiwan Dollar',
            'THB' => 'THB – Thai Baht',
            'IDR' => 'IDR – Indonesian Rupiah',
            'PHP' => 'PHP – Philippine Peso',
            'SAR' => 'SAR – Saudi Riyal',
            'AED' => 'AED – UAE Dirham',
            'QAR' => 'QAR – Qatari Riyal',
            'KWD' => 'KWD – Kuwaiti Dinar',
            'EGP' => 'EGP – Egyptian Pound',
            'ZAR' => 'ZAR – South African Rand',
            'NGN' => 'NGN – Nigerian Naira',
            'KES' => 'KES – Kenyan Shilling',
            'SEK' => 'SEK – Swedish Krona',
            'NOK' => 'NOK – Norwegian Krone',
            'DKK' => 'DKK – Danish Krone',
            'NZD' => 'NZD – New Zealand Dollar',
            'TRY' => 'TRY – Turkish Lira',
            'BRL' => 'BRL – Brazilian Real',
            'MXN' => 'MXN – Mexican Peso',
            'PKR' => 'PKR – Pakistani Rupee',
            'BDT' => 'BDT – Bangladeshi Taka',
        ];
    }
}
