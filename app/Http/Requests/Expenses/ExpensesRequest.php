<?php

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class ExpensesRequest extends FormRequest
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
                    'expense_type_id' => 'required',
                    'amount' => 'required|gt:0',
                    'date_recorded'=>'required',
                    'details'=>'required',
                   'supplier_id'=>'required',
                   'ref_no'=>'nullable',
                   'cu_invoice_no'=>"nullable"
                   
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
