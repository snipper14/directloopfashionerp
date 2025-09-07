<?php

namespace App\Http\Requests\Employee;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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

                    'first_name' => 'required|min:3|max:200',
                    'last_name' => 'required|min:3|max:200',
                    'email' => 'nullable|email|unique_custom:employees,email,branch_id,' . Auth::user()->branch_id,

                    'id_no' => 'nullable',
                    'dob' => 'nullable',
                    'phone' => 'nullable|numeric',
                    'other' => 'max:200',
                    'payment_type' => 'required',
                    'department_id' => 'required',
                    'employee_type_id' => 'required',
                    'salary' => 'numeric|between:0,9999999.99',
                    'hrly_rate' => 'nullable|between:0,9999999.99',

                    'kra_pin' => 'nullable',
                    'job_no' => 'nullable',
                    'start_contract' => 'required',
                    'deduct_nssf' => 'required',
                    'deduct_nhif' => 'required',
                    'deduct_paye' => 'required',
                    'pay_method' => 'max:200',
                    'bank_branch' => "nullable",
                    'bank_name' => 'nullable',
                    'account_name' => "nullable",
                    'account_no' => 'nullable',
                    'payment_phone' => 'nullable',
                    'deduct_housinglevy'=>'required'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                    'email' => "nullable",
                    'id_no' => "nullable",
                    'first_name' => 'required|min:3|max:200',
                    'last_name' => 'required|min:3|max:200',
                    'dob' => 'nullable',
                    'phone' => 'nullable|numeric',
                    'other' => 'max:200',
                    'department_id' => 'required',
                    'employee_type_id' => 'required',
                    'salary' => 'numeric|required|between:0,9999999.99',
                    'hrly_rate' => 'between:0,9999999.99',
                    'payment_type' => 'required',

                    'kra_pin' => 'nullable',
                    'job_no' => "nullable",
                    'start_contract' => 'required',
                    'deduct_nssf' => 'required',
                    'deduct_nhif' => 'required',
                    'deduct_paye' => 'required',
                    'pay_method' => 'max:200',
                    'bank_branch' => "nullable",
                    'bank_name' => 'nullable',
                    'account_name' => "nullable",
                    'account_no' => 'nullable',
                    'payment_phone' => 'nullable',
                    'deduct_housinglevy'=>'required'
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

            'email.unique_custom' => 'The email has error or is not unique.',


        ];
    }
}
