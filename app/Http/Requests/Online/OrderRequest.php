<?php

namespace App\Http\Requests\Online;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
                    "order_array"    => "required|array|min:1",
                    'notes' => "nullable",
                    'customer_name' => "required",
                    'customer_address' => "required",
                    'customer_phone' => 'required|numeric',
                  
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

            'order_array.required' => 'No orders available',
          


        ];
    }
}
