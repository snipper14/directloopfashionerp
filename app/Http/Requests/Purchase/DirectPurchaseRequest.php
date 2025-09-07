<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class DirectPurchaseRequest extends FormRequest
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
                    'direct_purchase_order_id' => "required",
                    'stock_id' => 'required',

                    'qty_delivered' => "numeric|required",
                    'qty_ordered' => "numeric|required",
                    'order_no' => "required",
                    'sub_total' => 'numeric|required',
                    'unit_price' => 'numeric|required',
                    'received_date' => "required",
                    'delivery_no' => 'required'
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
