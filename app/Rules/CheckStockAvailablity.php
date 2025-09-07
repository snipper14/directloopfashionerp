<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class CheckStockAvailablity implements Rule
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
        $stock_id=$value;
        $avail_qty=Stock::where(['id'=>$stock_id,'branch_id'=>Auth::user()->branch_id])->first();
        if($avail_qty){
        $avail_qty=$avail_qty->qty;
        if($avail_qty>=$this->qty){
            return true;
        }else{
            return false;
        }
    }else{
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
        return 'Quantity not enough in stock.';
    }
}
