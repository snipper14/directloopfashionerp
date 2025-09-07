<?php

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class PettyCashRequest extends FormRequest
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
                    'opening_balance' => 'numeric|required:gt:0',
                    'closing_balance' => 'numeric|required',
                   
                    'date_recorded'=>'required',
                   
                   
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                   
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

            'expense_type_id.required' => 'Select expense category.',

        ];
    }    
}
