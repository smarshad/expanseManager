<?php

namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;
class MobileNo implements Rule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function passes($attribute, $value)
    {
        return preg_match('/^\d{10}$/', $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid 10-digit mobile number.';
    }
}
