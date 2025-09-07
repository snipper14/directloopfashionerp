<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequestTemp extends FormRequest
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
                    
                    'order_deadline' => 'required',
                    'receipt_date' => 'required',
                    'stock_id' => 'required',
                    'shipping_address' => 'nullable',
                    'qty' => 'numeric|required|gte:1',
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
                   
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
