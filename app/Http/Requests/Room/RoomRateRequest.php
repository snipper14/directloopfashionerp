<?php

namespace App\Http\Requests\Room;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class RoomRateRequest extends FormRequest
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
                    "rate" => "numeric|required",
                    'room_standard_id' => 'required|max:250|three_unique_custom:room_rates,room_standard_id,room_package_id,' . $this->room_package_id . ',branch_id,' . Auth::user()->branch_id,
                    'room_package_id' => "required",
                    'meal_rate'=> "numeric|required",
                    'room_rate'=> "numeric|required",
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    "rate" => "numeric|required",
                    'room_standard_id' => 'required',
                    'room_package_id' => "required",
                    'meal_rate'=> "numeric|required",
                    'room_rate'=> "numeric|required",
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

            'room_standard_id.three_unique_custom' => ' This combination  already exists.',
          

        ];
    }
}
