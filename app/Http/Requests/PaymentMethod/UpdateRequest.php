<?php

namespace App\Http\Requests\PaymentMethod;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;


class UpdateRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $paymentMethod = $this->route('paymentMethod');

        if (! auth()->user()->can('update', $paymentMethod)) {
            throw new AuthorizationException(
                'You do not have permission to update PaymentMethod.'
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
        $paymentMethod = $this->route('paymentMethod');

        // If that fails, try to get the ID directly from the route parameters.
        // This works for routes like /categories/{id}
        $paymentMethodId = $paymentMethod ? $paymentMethod->id : $this->route('id');
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('payment_methods')->ignore($paymentMethodId)],
            'code' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Payment method is required.',
            'name.max'      => 'Payment method must not exceed 255 characters.',
            'name.unique'   => 'Payment method is already exists.',
            'code.required' => 'Payment method code is required.',
        ];
    }
}
