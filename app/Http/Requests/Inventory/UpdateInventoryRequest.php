<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
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
                    "curr_qty" => "required|numeric",
                     
                     'department_id' => "required",
                    'type'=>'required'
                  

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'stock_id' => "required",
                    "curr_qty" => "required|numeric",
                     
                     'department_id' => "required",
                    'type'=>'required',
                    'item_id'=>"required"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
