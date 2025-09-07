<?php

namespace App\Rules;

use App\POS\POSOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateCrementPOSOrderPrintStatus implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $id;
    public function __construct($id)
    {
        $this->id = $id;
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
        $res = POSOrder::where([
            'orderPrinted' => '1',
            'stock_id' => $value,
            'id' => $this->id,
            'branch_id' => Auth::user()->branch_id,
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
        return 'This order has been printed';
    }
}
