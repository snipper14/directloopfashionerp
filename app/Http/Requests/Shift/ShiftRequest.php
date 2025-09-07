<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRequest extends FormRequest
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
                 
                    'status'=>"required",
                    
                  
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                   'id'=>"required",
                   'status'=>"required",
                 
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }

   
}
