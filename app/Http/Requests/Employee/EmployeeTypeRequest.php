<?php

namespace App\Http\Requests\Employee;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeTypeRequest extends FormRequest
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
                    'name' => 'required|max:20|unique_custom:employee_types,name,branch_id,' . Auth::user()->branch_id,

                    'details' => "max:250"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",

                    'name' => 'required|edit_unique_custom:employee_types,name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,                    'name' => 'nullable|unique_custom:employee_types,name,branch_id,' . Auth::user()->branch_id,

                    'details' => "max:245"
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

            'name.unique_custom' => 'The role  should be unique.',
            'name.edit_unique_custom' => 'The role  should be unique.',
           
        ];
    }
}
