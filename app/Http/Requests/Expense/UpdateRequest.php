<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    use ValidatesPaymentSelection;

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return array_merge([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'expense_for_id' => ['required', 'integer', 'exists:expense_fors,id'],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')->where('user_id', $this->user()?->id)],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'cashback' => ['nullable', 'numeric', 'min:0'],
            'description' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_recurring' => ['nullable', 'boolean'],
            'frequency' => ['nullable', Rule::requiredIf($this->boolean('is_recurring')), Rule::in(['daily', 'occasionally', 'weekly', 'monthly', 'quarterly', 'yearly'])],
            'nature' => ['nullable', Rule::in(['need', 'want', 'investment'])],
            'transaction_kind' => ['required', Rule::in(['expense', 'investment', 'transfer', 'debt'])],
            'uploaded_file' => ['nullable', 'file', 'mimes:pdf,jpg,png,jpeg', 'max:10240'],
            'file_path' => ['nullable', 'string', 'max:255'],
        ], $this->paymentRules());
    }

    public function messages(): array
    {
        return ['account_id.required' => 'Account is required.', 'payment_method_id.required' => 'Payment method is required.'];
    }
}
