<?php

namespace App\Rules;

use App\Customer\Customer;
use App\Ledger\CustomerLedger;
use Illuminate\Contracts\Validation\Rule;

class ValidatePOSCreditLimit implements Rule
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
        $data = $this->request;

     

        $cust_balance = CustomerLedger::fetchCustomerBalance($data['customer_id']);
        $credit_limit = Customer::getCreditAmount($data['customer_id']);
       $balance=$value+$cust_balance;
       
        if ($credit_limit > 0) {
            if (($balance - $credit_limit) > 0) {
                return false;
            } else {
                return true;
            }
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
        return 'The customer has surpassed their credit limit';
    }
}
