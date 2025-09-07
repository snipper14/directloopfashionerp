<?php

namespace App\Http\Requests\Location;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class TableRequest extends FormRequest
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
                    "seat_type" => "required",
                    'name' => 'required|max:250|three_unique_custom:tables,name,department_id,' . $this->department_id . ',branch_id,' . Auth::user()->branch_id,
                    'department_id' => "required",
                    'no_seats'=>"numeric|required",
                    'details'=>'nullable'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    "seat_type" => "required",
                    'name' => 'required',
                    'department_id' => "required",
                    'no_seats'=>"numeric|required",
                    'details'=>'nullable'
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

            'name.three_unique_custom' => ' This  table name  already exists. in the location',
          'name.edit_three_unique_custom'=>' This  table name  already exists. in the location',

        ];
    }
}
