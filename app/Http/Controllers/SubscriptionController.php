<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subscription;
use App\Services\FxRateService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriptionController extends Controller
{
    public function __construct(private FxRateService $fx) {}

    /** Stream subscriptions as a CSV export */
    public function export(): StreamedResponse
    {
        $user = auth()->user();
        $baseCurrency = $user->getSettings()->base_currency ?? 'MYR';
        $subscriptions = Subscription::with('category')
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

        $query = Subscription::with('category')->forUser($user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $subscriptions = $query->orderBy('next_billing_date')->paginate(12)->withQueryString();
        $categories = Category::forUser($user->id)->orderBy('name')->get();

        return view('subscriptions.index', compact('subscriptions', 'categories', 'baseCurrency', 'currencySymbol'));
    }

    public function create()
    {
        $categories = Category::forUser(auth()->id())->orderBy('name')->get();
        $currencies = $this->currencyList();

        return view('subscriptions.create', compact('categories', 'currencies'));
    }

    public function store(Request $request)
    {
        $this->normalizeUrls($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,semi_annual,annual',
            'start_date' => 'required|date',
            'next_billing_date' => 'required|date',
            'status' => 'required|in:active,inactive,cancelled',
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->whereNull('user_id')->orWhere('user_id', auth()->id());
                }),
            ],
            'website_url' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['amount_myr'] = $this->fx->toMyr((float) $validated['amount'], $validated['currency']);

        Subscription::create($validated);

        return redirect()->route('subscriptions.index')
            ->with('success', "Subscription \"{$validated['name']}\" added!");
    }

    public function show(Subscription $subscription)
    {
        $this->authorize($subscription);
        $subscription->load('category');

        return view('subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $this->authorize($subscription);
        $categories = Category::forUser(auth()->id())->orderBy('name')->get();
        $currencies = $this->currencyList();

        return view('subscriptions.edit', compact('subscription', 'categories', 'currencies'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $this->authorize($subscription);
        $this->normalizeUrls($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,semi_annual,annual',
            'start_date' => 'required|date',
            'next_billing_date' => 'required|date',
            'status' => 'required|in:active,inactive,cancelled',
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->whereNull('user_id')->orWhere('user_id', auth()->id());
                }),
            ],
            'website_url' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['amount_myr'] = $this->fx->toMyr((float) $validated['amount'], $validated['currency']);

        $subscription->update($validated);

        return redirect()->route('subscriptions.index')
            ->with('success', "Subscription \"{$subscription->name}\" updated!");
    }

    public function destroy(Subscription $subscription)
    {
        $this->authorize($subscription);
        $name = $subscription->name;
        $subscription->delete();

        return redirect()->route('subscriptions.index')
            ->with('success', "Subscription \"{$name}\" deleted.");
    }

    // -------------------------------------------------------------------------

    /** Normalize website URL and auto-fill logo URL if needed */
    private function normalizeUrls(Request $request): void
    {
        if ($request->filled('website_url')) {
            $url = trim((string) $request->input('website_url'));
            if (! preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://'.$url;
                $request->merge(['website_url' => $url]);
            }

            if (! $request->filled('logo_url')) {
                $host = parse_url($url, PHP_URL_HOST) ?? '';
                $cleanHost = preg_replace('/^www\./', '', (string) $host);
                if ($cleanHost) {
                    $request->merge([
                        'logo_url' => "https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://{$cleanHost}&size=128",
                    ]);
                }
            }
        }

        if ($request->filled('name') && ! $request->filled('website_url')) {
            $nameLower = strtolower(trim((string) $request->input('name')));
            $brandMap = [
                'netflix' => 'netflix.com',
                'spotify' => 'spotify.com',
                'youtube' => 'youtube.com',
                'github' => 'github.com',
                'chatgpt' => 'openai.com',
                'openai' => 'openai.com',
                'claude' => 'anthropic.com',
                'apple' => 'apple.com',
                'icloud' => 'apple.com',
                'adobe' => 'adobe.com',
                'canva' => 'canva.com',
                'figma' => 'figma.com',
                'notion' => 'notion.so',
                'disney' => 'disneyplus.com',
                'amazon' => 'amazon.com',
                'google' => 'google.com',
                'microsoft' => 'microsoft.com',
                'discord' => 'discord.com',
                'duolingo' => 'duolingo.com',
                'slack' => 'slack.com',
                'zoom' => 'zoom.us',
                'dropbox' => 'dropbox.com',
                'linkedin' => 'linkedin.com',
            ];

            foreach ($brandMap as $brand => $domain) {
                if (str_contains($nameLower, $brand)) {
                    $url = "https://{$domain}";
                    $request->merge([
                        'website_url' => $url,
                        'logo_url' => "https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://{$domain}&size=128",
                    ]);
                    break;
                }
            }
        }

        if (! $request->filled('name') && $request->filled('website_url')) {
            $host = parse_url((string) $request->input('website_url'), PHP_URL_HOST) ?? '';
            $cleanHost = preg_replace('/^www\./', '', (string) $host);
            $parts = explode('.', $cleanHost);
            $derivedName = ucfirst($parts[0] ?? 'Subscription');
            $request->merge(['name' => $derivedName]);
        }
    }

    // -------------------------------------------------------------------------

    /** Ensure subscription belongs to authenticated user */
    private function authorize(Subscription $subscription): void
    {
        abort_if($subscription->user_id !== auth()->id(), 403);
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
