<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableBookingRequest extends FormRequest
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
                    'name' => 'required',
                    'email' => 'required|email',
                    'phone'=>'required',
                    'event_date'=>'required',
                    'no_people'=>'required|numeric|gte:1',
                    'details' => "max:250"
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


    public function messages()
    {
        return [

           

        ];
    }
}
