<?php

namespace App\Rules;

use App\Stock\Stock;
use App\Inventory\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateIssueStoreQty implements Rule
{
    protected $stock_id;
    protected $department_id;
    protected $source_department_id;
    public function __construct($param1, $product_department_id)
    {
        $this->stock_id = $param1;
        $this->source_department_id = $product_department_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {  DB::enableQueryLog();
        $res = Inventory::where('qty', '>=', $value)->where(['department_id'=>$this->source_department_id,'stock_id' => $this->stock_id])->get();
       // print_r(DB::getQueryLog());
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
        return ':attribute  has less stock in database';
    }
}
