<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class POReceivableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $method = $this->method();
        if (null !== $this->get('_method', null)) {
            $method = $this->get('_method');
        }
        $this->offsetUnset('_method');
        switch ($method) {

            case 'POST':
                $this->rules = [
                    'purchase_order_id' => "required",
                    'stock_id' => 'required',
                    'supplier_id' => "required",
                    'qty_delivered' => "numeric|required",
                    'qty_ordered' => "numeric|required",
                    'order_no' => "required",
                    'sub_total' => 'numeric|required',
                    'unit_price' => 'numeric|required',
                    'received_date' => "required",
                    'delivery_no' => 'required',
                    'department_id' => 'required',
                    'tax_amount'=>'numeric|required',
                    'tax_rate'=>'numeric|required',
                    'batch_no'=>"nullable",
                    'expiry_date'=>"nullable",
                    'discount_amount'=>"numeric|required"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'stock_id' => 'required',
                    'purchase_order_id' => "required",
                    'supplier_id' => "required",
                    'qty_delivered' => "numeric|required",
                    'qty_ordered' => "numeric|required",
                    'order_no' => "required",
                    'unit_price' => 'numeric|required',
                    'sub_total' => 'numeric|required',
                    'received_date' => "required",
                    'delivery_no' => 'required',
                    'tax_amount'=>'numeric|required',
                    'tax_rate'=>'numeric|required'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
