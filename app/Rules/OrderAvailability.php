<?php

namespace App\Rules;

use App\Inventory\Inventory;
use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class OrderAvailability implements Rule
{

    protected $stock_id;
    protected $department_id;
    public function __construct($param, $department_id)
    {
        $this->stock_id = $param;
        $this->department_id = $department_id;
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
        $stock_stat = Stock::where(['id' => $this->stock_id])->first();
        if ($stock_stat->inventory_type == "non_inventory") {
            return true;
        }
        $res = Inventory::where('qty', '>=', $value)->where(['stock_id' => $this->stock_id, 'department_id' => $this->department_id, 'branch_id' => Auth::user()->branch_id])->get();
        if ($res->isEmpty()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Stock  has less qty in database';
    }
}
