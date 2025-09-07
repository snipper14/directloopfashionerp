<?php

namespace App\Rules;

use App\dept\Department;
use App\Inventory\Inventory;
use App\PurchaseOrder\PurchaseOrderReceivable;
use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidateStockAvailability implements Rule
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
       
        
     
        $department_id  =   Department::where('show_pos', 1)->first()->id;

        $qty = $this->request->order_data_offline[$index]['qty'];
        $this->item_name = $this->request->order_data_offline[$index]['product_name'];
        $stock_id = $value;
        $stock_stat = Stock::where(['id' => $stock_id])->first();
        if(!$stock_stat) return false;
        if ($stock_stat->inventory_type == "non_inventory") {
            return true;
        }
        $res = Inventory::where(['stock_id' => $stock_id, 'department_id' => $department_id])->where('qty', '>=', $qty)->first();
        if ($res) {
            return true;
        } else {
            if (Auth::user()->branch->enforce_pos_qty == 1) {
                return false;
            } else {
                return true;
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
