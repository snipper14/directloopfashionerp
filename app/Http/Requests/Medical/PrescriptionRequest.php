<?php

namespace App\Http\Requests\Medical;

use Illuminate\Foundation\Http\FormRequest;

class PrescriptionRequest extends FormRequest
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
                    'customer_id' => "required",
                    'product_name' => "required",
                    'repeat_pattern' => "nullable",
                    'note' => "nullable",
                    'qty' => "required|numeric",
                    'prescription_date_time' => "nullable",


                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                   'entry_code' => 'required',
                   'stock_id' => 'required',
                   'customer_id' => "required",
                   'product_name' => "required",
                   'repeat_pattern' => "nullable",
                   'note' => "nullable",
                   'qty' => "required|numeric",
                   'prescription_date_time' => "nullable",
                   
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

            'stock_id.required' => 'Stock required',
            'customer_id.required' => 'Patient Name required',

        ];
    }
}
