<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class DirectPurchaseOrderRequest extends FormRequest
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

                    'department_id' => "required",

                    'order_no' => "required",

                    'order_deadline' => "required",
                    'order_date' => "required",
                    'stock_id' => "required",
                    'qty' => "required|numeric",

                    'unit_price' => "required|numeric",
                    'sub_total' => "required|numeric",
                    'tax_rate' => "required|numeric",
                    'taxes' => "required|numeric"

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

    public function messages()
    {
        return [

            'department_id.required' => 'The Department should be selected.',

        ];
    }
}
