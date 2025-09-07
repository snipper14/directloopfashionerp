<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class HomeIssueRequest extends FormRequest
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
                    
                    'stock_id' => "required",
                    'unit_id'=>'required',
                    'purchase_price'=>'gte:0',
                    'qty' => "numeric",
                    'row_total'=>'gte:0',
                    'date_issued'=>"required"
                  
                   
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id'=>'required',
                    'stock_id' => "required",

                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
