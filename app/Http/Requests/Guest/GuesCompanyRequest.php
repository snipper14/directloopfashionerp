<?php

namespace App\Http\Requests\Guest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class GuesCompanyRequest extends FormRequest
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
                    'name' => 'required|unique_custom:guest_companies,name,branch_id,' . Auth::user()->branch_id,
                    'address' => 'nullable',
                    'contacts' => 'nullable',
                     'email' => 'nullable|email|max:200',
                   




                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [

                    'id' => "required",
                    'name' => 'required|edit_unique_custom:guest_companies,name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'address' => 'nullable',
                    'contacts' => 'nullable',
                     'email' => 'nullable|email|max:200',
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

            'name.unique_custom' => 'The Company already registered',
            'name.edit_unique_custom' => 'The Company  already registered',
           
        ];
    }
}
