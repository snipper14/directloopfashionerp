<?php

namespace App\Http\Requests\Location;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
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
                    'name' => 'required|unique_custom:locations,name,branch_id,' . Auth::user()->branch_id,
                    'details'=>"nullable"



                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                   'name' => 'required|edit_unique_custom:locations,name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'id' => 'required',
                    'details' => 'nullable',
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
