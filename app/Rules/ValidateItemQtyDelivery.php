<?php

namespace App\Rules;

use App\Invoices\Invoice;
use Illuminate\Contracts\Validation\Rule;

class ValidateItemQtyDelivery implements Rule
{
    protected $input_qty;
    public function __construct($input_qty)
    {
        $this->input_qty = $input_qty;
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
        $invoice_id = $value;
        $res = Invoice::where('id', $invoice_id)->first();
       if($res){
           $invoice_qty=$res->qty;
        if ($invoice_qty >= $this->input_qty) {
            return true;
            // $credit_results = Credit::where('invoice_id', $invoice_id)->get();
            // if (!$credit_results->isEmpty()) {
            //     $total_qty = 0;
            //     foreach ($credit_results as $value) {
            //         $total_qty += $value['qty'];
            //          }
            //          if($total_qty<$invoice_qty){
            //              return true;
            //          }else{
            //              return false;
            //          }
            // } else {
            //     return true;
            // }
        } else {
            return false;
        }
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
        return 'Quantity exceeds invoice';
    }
}
