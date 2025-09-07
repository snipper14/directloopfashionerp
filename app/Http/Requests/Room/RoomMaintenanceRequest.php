<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;

class RoomMaintenanceRequest extends FormRequest
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
                   
                    
                    'details' => 'nullable',
                    'item' => 'required|max:250',
                    'room_id'=>'required',
                    'qty'=>'required|numeric|min:1',
                    'price'=>'required|numeric',
                    'total'=>'required|numeric',
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                        'id'=>"required",
                    'details' => 'nullable',
                    'item' => 'required|max:250',
                    'room_id'=>'required',
                    'qty'=>'required|numeric',
                    'price'=>'required|numeric',
                    'total'=>'required|numeric',
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
