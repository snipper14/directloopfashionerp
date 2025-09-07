<?php

namespace App\Http\Requests\Casual;

use Illuminate\Foundation\Http\FormRequest;

class CasualTimelogRequest extends FormRequest
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
                    'date_recorded' => 'required',
                    'no_hrs' => 'required|numeric',
                     
                    'employee_id' => 'required',
                    'time_in' => 'nullable',
                    'time_out' => 'nullable',
                    'amount' => 'required|numeric'

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                    'date_recorded' => 'required',
                    'no_hrs' => 'required|numeric',
                    'employee_id' => 'required',
                    'time_in' => 'nullable',
                    'time_out' => 'nullable',
                    'amount' => 'required|numeric'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
