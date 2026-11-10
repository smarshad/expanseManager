<?php

namespace App\Http\Requests\PaymentMethod;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Validator;
use App\Models\PaymentMethod;
use Illuminate\Auth\Access\AuthorizationException;

class StoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! auth()->user()->can('create', PaymentMethod::class)) {
            throw new AuthorizationException(
                'You do not have permission to create PaymentMethod.'
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
            'code' => ['required', 'string'],
        ];
    }


    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $name = $this->input('name');

            if ($name) {
                // Check if a soft-deleted record exists
                $deletedRecord = PaymentMethod::onlyTrashed()
                    ->where('name', $name)
                    ->first();

                if ($deletedRecord) {
                    $validator->errors()->add(
                        'name',
                        'This payment method already exists but has been deleted. Please restore it instead of creating a new one.'
                    );
                }

                // Check if an active record exists
                $exists = PaymentMethod::where('name', $name)->exists();
                if ($exists) {
                    $validator->errors()->add(
                        'name',
                        'This payment method name is already in use.'
                    );
                }
            }
        });
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
