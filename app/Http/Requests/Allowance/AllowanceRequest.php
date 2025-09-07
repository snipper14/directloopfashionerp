<?php

namespace App\Http\Requests\Allowance;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class AllowanceRequest extends FormRequest
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
                    'employee_id' => 'required',
                    'amount' => 'required|numeric',
                    'allowance_type' => 'required|max:250|edit_unique_custom:allowances,allowance_type,branch_id,' . Auth::user()->branch_id . ',employee_id,' . $this->employee_id,
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",

                    'employee_id' => 'required',
                    'amount' => 'required|numeric',
                    'allowance_type' => "required",
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
            'allowance_type.edit_unique_custom' => 'The allowance_type  should be unique.',

        ];
    }
}
