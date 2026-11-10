<?php

namespace App\Http\Requests\Expense;

use App\Models\Account;
use App\Models\PaymentPlatform;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesPaymentSelection
{
    protected function paymentRules(): array
    {
        $userId = $this->user()?->id;
        return [
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'payment_platform_id' => ['nullable', 'integer', Rule::exists('payment_platforms', 'id')->where('user_id', $userId)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->hasAny(['payment_method_id', 'account_id', 'payment_platform_id'])) {
                return;
            }

            $userId = $this->user()->id;
            $methodId = $this->integer('payment_method_id');
            $accountId = $this->integer('account_id');
            $platformId = $this->filled('payment_platform_id') ? $this->integer('payment_platform_id') : null;
            $account = Account::where('user_id', $userId)->find($accountId);

            if (! $account || ! $account->paymentMethods()->whereKey($methodId)->exists()) {
                $validator->errors()->add('account_id', 'Selected account does not support this payment method.');
                return;
            }

            $availablePlatforms = PaymentPlatform::where('user_id', $userId)
                ->where('payment_method_id', $methodId)
                ->where('is_active', true)
                ->whereHas('accounts', fn ($query) => $query->whereKey($accountId));

            if (! $platformId && $availablePlatforms->exists()) {
                $validator->errors()->add('payment_platform_id', 'Select a payment platform for this method and account.');
                return;
            }

            if ($platformId && ! (clone $availablePlatforms)->whereKey($platformId)->exists()) {
                $validator->errors()->add('payment_platform_id', 'Selected platform is not linked to this method and account.');
            }
        });
    }
}
