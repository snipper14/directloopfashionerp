<?php

namespace App\Http\Requests\Digitax;

use Illuminate\Foundation\Http\FormRequest;

class DigitaxStockRequest extends FormRequest
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

                    'item_classification_id' => "required",
                    'etims_item_type_id' => "required",
                    'etims_origin_country_id' => "required",
                    'etims_item_packaging_code_id' => "required",
                   'id' => 'required',
                    
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'item_classification_id' => "required",
                    'etims_item_type_id' => "required",
                    'etims_origin_country_id' => "required",
                    'etims_item_packaging_code_id' => "required",
                   'id' => 'required',
                    'item_id' => 'required',
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

            'department.unique_custom' => 'The department  should be unique.',
            'department.edit_unique_custom' => 'The department  should be unique.',

        ];
    }
}
