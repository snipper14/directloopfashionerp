<?php

namespace App\Http\Requests\PreOrders;

use Illuminate\Foundation\Http\FormRequest;

class PreorderRequest extends FormRequest
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
                    'stock_id' => "required",
                    'qty' => 'required|numeric|gt:0',
                    'selling_price' => 'numeric|required|gt:0',
                    'purchase_price' => 'numeric|required',
                    'row_total' => 'required|numeric|gt:0',
                    'row_vat' => 'required|numeric|gte:0',
                    'description' => "nullable",
                    'customer_id' => 'required',
                    'order_date' => 'required',
                    'customer_phone'=>'required',
                    'customer_name'=>'required'
                   

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

            'location_id.required' => 'Select Location',
            'table_id.required' => 'Select Table',


        ];
    }
}
