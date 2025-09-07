<?php

namespace App\Http\Requests\Room;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
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
                   
                    'room_standard_id'=>"required",
                    'room_standard_id'=>"required",
                    'details' => 'nullable',
                    'qty_bed'=>"required",
                    "floor_no"=>"required",
                    'door_name' => 'required|max:250|unique_custom:rooms,door_name,branch_id,' . Auth::user()->branch_id,
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'room_standard_id'=>"required",
                    'room_standard_id'=>"required",
                    'details' => 'nullable',
                    'qty_bed'=>"required",
                    "floor_no"=>"required",
                    'id'=>'required',
                    'door_name' => 'required|max:250|edit_unique_custom:rooms,door_name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                  
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

            'door_name.unique_custom' => 'The door name  should be unique.',
            'door_name.edit_unique_custom' => 'The door name  should be unique.',
      
        ];
    }
}
