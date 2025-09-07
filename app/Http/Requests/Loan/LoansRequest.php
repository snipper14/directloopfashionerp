<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class LoansRequest extends FormRequest
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
                     
                    'employee_id'=>'required',
            'description'=>'max:200',
            'loan_date'=>'required',
            'loan_amount'=>'numeric|required',
            'installment_amount'=>'numeric|required',
            'outstanding_balance'=>'numeric|required',
            'completion_date'=>'required',
            'percent_interest'=>'nullable',
            'loan_period'=>'nullable',
            'loan_type'=>'required',
                
                  
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id'=>'required',
                    'employee_id'=>'required',
                    'description'=>'string',
                    'loan_date'=>'required',
                    'loan_amount'=>'required|numeric',
                    'installment_amount'=>'required|numeric',
                    'outstanding_balance'=>'required|numeric',
                    'completion_date'=>'required',
                    'percent_interest'=>'nullable',
                    'loan_period'=>'nullable',
                  
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }

}
