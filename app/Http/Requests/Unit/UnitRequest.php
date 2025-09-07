<?php

namespace App\Http\Requests\Unit;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
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
                   // 'name' => 'required|unique_custom:units,name,branch_id,' . Auth::user()->branch_id,
                    'name'=>'required|max:250|unique:units,name,NULL,id,deleted_at,NULL',
                    'description' => "nullable",
                    'code'=>"required"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                    //'name' => 'required|edit_unique_custom:units,name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'name'=>'required|unique:units,name,'.$this->id.',id,deleted_at,NULL',
                    'description' => "max:250",
                    'code'=>"required"
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

            'name.unique_custom' => 'The name  should be unique.',
            'name.edit_unique_custom' => 'The name  should be unique.',

        ];
    }
}
