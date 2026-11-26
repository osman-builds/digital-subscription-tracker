<?php

namespace App\Http\Requests;

use App\Rules\SecurePublicUrl;
use App\Services\SandboxCategoryStore;
use App\Services\SubscriptionUrlNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        app(SubscriptionUrlNormalizer::class)->normalize($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $categoryRule = $user->isSandbox()
            ? Rule::in(app(SandboxCategoryStore::class)->all($user->id)->pluck('id')->all())
            : Rule::exists('categories', 'id')->where(function ($query) use ($user): void {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
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
}
