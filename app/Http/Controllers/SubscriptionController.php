<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subscription;
use App\Services\BrandIconService;
use App\Services\FxRateService;
use App\Services\SandboxCategoryStore;
use App\Services\SandboxSubscriptionStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionController extends Controller
{
    public function __construct(
        private FxRateService $fx,
        private BrandIconService $brandIcons,
        private SandboxCategoryStore $sandboxCategories,
        private SandboxSubscriptionStore $sandboxSubscriptions,
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

    /** Stream subscriptions as a CSV export */
    public function export(): StreamedResponse
    {
        $user = auth()->user();
        $baseCurrency = $user->getSettings()->base_currency ?? 'MYR';
        $subscriptions = $user->isSandbox()
            ? $this->sandboxSubscriptions->all($user->id)->sortBy('name')->values()
            : Subscription::with('category')
                ->where('user_id', $user->id)
                ->orderBy('name')
                ->get();

        $filename = 'subshield-subscriptions-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($subscriptions, $baseCurrency): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Service Name',
                'Website URL',
                'Category',
                'Status',
                'Original Amount',
                'Currency',
                'Billing Cycle',
                "Monthly Equivalent ({$baseCurrency})",
                "Yearly Equivalent ({$baseCurrency})",
                'Start Date',
                'Next Billing Date',
                'Notes',
            ]);

            foreach ($subscriptions as $sub) {
                fputcsv($handle, [
                    $this->sanitizeCsv($sub->name),
                    $this->sanitizeCsv($sub->website_url ?? ''),
                    $this->sanitizeCsv($sub->category?->name ?? 'Uncategorized'),
                    ucfirst((string) $sub->status),
                    number_format((float) $sub->amount, 2, '.', ''),
                    $sub->currency,
                    $sub->billingCycleLabel(),
                    number_format($sub->monthlyAmountIn($baseCurrency), 2, '.', ''),
                    number_format($sub->yearlyAmountIn($baseCurrency), 2, '.', ''),
                    $sub->start_date?->format('Y-m-d') ?? '',
                    $sub->next_billing_date?->format('Y-m-d') ?? '',
                    $this->sanitizeCsv($sub->notes ?? ''),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /** Neutralize CSV formula injection (CWE-1236 / OWASP) */
    private function sanitizeCsv(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
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

            return view('subscriptions.index', compact('subscriptions', 'categories', 'baseCurrency', 'currencySymbol'));
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

        return view('subscriptions.index', compact('subscriptions', 'categories', 'baseCurrency', 'currencySymbol'));
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
        $this->normalizeUrls($request);

        $validated = $request->validate($this->subscriptionRules());
        $validated['user_id'] = auth()->id();
        $validated['amount_myr'] = $this->fx->toMyr((float) $validated['amount'], $validated['currency']);

        if (auth()->user()->isSandbox()) {
            $this->sandboxSubscriptions->create(auth()->id(), $validated);

            return redirect()->route('subscriptions.index')
                ->with('success', "Subscription \"{$validated['name']}\" added (sandbox session only)!");
        }

        Subscription::create($validated);

        return redirect()->route('subscriptions.index')
            ->with('success', "Subscription \"{$validated['name']}\" added!");
    }

    public function show(Subscription $subscription)
    {
        $this->authorize($subscription);
        if (! auth()->user()->isSandbox()) {
            $subscription->load('category');
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
        $this->normalizeUrls($request);

        $validated = $request->validate($this->subscriptionRules());
        $validated['amount_myr'] = $this->fx->toMyr((float) $validated['amount'], $validated['currency']);

        if (auth()->user()->isSandbox()) {
            $this->sandboxSubscriptions->update(auth()->id(), $subscription, $validated);

            return redirect()->route('subscriptions.index')
                ->with('success', "Subscription \"{$validated['name']}\" updated (sandbox session only)!");
        }

        $subscription->update($validated);

        return redirect()->route('subscriptions.index')
            ->with('success', "Subscription \"{$subscription->name}\" updated!");
    }

    public function destroy(Subscription $subscription)
    {
        $this->authorize($subscription);
        $name = $subscription->name;

        if (auth()->user()->isSandbox()) {
            $this->sandboxSubscriptions->delete(auth()->id(), $subscription);

            return redirect()->route('subscriptions.index')
                ->with('success', "Subscription \"{$name}\" deleted (sandbox session only).");
        }

        $subscription->delete();

        return redirect()->route('subscriptions.index')
            ->with('success', "Subscription \"{$name}\" deleted.");
    }

    // -------------------------------------------------------------------------

    /** Normalize website URL and auto-fill logo URL if needed */
    private function normalizeUrls(Request $request): void
    {
        $this->normalizeWebsiteUrl($request);
        $this->inferBrandDetailsFromName($request);
        $this->inferNameFromWebsite($request);
    }

    private function normalizeWebsiteUrl(Request $request): void
    {
        if (! $request->filled('website_url')) {
            return;
        }

        $url = trim((string) $request->input('website_url'));
        if (! preg_match('~^(?:[a-z][a-z0-9+.-]*://|javascript:|data:|vbscript:)~i', $url)) {
            $url = 'https://'.$url;
            $request->merge(['website_url' => $url]);
        }

        if (! $request->filled('logo_url')) {
            $cleanHost = $this->brandIcons->cleanHost($url);
            if ($cleanHost !== '') {
                $request->merge([
                    'logo_url' => $this->brandIcons->googleFaviconUrl($cleanHost),
                ]);
            }
        }
    }

    private function inferBrandDetailsFromName(Request $request): void
    {
        if (! $request->filled('name') || $request->filled('website_url')) {
            return;
        }

        $matched = $this->brandIcons->matchByName((string) $request->input('name'));

        if ($matched !== null) {
            $domain = $matched['domain'];
            $request->merge([
                'website_url' => "https://{$domain}",
                'logo_url' => $this->brandIcons->googleFaviconUrl($domain),
            ]);
        }
    }

    private function inferNameFromWebsite(Request $request): void
    {
        if ($request->filled('name') || ! $request->filled('website_url')) {
            return;
        }

        $host = parse_url((string) $request->input('website_url'), PHP_URL_HOST) ?? '';
        $cleanHost = preg_replace('/^www\./', '', (string) $host);
        $parts = explode('.', $cleanHost);
        $derivedName = ucfirst($parts[0] ?? 'Subscription');
        $request->merge(['name' => $derivedName]);
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
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,semi_annual,annual',
            'start_date' => 'required|date',
            'next_billing_date' => 'required|date',
            'status' => 'required|in:active,inactive,cancelled',
            'category_id' => ['nullable', $categoryRule],
            'website_url' => 'nullable|url:http,https|max:255',
            'logo_url' => 'nullable|url:http,https|max:255',
            'notes' => 'nullable|string|max:500',
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
