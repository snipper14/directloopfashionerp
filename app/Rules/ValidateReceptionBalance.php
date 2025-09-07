<?php

namespace App\Rules;

use App\Reception\ReceptionBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateReceptionBalance implements Rule
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
        $res = ReceptionBalance::where('pending_closing', '1')->where(['user_id' => Auth::user()->id, 'branch_id' => Auth::user()->branch_id])->get();
        if (!$res->isEmpty()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'There is another pending opening balance ';
    }
}
