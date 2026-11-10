<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class Store extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id'      => ['nullable', 'integer', 'exists:categories,id'],
            'name'           => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->where(fn ($q) => $q->where('parent_id', $this->input('parent_id'))),
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

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category already exists under the selected parent.',
            'parent_id.exists' => 'Selected parent category does not exist.',
        ];
    }
}
