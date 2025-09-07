<?php

namespace App\Rules;

use App\POS\RoomOrder;
use Illuminate\Contracts\Validation\Rule;

class ValidateDeleteRoomServiceOrderPrinted implements Rule
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
        $res = RoomOrder::where([
            'orderPrinted' => '1',
            'id' => $value,
           
        ])

            ->get();
        if ($res->isEmpty()) {
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
        return 'Cannot delete printed orders.';
    }
}
