<?php

namespace App\Rules;

use App\Returns\ReturnGRN;
use Illuminate\Contracts\Validation\Rule;
use App\PurchaseOrder\PurchaseOrderReceivable;

class ValidateQtyGRNReturns implements Rule
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
        $qty = $this->request->return_array[$index]['qty'];
        $delivery_no = $this->request->return_array[$index]['delivery_no'];
        $this->item_name = $this->request->return_array[$index]['stock']['product_name'];
        $stock_id = $value;
        $delivered_qty = PurchaseOrderReceivable::where(['stock_id' => $stock_id, 'delivery_no' => $delivery_no])->selectRaw('SUM(qty_delivered) AS sum_delivered')->first()->sum_delivered;
        $return_res = ReturnGRN::where(['stock_id' => $stock_id, 'delivery_no' => $delivery_no])->selectRaw('SUM(qty) AS sum_returned')->first()->sum_returned;
        $return_qty = $return_res ? $return_res : 0;
        if ($return_qty == 0) {
            return true;
        }
        $net_qty = $delivered_qty - $return_qty;
        //echo "return_qty >".$return_qty.' delivered_qty >'.$delivered_qty.' qty >'.$qty.' DIFF'.$net_qty;
        if ($net_qty > $qty) {

            return true;
        } else {
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
        return 'Quantity of ' . $this->item_name . ' surpasses delivered quantity';
    }
}
