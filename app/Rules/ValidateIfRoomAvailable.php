<?php

namespace App\Rules;

use App\Reservation\RoomReservation;
use App\Rooms\Room;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidateIfRoomAvailable implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */



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
        $res = Room::where(['occupation' => 'empty', 'id' => $value,])
            // ->where('from', '>=', $this->from)->where('to', '<=', $value)->
            // where('to', '>=', $this->from)
            ->get();
        if ($res->isEmpty()) {
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
        return 'The room is Reserved for the period.';
    }
}
