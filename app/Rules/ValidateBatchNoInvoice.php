<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class ValidateBatchNoInvoice implements Rule
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
    {$res=Stock::where(['id'=>$value])->first();

       
        $batch_no = $this->request->batch_no;
        $this->item_name = $res->name;
        if ($batch_no) {
            $batch_query = PurchaseOrderReceivableWithBatch::where(['batch_no' => $batch_no,'stock_id'=>$value])->get();
            if ($batch_query->isEmpty()) {
                return false;
            } else {
                return true;
            }
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
        return 'Batch not found for '.$this->item_name ;
    }
}
