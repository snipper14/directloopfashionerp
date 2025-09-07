<?php

namespace App\Http\Requests\TimeLog;

use Illuminate\Foundation\Http\FormRequest;

class TimeLogRequest extends FormRequest
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

                    'logged_on' => 'required',
                    'no_hours' => 'required|numeric',
                    'employee_id' => 'required',
                    'time_in' => 'nullable',
                    'time_out' => 'nullable',
                    'total_amount' => 'required|numeric'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                    'logged_on' => 'required',
                    'no_hours' => 'required|numeric',
                    'employee_id' => 'required',
                    'time_in' => 'nullable',
                    'time_out' => 'nullable',
                    'total_amount' => 'required|numeric'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
