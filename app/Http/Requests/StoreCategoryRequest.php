<?php

namespace App\Http\Requests;

use App\Services\SandboxCategoryStore;
use Closure;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    protected const COLOR_HEX_REGEX = 'regex:/^#[0-9A-Fa-f]{6}$/';

    public function authorize(): Response|bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $userId = (int) $user->id;
        $ignoreId = $this->ignoreCategoryId();

        if ($user->isSandbox()) {
            $nameRule = function (string $attribute, mixed $value, Closure $fail) use ($userId, $ignoreId): void {
                if (app(SandboxCategoryStore::class)->nameExists($userId, (string) $value, $ignoreId)) {
                    $fail('A category with this name already exists.');
                }
            };
        } else {
            $uniqueRule = Rule::unique('categories', 'name')->where(function ($query) use ($userId) {
                return $query->where(function ($q) use ($userId): void {
                    $q->whereNull('user_id')->orWhere('user_id', $userId);
                });
            });

            if ($ignoreId !== null) {
                $uniqueRule->ignore($ignoreId);
            }

            $nameRule = $uniqueRule;
        }

        return [
            'name' => ['required', 'string', 'max:255', $nameRule],
            'color' => ['required', 'string', 'size:7', self::COLOR_HEX_REGEX],
        ];
    }

    protected function ignoreCategoryId(): ?int
    {
        return null;
    }
}
