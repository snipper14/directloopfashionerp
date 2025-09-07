<?php

namespace App\Http\Requests\employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
  
    public function rules()
    {
        return [
            'id'=>'required',
            'email'=>"nullable",
            'id_no'=>"nullable",
            'first_name'=>'required|min:3|max:200',
            'last_name'=>'required|min:3|max:200',
              'dob'=>'nullable',
             'phone'=> 'nullable|numeric',
             'other'=>'max:200',
             'department_id'=>'required',
             'employee_type_id'=>'required',
             'salary'=>'numeric|required|between:0,9999999.99',
             'hrly_rate'=>'between:0,9999999.99',
             'payment_type'=>'max:200',

             'kra_pin'=>'nullable',
             'job_no'=>"nullable",
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
