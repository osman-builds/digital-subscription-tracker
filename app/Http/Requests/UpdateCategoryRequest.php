<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function authorize(): Response|bool
    {
        $category = $this->route('category');
        $user = $this->user();

        if (! $category instanceof Category || $user === null) {
            return false;
        }

        return Gate::forUser($user)->inspect('update', $category);
    }

    protected function ignoreCategoryId(): ?int
    {
        $category = $this->route('category');

        return $category instanceof Category ? (int) $category->id : null;
    }
}
