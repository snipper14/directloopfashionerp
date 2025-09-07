<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
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
                    'order_no' => 'required',
                    'stock_id' => "required",
                    'department_id' => "required",
                    'customer_id' => 'required',
                    'order_date' => "required",
                    'qty' => "required|numeric",
                    'selling_price' => "required|numeric",
                    'row_total' => "required|numeric|gt:0",
                    'order_total' => "required|numeric|gte:0",
                    'tax_amount' => "required|numeric|between:0,99999999999999",
                    'purchase_price' => 'required|numeric',
                    'discount'=>'required|numeric',
                    'product_category_id'=>'required',
                    'batch_no'=>'nullable'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'order_no' => 'required',
                    'stock_id' => "required",
                    'department_id' => "required",
                    'customer_id' => 'required',
                    'order_date' => "required",
                    'qty' => "required|numeric",
                    'selling_price' => "required|numeric",
                    'row_total' => "required|numeric|between:1,99999999999999",
                    'order_total' => "required|numeric|between:0,99999999999999",
                    'tax_amount' => "required|numeric|between:0,99999999999999",
                    'purchase_price' => 'required|numeric',
                    'discount'=>'required|numeric',
                    'product_category_id'=>'required'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
