<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'base_currency' => 'required|string|size:3|alpha:ascii',
            'monthly_budget' => 'nullable|numeric|min:0|max:999999.99',
            'budget_alert_percent' => 'nullable|integer|min:50|max:100',
            'alert_7_days' => 'nullable|boolean',
            'alert_3_days' => 'nullable|boolean',
        ];
    }
}
