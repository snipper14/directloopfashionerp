<?php

namespace App\Http\Requests\Deduction;

use Illuminate\Foundation\Http\FormRequest;

class DeductionRequest extends FormRequest
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
                    "deduction_type"=>"required",
            'employee_id'=>"required",
            "monthy_deduction"=>'required|numeric',
            "deduction_total"=>'required|numeric',
            'effective_date'=>'required|date',
            'end_date'=>'required|date',
            'no_of_months'=>"required|digits_between: 1,200",

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    "deduction_type"=>"required",
                    'employee_id'=>"required",
                    "monthy_deduction"=>'required|numeric',
                    "deduction_total"=>'required|numeric',
                    'effective_date'=>'required',
                    'end_date'=>'required|date',
                    'no_of_months'=>"required|digits_between: 1,200",
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
