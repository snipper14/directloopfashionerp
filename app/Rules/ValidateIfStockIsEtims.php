<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;

class ValidateIfStockIsEtims implements Rule
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
       
        
     
       

     
        $this->item_name = $this->request->order_data_offline[$index]['product_name'];
        $stock_id = $value;
        $stock_stat = Stock::where(['id' => $stock_id])->first();
        if ($stock_stat->etims_code ) {
            return true;
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
        return 'The items ' . $this->item_name . ' is not on Etims';
    }
}
