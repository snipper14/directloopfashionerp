<?php

namespace App\Http\Requests\Conference;

use Illuminate\Foundation\Http\FormRequest;

class ConferenceRequest extends FormRequest
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
                    'conference_room' => "required",
                    'no_guest' => "nullable",
                    "company_name" => "required",
                    "email" => "nullable",
                    "phone" => "required",
                    'details' => 'nullable',
                    'total_paid' => 'required|numeric',
                    'receipt_no' => 'required',
                    "to" => "nullable",
                    "from" => "nullable",
                    "pay_method" => "required"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",

                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
