<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subscription;
use App\Services\FxRateService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function __construct(private FxRateService $fx) {}

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
        ];
    }
}
