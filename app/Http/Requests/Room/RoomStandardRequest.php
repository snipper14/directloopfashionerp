<?php

namespace App\Http\Requests\Room;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class RoomStandardRequest extends FormRequest
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
                    'name' => 'required|max:250|unique_custom:room_standards,name,branch_id,' . Auth::user()->branch_id,
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'details' => "nullable",
                    'id'=>'required',
                    'name' => 'required|max:250|edit_unique_custom:room_standards,name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                  
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

            'name.unique_custom' => 'The name  should be unique.',
            'name.edit_unique_custom' => 'The name  should be unique.',
      
        ];
    }
}
