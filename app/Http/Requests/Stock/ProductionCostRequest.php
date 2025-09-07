<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class ProductionCostRequest extends FormRequest
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
                     'product_id'=>"required",
                    'qty_produced' => 'numeric|required|gt:0',
                    'total_amount'=>"numeric|required|gt:0",
                    'details'=>"required"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id'=>"required",
                    'stock_id'=>"required",
                    'product_id'=>"required",
                    'qty_produced' => 'numeric|required|gt:0',
                    'total_amount'=>"numeric|required",
                    'details'=>"required"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }

  
}
