<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class SplitProductRequest extends FormRequest
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
                    'original_product_id' => "required",
                    'final_product_id' => 'required',
                    'final_qty' => "required|numeric|min:1",
                    'qty_split' => "required|numeric|min:1",
                    'split_product_setting_id' => "required"


                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'original_product_id' => "required",
                    'final_product_id' => 'required',
                    'final_qty' => "required|numeric|min:1",
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
