<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;

class ValidateOrderStockAvailability implements Rule
{
    protected $request;
    protected $item_name;
    public function __construct($request)
    {
        $this->request = $request;
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

        $index = explode('.', $attribute)[1];
        $qty = $this->request->sales_orders[$index]['qty'];
        $this->item_name = $this->request->sales_orders[$index]['stock']['product_name'];
        $stock_id = $value;

        $res = Stock::where('id', $stock_id)->where('qty', '>=', $qty)->first();
        if ($res) {
            return true;
        } else {
            return false;
        }
        // return $res ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Quantity of ' . $this->item_name . ' is less in stock';
    }
}
