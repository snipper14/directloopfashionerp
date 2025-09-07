<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class RoomTransferRequest extends FormRequest
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
                    'price' => 'required|numeric|min:1',
                    'total' => "required|numeric|min:1",
                    'qty' => "numeric|required|min:1",
                    "id"=>"required",
                    'room_id' => 'required',
                    'room_rate_id' => "required",

                    'room_package_id' => "required",
                    'tax_amount' => "numeric|required",
                    'tax_rate' => "numeric|required",
                   
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

    public function messages()
    {
        return [
    
          

        ];
    }
}
