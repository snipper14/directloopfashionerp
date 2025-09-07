<?php

namespace App\Rules;

use App\Inventory\Inventory;
use Illuminate\Contracts\Validation\Rule;

class ValidateParentStockAvailabilityDisassemblyProduct implements Rule
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
    {

      //  $index = explode('.', $attribute)[1];
        $qty = $this->request->parent_qty;
        $parent_department_id=$this->request->parent_department_id;
      
        $stock_id = $value;
     
        $res = Inventory::where([
            'stock_id'=> $stock_id,
        'department_id'=>$parent_department_id])->where('qty', '>=', $qty)->first();
      //  print_r(DB::getQueryLog());
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
        return 'Parent Quantity  is less in stock';
    }
}
