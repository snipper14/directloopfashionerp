<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class POSOrderRequest extends FormRequest
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
                    'user_id' => 'required',
                    'department_id' => 'required',
                    'isBar' => "required",
                    'stock_id' => 'required',
                    'table_id' => "nullable",
                    'unit_id' => 'required',
                    'customer_id' => 'nullable',
                    'qty' => "required|numeric|min:1",
                    'price' => "required|numeric",
                    'row_total' => "required|numeric",
                    'row_vat' => "required|numeric",
                    'no_guest' => "required|numeric|min:1",
                    'notes' => "nullable",
                    'order_date' => "required",
                    'order_no' => "required",
                    'order_type' => 'required',
                    'company_name' => "required",
                    'cost_total' => "min:0|numeric",
                    'cost_price' => "min:0|numeric",
                    'accompaniment_id' => "nullable",
                    'customer_name' => 'nullable',
                    'customer_address' => 'nullable',
                    'customer_phone' => 'nullable',
                    'delivery_time' => 'nullable'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'department_id' => 'required',
                    'isBar' => "required",
                    'stock_id' => 'required',
                    'unit_id' => 'required',
                    'customer_id' => 'nullable',
                    'qty' => "required|numeric|min:1",
                    'price' => "required|numeric",
                    'row_total' => "required|numeric",
                    'row_vat' => "required|numeric",
                    'no_guest' => "required|numeric|min:1",
                    'notes' => "nullable",
                    'order_date' => "required",
                    'order_no' => "required",
                    'order_type' => 'required',
                    'company_name' => "required",
                    'cost_total' => "min:0|numeric",
                    'cost_price' => "min:0|numeric",
                    'customer_name' => 'nullable',
                    'customer_address' => 'nullable',
                    'customer_phone' => 'nullable',
                    'delivery_time' => 'nullable'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
