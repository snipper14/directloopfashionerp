<?php

namespace App\Rules;

use App\Online\OnlineOrders;
use Illuminate\Contracts\Validation\Rule;

class VlidateVoidSingleOnline implements Rule
{
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
       $res= OnlineOrders::where("id",$this->id)->where("qty",">=",$value)->first();
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
