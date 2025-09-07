<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class SplitOrderRequest extends FormRequest
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
                    'id' => "required",
                    'qty' => 'numeric|min:1',
                    'order_no' => "required",
                    'split_order_no' => "required",
                    'order_qty' => 'numeric',
                    'department_id' => "required",
                    'row_total' => "numeric|required",
                    'row_vat' => "numeric|required",
                    'no_guest' => 'numeric|min:1',
                    'accompaniment_id' => "nullable"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'qty' => 'numeric|min:1',
                    'order_no' => "required",
                    'order_qty' => 'numeric',

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
