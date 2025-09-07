<?php

namespace App\Http\Requests\employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
   
    public function rules()
    {
        return [
            'first_name'=>'required|min:3|max:200',
            'last_name'=>'required|min:3|max:200',
            'email'=>'nullable',
            'id_no'=>'nullable',
            'dob'=>'nullable',
             'phone'=> 'nullable|numeric',
             'other'=>'max:200',
            'payment_type'=>'max:200',
             'department_id'=>'required',
             'employee_type_id'=>'required',
             'salary'=>'numeric|between:0,9999999.99',
             'hrly_rate'=>'nullable|between:0,9999999.99',

             'kra_pin'=>'nullable',
             'job_no'=>'nullable',
             'start_contract'=>'max:200',
             'deduct_nssf'=>'required',
             'deduct_nhif'=>'required',
             'deduct_paye'=>'required',
             'pay_method'=>'max:200',
             'bank_branch'=>"nullable",
             'bank_name'=>'nullable',
             'account_name'=>"nullable",
             'account_no'=>'nullable',
             'payment_phone'=>'nullable'
        ];
    }

  
}
