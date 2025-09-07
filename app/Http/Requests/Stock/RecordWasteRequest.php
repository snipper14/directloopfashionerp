<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class RecordWasteRequest extends FormRequest
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
                    'waste_reason_id'=>'required',
                    'unit_id'=>'required',
                    'department_id'=>'required',
                    'purchase_price'=>'gte:0',
                    'qty' => 'gte:0',
                    'total'=>'gte:0',
                    'date_wasted'=>"required",
                  'waste_no'=>'required',
                  'staff_id'=>'required'
                   
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
    public function messages(){
        return [
            "department_id.required"=>" department required",
            "reason_id.required"=>" reason required",
            "staff_id.required"=>" staff required"
        ];
    }
}
