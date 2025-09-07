<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Contracts\Validation\Rule;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class ValidateBatchQtyInvoice implements Rule
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
    {
        $res=Stock::where(['id'=>$value])->first();
     
        $batch_no = $this->request->batch_no;
        $qty=$this->request->qty;
        $this->item_name =$res->name;
        if ($batch_no) {
            $query = PurchaseOrderReceivableWithBatch::where(['batch_no' => $batch_no,'stock_id'=>$value])
            ->selectRaw('qty_delivered-qty_sold AS pending_qty ')->first();
         
           if($query){
            if ($query->pending_qty >=$qty) {
                return true;
            } else {
                return false;
            }
           }else{
            return false;
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
        return 'Batch qty not  enough for '.$this->item_name ;
    }
}
