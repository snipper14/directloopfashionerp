<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;

class ValidateMainStoreStockQtyAvailability implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $qty;
    public function __construct($qty)
    {
       $this->qty=$qty;
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
        $stock_id = $value;
        $res = Stock::where(['id' => $stock_id])->where('main_store_qty', '>=', $this->qty)->first();
        return $res ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Less Stock Available.';
    }
}
