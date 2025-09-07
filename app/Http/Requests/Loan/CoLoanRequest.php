<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class CoLoanRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employee_id'=>'required',
            'description'=>'max:200',
            'loan_date'=>'required',
            'loan_amount'=>'required|numeric',
            'installment_amount'=>'required|numeric',
          'completion_date'=>'required',
            'percent_interest'=>'nullable',
            'loan_period'=>'nullable',
            'loan_type'=>'required',
            'total_payable_amount'=>'required|numeric',
        ];
    }


  
}
