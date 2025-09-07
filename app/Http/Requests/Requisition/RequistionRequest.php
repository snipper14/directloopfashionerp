<?php

namespace App\Http\Requests\Requisition;

use Illuminate\Foundation\Http\FormRequest;

class RequistionRequest extends FormRequest
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
                   
                    'department_id' => 'required',
                    'stock_id' => 'required',
                    'qty' => 'numeric|required',
                    'unit_price'=>'numeric|required|between:0,999999999.99',
                    'date_due' => 'required',
                    'priority' => 'required',
                    'product_type' => 'required',
                    'details' => 'nullable',
                    'req_id'=>'required'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'department_id' => 'required',
                    'stock_id' => 'required',
                    'qty' => 'numeric|required',
                    'unit_price'=>'numeric|required|between:0,999999999.99',
                    'date_due' => 'required',
                    'priority' => 'required',
                    'product_type' => 'required',
                    'details' => 'nullable',
                    'req_id'=>'required'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
