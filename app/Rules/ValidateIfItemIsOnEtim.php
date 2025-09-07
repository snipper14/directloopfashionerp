<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;

class ValidateIfItemIsOnEtim implements Rule
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







        $stock_id = $value;
        $stock_stat = Stock::where(['id' => $stock_id])->first();
        $this->item_name=$stock_stat->product_name;
        if ($stock_stat->item_id) {
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
        return '' . $this->item_name . ' is not registered on Etims';
    }
}
