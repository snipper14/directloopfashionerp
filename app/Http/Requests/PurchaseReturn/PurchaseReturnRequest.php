<?php

namespace App\Http\Requests\PurchaseReturn;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseReturnRequest extends FormRequest
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
                   
                   
                    'stock_id'=>'required',
                    'supplier_id'=>"required",
                    "qty"=>'numeric|required',
                    'return_code'=>"required",
                    'order_no'=>"required",
                    "details"=>"required",
                    'return_date'=>"required",
                    'deduct_stock'=>"required",
               'unit_price'=>"numeric|required"
               
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                  
                    'stock_id'=>'required',
                    'supplier_id'=>"required",
                    "qty"=>'numeric|required',
                    'return_code'=>"required",
                    'order_no'=>"required",
                    "details"=>"required",
                    'return_date'=>"required",
                    'deduct_stock'=>"required",
                    'unit_price'=>"numeric|required"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
