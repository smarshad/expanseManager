<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;

class MenuStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! auth()->user()->can('create', Menu::class)) {
            throw new AuthorizationException(
                'You do not have permission to create menu.'
            );
        }
    
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:menus,id'],
            'order' => ['nullable', 'integer', 'min:0'],
            // Assuming 'permissions' table exists
            'permission_id' => ['nullable', 'exists:permissions,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The menu name is required.',
            'name.string' => 'The menu name must be a valid string.',
            'name.max' => 'The menu name must not exceed 255 characters.',

            'route.string' => 'The route must be a valid string.',
            'route.max' => 'The route must not exceed 255 characters.',

            // 'url.url' => 'Please enter a valid URL format (e.g., https://example.com).',
            'url.string' => 'Please enter a valid URL format (e.g., https://example.com).',
            'url.max' => 'The URL must not exceed 255 characters.',

            'icon.string' => 'The icon must be a valid string.',
            'icon.max' => 'The icon must not exceed 100 characters.',

            'parent_id.exists' => 'The selected parent menu does not exist.',

            'order.integer' => 'The order must be an integer number.',
            'order.min' => 'The order must be at least 0.',

            'permission_id.exists' => 'The selected permission does not exist.',

            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }
}
