<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
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
                $data = json_decode($this['data'], true);
      
                $this->rules = [
                    'branch' => "required|unique:branches,branch,NULL,id,deleted_at,NULL",
                    'business_name' => 'required:max:50',
                    'address' => 'required:max:50',
                    'postal_address' => "required:max:30",
                    'country' => "max:50",
                    'till_no' => "nullable",
                    'town' => "max:50",
                    'kra_pin' => 'max:50',
                    'phone_no' => 'max:50',
                    'email' => "max:200",
                    'details' => "max:250",
                    'file' => 'mimes:jpeg,jpg,png,gif|required|max:10000'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                    'branch' => "required|unique:branches,branch,{$this->id},id,deleted_at,NULL",
                    'business_name' => 'required:max:50',
                    'address' => 'required:max:50',
                    'postal_address' => "required:max:30",
                    'country' => "max:50",
                    'till_no' => "nullable",
                    'town' => "max:50",
                    'kra_pin' => 'max:50',
                    'phone_no' => 'max:50',
                    'email' => "max:200",
                    'details' => "max:250"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
