<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class TakeAwayOrderRequest extends FormRequest
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


                    'stock_id' => 'required',
                    'unit_id' => 'required',
                    'customer_id' => 'nullable',
                    'qty' => "required|numeric|min:1",
                    'price' => "required|numeric",
                    'row_total' => "required|numeric",
                    'row_vat' => "required|numeric",
                    'department_id' => "required",
                    'notes' => "nullable",
                    'order_date' => "required",
                    'order_no' => "required",
                    'order_type' => 'required',
                    'company_name' => "required",
                    'cost_total' => "min:0|numeric",
                    'cost_price' => "min:0|numeric"

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'stock_id' => 'required',
                    'unit_id' => 'required',
                    'customer_id' => 'nullable',
                    'qty' => "required|numeric|min:1",
                    'price' => "required|numeric",
                    'row_total' => "required|numeric",
                    'row_vat' => "required|numeric",

                    'notes' => "nullable",
                    'order_date' => "required",
                    'order_no' => "required",
                    'order_type' => 'required',
                    'company_name' => "required",
                    'cost_total' => "min:0|numeric",
                    'cost_price' => "min:0|numeric"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
