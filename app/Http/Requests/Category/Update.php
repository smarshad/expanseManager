<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class Update extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category?->id;

        return [
            'parent_id'      => ['nullable', 'integer', 'exists:categories,id', Rule::notIn([$categoryId])],
            'name'           => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')
                    ->ignore($categoryId)
                    ->where(fn ($q) => $q->where('parent_id', $this->input('parent_id'))),
            ],
            'description'    => ['nullable', 'string'],
            'color_code'     => ['nullable', 'string', 'max:7'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'budget_type'    => ['required', Rule::in(['no_budget', 'soft_budget', 'hard_budget', 'fixed'])],
            'monthly_budget' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'nature'         => ['required', Rule::in(['need', 'want', 'investment'])],
            'is_active'      => ['nullable', 'boolean'],
        ];
    }
}
