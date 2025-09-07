<?php

namespace App\Http\Requests\employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'employee_id'=>'required|unique:employee_details',
            'department_id'=>'required',
            'employee_type_id'=>'required',
            'salary'=>'numeric|required|between:0,9999999.99',
            'house_allowance'=>'numeric|between:0,9999999.99',
            'nhif'=>'numeric|between:0,9999999.99',
            'nssf'=>'numeric|between:0,9999999.99',
            'paye'=>'numeric|between:0,9999999.99',
             'other_deductions'=> 'numeric|between:0,9999999.99',
             
        ];
    }
}
