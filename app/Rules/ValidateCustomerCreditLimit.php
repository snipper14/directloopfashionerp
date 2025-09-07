<?php

namespace App\Rules;

use App\Cart\OrderCart;
use App\Customer\Customer;
use App\Ledger\CustomerLedger;
use Illuminate\Contracts\Validation\Rule;

class ValidateCustomerCreditLimit implements Rule
{
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
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
        $data = $this->data;

        $row_total = $data['row_total'];

        $cust_balance = CustomerLedger::fetchCustomerBalance($data['customer_id']);
        $credit_limit = Customer::getCreditAmount($data['customer_id']);
        $cart_res = OrderCart::where(['customer_id' => $data['customer_id']])->selectRaw('sum(row_total) as total_cart')->first();
        $cart_total = 0;
        if ($cart_res->total_cart) {
            $cart_total = $cart_res->total_cart;
        }
        $balance = $cust_balance + $row_total + $cart_total;
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
