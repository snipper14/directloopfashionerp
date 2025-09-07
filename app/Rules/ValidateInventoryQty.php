<?php

namespace App\Rules;

use App\Stock\Stock;
use App\Inventory\Inventory;
use Illuminate\Contracts\Validation\Rule;

class ValidateInventoryQty implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
    { $stock_id = $value;
        if($this->isInventory( $stock_id)){

        $qty = $this->request->material_qty;
        $raw_material_department_id = $this->request->raw_material_department_id;

       

        $res = Inventory::where([
            'stock_id' => $stock_id,
            'department_id' => $raw_material_department_id
        ])->where('qty', '>=', $qty)->first();
        //  print_r(DB::getQueryLog());
        if ($res) {
            return true;
        } else {
            return false;
        }
    }else{return true;}
        // return $res ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Quantity  is less in stock';
    }
    function isInventory($stock_id){
        $rs = Stock::where('id', $stock_id)->first();
        if ($rs->inventory_type == "inventory") {
           
            return true;
        }else{
           
            return false;
        }
    }
}
