<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule; // <-- Import the Rule class
use Illuminate\Auth\Access\AuthorizationException;

class MenuUpdateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $menu = $this->route('menu');

        if (! auth()->user()->can('update', $menu)) {
            throw new AuthorizationException(
                'You do not have permission to update menu.'
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
        // Get the menu ID from the route parameter.
        // This assumes your route is defined like /menus/{menu}
        $menuId = $this->route('menu');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Ensure the name is unique, but ignore the current menu being updated.
                Rule::unique('menus')->ignore($menuId),
            ],
            'route' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:menus,id'],
            'order' => ['nullable', 'integer', 'min:0'],
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
            'name.unique' => 'This menu name is already taken.', // <-- Added message for unique rule

            'route.string' => 'The route must be a valid string.',
            'route.max' => 'The route must not exceed 255 characters.',

            'url.string' => 'Please enter a valid URL format (e.g., https://example.com ).',
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
