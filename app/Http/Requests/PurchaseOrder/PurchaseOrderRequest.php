<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
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
                    'supplier_id' => "required",
                    'vendor_reference' => 'nullable',
                    'order_no' => 'required',
                    'order_deadline' => 'required',
                    'receipt_date' => 'required',
                    'stock_id' => 'required',
                    'shipping_address' => 'required',
                    'qty' => 'numeric|required',
                    'unit_price' => 'numeric|required',
                    'sub_total' => 'numeric|required',
                    'taxes' => 'numeric|required',
                    'tax_rate' => 'numeric|required',
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'supplier_id' => "required",
                    'vendor_reference' => 'nullable',
                    'order_no' => "required",
                    'order_deadline' => 'required',
                    'shipping_address' => 'required',
                    'receipt_date' => 'required',
                    'stock_id' => 'required',
                    'qty' => 'numeric|required',
                    'unit_price' => 'numeric|required|between:0,999999999.99',
                    'sub_total' => 'numeric|required',
                    'taxes' => 'numeric|required|between:0,999999999.99',
                    'tax_rate' => 'numeric|required|between:0,999999999.99',
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
