<?php

namespace App\Rules;

use App\Cashier\CashierBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateCashierClosingBalance implements Rule
{
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $res = CashierBalance::where('pending_closing', '1')->where(['user_id' => Auth::user()->id, 'branch_id' => Auth::user()->branch_id])->orderBy('id', 'DESC')->first();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please add opening balance first';
    }
}
