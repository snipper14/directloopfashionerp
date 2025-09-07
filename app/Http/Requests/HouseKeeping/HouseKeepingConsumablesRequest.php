<?php

namespace App\Http\Requests\HouseKeeping;

use Illuminate\Foundation\Http\FormRequest;

class HouseKeepingConsumablesRequest extends FormRequest
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
                    'room_id' => 'required',
                    
                    'house_keeper_id' => "required",
                    'stock_id' => 'required',
                   
                    'qty' => 'numeric|min:1',
                    'price' => 'numeric|min:1',
                    'total' => 'numeric|min:1',
                    'details' => 'nullable|max:200',
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [

                    'id' => "required",
                    'room_id' => 'required',
                  
                    'house_keeper_id' => "required",
                    'room_reservation_id' => 'required',
                    'stock_id' => 'required',
                   
                    'qty' => 'numeric|min:1',
                    'price' => 'numeric|min:1',
                    'total' => 'numeric|min:1',
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

            'house_keeper_id.required' => 'Select house keeper',
            'stock_id.required' => 'No stock items selected',


        ];
    }
}
