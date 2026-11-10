<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StorePermissionRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'name' => [
                'required',
                'string',
                'max:25',
                Rule::unique('permissions')
                    ->where('guard_name', $this->input('guard_name', 'web'))
                    ->where('module', $this->input('module'))
            ],
            'module' => ['required', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:55'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The permission name is required.',
            'name.max' => 'The permission name may not be greater than 25 characters.',
            'module.required' => 'The module name is required.',
            'module.max' => 'The module name may not be greater than 255 characters.',
        ];
    }
}
