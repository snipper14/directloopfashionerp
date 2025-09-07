<?php

namespace App\Http\Requests\Guest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class GuestRequest extends FormRequest
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
                    'id_no' => 'required|unique_custom:guests,id_no,branch_id,' . Auth::user()->branch_id,
                    'phone' => 'required|unique_custom:guests,phone,branch_id,' . Auth::user()->branch_id,
                    'name' => 'required',
                    'guest_company_id'=>"required",
                    // 'name' => 'required|unique_custom:guests,name,branch_id,' . Auth::user()->branch_id,
                    'email' => 'nullable|email|max:200',
                    'country_origin' => "nullable",
                    'gender' => 'required',
                    "birth_date" => "required",
                    'company_name' => "nullable",
                    'age' => 'nullable',
                    'members' => 'numeric',
                    'postal_address' => 'nullable|max:200',

                    'details' => 'nullable|max:200',




                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [

                    'id' => "required",
                    'id_no' => 'required|edit_unique_custom:guests,id_no,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'phone' => 'required|edit_unique_custom:guests,phone,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'name' => 'required',
                    
                    'guest_company_id'=>"required",
                    // 'name' => 'required|edit_unique_custom:guests,name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'email' => 'nullable|email|max:200',
                    'country_origin' => "nullable",
                    "birth_date" => "required",
                    'company_name' => "nullable",
                    'gender' => 'required',
                    'age' => 'nullable',
                    'members' => 'numeric',
                    'postal_address' => 'nullable|max:200',
                    'customer_id' => 'required',
                    'details' => 'nullable|max:200',
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

           
            'guest_company_id.required' => 'Select company',
            'id_no.unique_custom' => 'The ID already registered',
            'id_no.edit_unique_custom' => 'The ID  already registered',
            'phone.unique_custom' => 'The phone no.  should be unique.',
            'phone.edit_unique_custom' => 'The phone no  should be unique.',
            'name.unique_custom' => 'The name   should be unique.',
            'name.edit_unique_custom' => 'The name  should be unique.',

        ];
    }
}
