<?php

namespace App\Http\Requests\Stock;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class AccompanimentRequest extends FormRequest
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

                    'stock_id' => 'required',
                     'category_id' => "required",
                    'accompaniment_array_id' => "required|array|min:1",
                    'category' => "max:50",

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'stock_id' => 'required',
                    'accompaniment_id' => "required",
                    'category_id' => "required",
                    'category' => "max:50",
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

            'stock_id.required' => 'The Product is required.',
            'accompaniment_id.required' => 'The accomapaniment item  required.',
            
        ];
    }
}
