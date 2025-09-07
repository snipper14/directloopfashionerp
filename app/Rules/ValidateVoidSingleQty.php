<?php

namespace App\Rules;

use App\POS\POSOrder;
use Illuminate\Contracts\Validation\Rule;

class ValidateVoidSingleQty implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $id;
    public function __construct($id)
    {
       $this->id=$id;
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
       $res= POSOrder::where("id",$this->id)->where("qty",">=",$value)->first();
       if($res){
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
        return 'Qty supersedes order amount.';
    }
}
