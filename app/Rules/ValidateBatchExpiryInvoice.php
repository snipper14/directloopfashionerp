<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class ValidateBatchExpiryInvoice implements Rule
{
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
    { $res=Stock::where(['id'=>$value])->first();
        $stock_id = $value;
      
        $batch_no = $this->request->batch_no;
        $this->item_name = $res->name;
       
        if ($batch_no) {
            $_expiry_query = PurchaseOrderReceivableWithBatch::where(['batch_no'=> $batch_no,'stock_id'=>$stock_id])->first();
           
            if ($_expiry_query) {
               
                $current_expiry = $_expiry_query->expiry_date;
            
           
                $batch_query = PurchaseOrderReceivableWithBatch::whereColumn('qty_sold', '<', 'qty_delivered')
                    ->where('batch_no', '<>', $batch_no)
                    ->where('expiry_date', '<', $current_expiry)
                    ->where(['stock_id' => $stock_id])->get();
                   
                if ($batch_query->isEmpty()) {
                    return true;
                } else {

                    return false;
                }
            } else {
                return false;
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
        return ' There is Another batch that will expire sooner for  ' . $this->item_name;
    }
}
