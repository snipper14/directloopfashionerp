<?php

namespace App\Rules;

use App\Stock\Stock;
use App\dept\Department;
use App\Inventory\Inventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;
use App\PurchaseOrder\PurchaseOrderReceivable;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class ValidateBatchNo implements Rule
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
        $this->item_name = $this->request->order_data_offline[$index]['product_name'];
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
