<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;

class ScanReceiptRequest extends FormRequest
{
    public function authorize(): Response|bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        if (! $user->isSandbox() && ! $user->hasRealVerifiedEmail()) {
            return Response::deny('Live AI receipt scanning is reserved for verified accounts.');
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $presence = $this->user()?->isSandbox() ? 'nullable' : 'required';

        return [
            'receipt' => [$presence, 'file', 'max:4096', 'mimes:jpg,jpeg,png,webp,pdf'],
        ];
    }
}
