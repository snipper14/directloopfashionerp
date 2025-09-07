<?php

namespace App\Rules;

use App\Register\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateRegisterActiveBeforeClosingShift implements Rule
{
  /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $request;
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
      //  $res = Register::where(['user_id' => Auth::user()->id, 'status' => 'closed'])->first();
      
      if(Auth::user()->branch->cashier_options=='consolidated'){
    
        $res = Register::where([ 'user_id' => Auth::user()->id,'status' => 'opened'])->first();
        
        $status = false;
        if ($res) {
            $status = true;
        }
        return $status;
    }else{
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
        return 'You need to open register first';
    }
}
