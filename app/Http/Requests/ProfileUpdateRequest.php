<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile_no' => ['required', 'string', 'digits:10'],
            'email' => [
                'required',
                'string',
                'email',
                'lowercase',
                'max:255',
                Rule::unique('users')->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_no.required' => 'The mobile number is required.',
            'mobile_no.digits' => 'The mobile number must be exactly 10 digits.',
        ];
    }
}