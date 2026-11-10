<?php

namespace App\Http\Requests;

use App\Rules\MobileNo;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseFormRequest
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
        $userId = $this->route('user')?->id ?? $this->user()?->id;
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'lowercase', 'max:255', Rule::unique(User::class, 'email')->ignore($userId)],
            'mobile_no' => ['required', 'string',  'max:15', new MobileNo, Rule::unique(User::class, 'mobile_no')->ignore($userId)],
            'password' => ['sometimes', 'nullable', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id']
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Please enter yaaour name.',
            'name.string'         => 'Name must be a valid string.',
            'name.max'            => 'Name cannot exceed 255 characters.',

            'email.required'      => 'Email is required.',
            'email.string'        => 'Email must be a valid string.',
            'email.email'         => 'Please provide a valid email address.',
            'email.lowercase'     => 'Email must be in lowercase.',
            'email.max'           => 'Email may not be greater than 255 characters.',
            'email.unique'        => 'This email address is already registered.',

            'mobile_no.required'  => 'Mobile number is required.',
            'mobile_no.string'    => 'Mobile number must be a valid string.',
            'mobile_no.max'       => 'Mobile number may not exceed 15 digits.',
            'mobile_no.unique'    => 'This mobile number is already registered.',

            'password.required'   => 'Password is required.',
            'roles.*.exists'      => 'The selected role :input does not exist.'
        ];
    }
}
