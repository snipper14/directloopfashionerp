<?php

namespace App\Http\Requests\dept;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
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
                    // 'department' => 'required|unique_custom:departments,department,branch_id,' . Auth::user()->branch_id,
                    'branch_id' => 'required',
                    'department' => 'required|unique:departments,department,NULL,id,deleted_at,NULL',
                    'show_pos' => "required",
                    'details' => "max:250",
                    'location_type' => "required"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'branch_id' => 'required',
                    'id' => 'required',
                    //'department' => 'required|edit_unique_custom:departments,department,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'department' => 'required|unique:departments,department,' . $this->id . ',id,deleted_at,NULL',
                    'show_pos' => "required",
                    'details' => "max:250",
                    'location_type' => "required"
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
            'branch_id.required' => 'Select branch',
            'department.unique_custom' => 'The department  should be unique.',
            'department.edit_unique_custom' => 'The department  should be unique.',

        ];
    }
}
