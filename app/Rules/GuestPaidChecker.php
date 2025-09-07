<?php

namespace App\Rules;

use App\Reservation\RoomReservation;
use Illuminate\Contracts\Validation\Rule;

class GuestPaidChecker implements Rule
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

        $res = RoomReservation::where(['id' => $value])->first();

        if ($res->total <= $res->amount_paid) {
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
        return 'Room Has not been paid for';
    }
}
