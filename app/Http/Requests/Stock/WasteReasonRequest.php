<?php

namespace App\Http\Requests\Stock;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class WasteReasonRequest extends FormRequest
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
                    'reasons' => 'required|unique_custom:waste_reasons,reasons,branch_id,' . Auth::user()->branch_id,
                   ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'reasons' => 'required|edit_unique_custom:waste_reasons,reasons,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
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

            'reasons.unique_custom' => 'The Reasons  should be unique.',
          
        ];
    }
}
