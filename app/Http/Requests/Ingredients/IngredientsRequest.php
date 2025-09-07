<?php

namespace App\Http\Requests\Ingredients;

use Illuminate\Foundation\Http\FormRequest;

class IngredientsRequest extends FormRequest
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
                     'stock_id'=>"required",
                     'produced_qty'=>"numeric|required|gte:0",
                    'ingredient_id' => 'required',
                    
                    'qty' => 'numeric|required|gte:0',
                   
                 
                
                  
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id'=>"required",
                    'stock_id'=>"required",
                    'ingredient_id' => 'required',
                    'produced_qty'=>"numeric|required|gte:0",
                    'qty' => 'numeric|required',
                   
                  
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
