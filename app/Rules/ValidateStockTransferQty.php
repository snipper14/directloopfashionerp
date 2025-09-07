<?php

namespace App\Rules;

use App\Stock\Stock;
use App\Inventory\Inventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateStockTransferQty implements Rule
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
        
       
        
     
        $department_id  =   $this->request->department_id;

        $qty = $this->request->qty;
        $stock_id = $value;
        $stock = Stock::where(['id' => $stock_id])->first();
        $this->item_name=$stock->product_name;
        if ($stock->inventory_type == "non_inventory") {
            return true;
        }
        $res = Inventory::where(['stock_id' => $stock_id, 'department_id' => $department_id])->where('qty', '>=', $qty)->first();
        if ($res) {

            return true;
        } else {
            if (Auth::user()->branch->enforce_pos_qty != 1) {
                return true;
            }else{
                return false;
            }
        }
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
