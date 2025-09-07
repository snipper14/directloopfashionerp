<?php

namespace App\Rules;

use App\POS\POSOrder;
use Illuminate\Contracts\Validation\Rule;

class ValidateOrderComplete implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $order_no = $value;
        $res = POSOrder::where(['isComplete' => '0', 'order_no' => $order_no])->get();
      
        if (!$res->isEmpty()) {
              //found incomplete
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
        return 'The order has not been completed.';
    }
}
