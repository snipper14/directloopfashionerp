<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class ExpensesPaymentRequest extends FormRequest
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
                    'expense_id'=>'required',
                    'supplier_id' => 'required',
                    'account_id'=>'required',
                    'expense_amount' => 'required|gt:0',
                    'amount_paid'=> 'required|gt:0',
                    'ref_no'=>"required",
                    'date_paid'=>'required',
                    'payment_details'=>'nullable',
                    'pay_method'=>'nullable',
                   
                   
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
