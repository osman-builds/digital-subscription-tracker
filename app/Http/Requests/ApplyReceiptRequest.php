<?php

namespace App\Http\Requests;

use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;

class ApplyReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $subscription = $this->route('subscription');

        if (! $subscription instanceof Subscription) {
            return false;
        }

        return $this->user()?->can('update', $subscription) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3|alpha:ascii',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,semi_annual,annual',
            'next_billing_date' => 'required|date',
            'ai_audit_id' => 'nullable|integer',
            'input_source' => 'nullable|in:ai_image,ai_pdf',
        ];
    }
}
