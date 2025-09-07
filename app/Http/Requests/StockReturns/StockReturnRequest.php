<?php

namespace App\Http\Requests\StockReturns;

use Illuminate\Foundation\Http\FormRequest;

class StockReturnRequest extends FormRequest
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
                    'return_user_id' => "required",
                    'qty_returned' => 'numeric|required',
                    'date_returned' => 'required',
                    
                    'details' => 'nullable',
                    'return_code' => 'required'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",

                    'department_id' => 'required',
                    'stock_id' => 'required',
                    'return_user_id' => "required",
                    'qty_returned' => 'numeric|required',
                    'date_returned' => 'required',
                    'priority' => 'required',

                    'details' => 'nullable',
                    'return_code' => 'required'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
