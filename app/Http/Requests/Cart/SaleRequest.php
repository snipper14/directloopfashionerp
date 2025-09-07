<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
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
                    'customer_id' => 'required',
                    'department_id' => 'required',
                    'order_date' => "required",
                    'qty' => "required|numeric",
                  
                    'item_price' => "required|numeric|gt:0",
                    'row_total' => "required|numeric|gt:0",
                    'order_total' => "required|numeric|gt:0",
                    'tax_amount' => "required|numeric|gte:0",
                    'discount' => 'required:numeric',
                    'product_category_id' => "required",
                    'purchase_price'=>'numeric|required'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'order_no' => 'required',
                    'stock_id' => "required",
                    'customer_id' => 'required',
                    'department_id' => 'required',
                    'order_date' => "required",
                    'qty' => "required|numeric",
                    'item_price' => "required|numeric|gt:0",
                    'row_total' => "required|numeric|gt:0",
                    'order_total' => "required|numeric|gt:0",
                    'tax_amount' => "required|numeric|gte:0",
                    'discount' => 'required:numeric',
                    'product_category_id' => "required",
                    'purchase_price'=>'numeric|required'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
