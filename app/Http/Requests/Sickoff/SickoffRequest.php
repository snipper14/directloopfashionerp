<?php

namespace App\Http\Requests\Sickoff;

use Illuminate\Foundation\Http\FormRequest;

class SickoffRequest extends FormRequest
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

                    'customer_id' => "required",
                    'age' => 'required',
                    'reg_no' => 'nullable',
                    'gender' => "required",
                    'sickoff_days' => 'gte:0',
                    'startdate' => "required",
                    'enddate' => 'required',
                    'currentdate' => 'required',
                    'doctors_name' => 'required',
                    'doctors_report' => 'required',
                    'cost' => 'required|gt:0',
                    'patient_name' => 'required'

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                    

                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
