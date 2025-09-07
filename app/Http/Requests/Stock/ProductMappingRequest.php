<?php

namespace App\Http\Requests\Stock;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ProductMappingRequest extends FormRequest
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
                    "qty" => "numeric|required",
                    'element_id' => 'required|max:250|three_unique_custom:product_mappings,element_id,stock_id,' . $this->stock_id . ',branch_id,' . Auth::user()->branch_id,
                    'stock_id' => "required",
                 
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

            'element_id.three_unique_custom' => ' This combination  already exists.',


        ];
    }
}
