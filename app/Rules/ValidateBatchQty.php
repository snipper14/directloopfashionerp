<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\PurchaseOrder\PurchaseOrderReceivable;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class ValidateBatchQty implements Rule
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
        $index = explode('.', $attribute)[1];
        $batch_no = $this->request->order_data_offline[$index]['batch_no'];
        $qty=$this->request->order_data_offline[$index]['qty'];
        $this->item_name = $this->request->order_data_offline[$index]['product_name'];
        if ($batch_no) {
            $query = PurchaseOrderReceivableWithBatch::where(['batch_no' => $batch_no,'stock_id'=>$value])->selectRaw('qty_delivered-qty_sold AS pending_qty ')->first();
          // print_r($query->pending_qty.">>>>>>>>>>>".$qty."????".$this->item_name);
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
