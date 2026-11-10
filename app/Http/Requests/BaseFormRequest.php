<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{
    /**
     * Handle failed validation for ALL child requests.
     */
    protected function failedValidation(Validator $validator)
    {
        // If the request expects JSON (AJAX, fetch(), axios)
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422)
            );
        }

        // Otherwise use Laravel's normal behavior (redirect back with errors)
        parent::failedValidation($validator);
    }
}
