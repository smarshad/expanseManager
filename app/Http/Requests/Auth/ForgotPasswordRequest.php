<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'email' => ['required', 'email']
        ];
    }

   /**
    * Get Custom messages for validation errors
    */
    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email.',
            'email.email'  => 'Please enter valid email.',
        ];
    }
}
