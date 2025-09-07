<?php

namespace App\Http\Requests\Advance;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceRequest extends FormRequest
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
                    'employee_id'=>"required",
                    'payment_month'=>"required",
                    'amount'=>'required|numeric',
                    'payroll_code'=>"nullable",
                    'payroll_account_id'=>'required',
                    'ref_no'=>'required'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",

                    'employee_id'=>"required",
                    'payment_month'=>"required",
                    'amount'=>'required|numeric',
                    'payroll_code'=>"nullable",
                      'payroll_account_id'=>'required'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }

   
}
