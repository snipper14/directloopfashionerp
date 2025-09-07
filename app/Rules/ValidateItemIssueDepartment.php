<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateItemIssueDepartment implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $department_id;
    public function __construct($department_id)
    {
        $this->department_id=$department_id;
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
        if($this->department_id==$value){
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
        return 'You are trying to issue Item to wrong department';
    }
}
