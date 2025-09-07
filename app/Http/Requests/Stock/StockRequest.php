<?php

namespace App\Http\Requests\Stock;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StockRequest extends FormRequest
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
                    'name' => "required",
                    'product_name' => 'required|max:200|unique_custom:stocks,product_name,branch_id,' . Auth::user()->branch_id,
                    'unit_id' => "required",
                    'product_category_id' => "required",

                    'code' => 'required|max:20|unique_custom:stocks,code,branch_id,' . Auth::user()->branch_id,
                    'composition' => "max:50",
                    'selling_price' => 'numeric|required',
                    'product_type' => "required",
                    'wholesale_price' => 'numeric|required',
                    'cost_price' => 'numeric|required',
                    'production_cost' => 'numeric|required',
                    'qty' => 'numeric|required',
                    'reorder_qty' => 'numeric|required',
                    'description' => 'nullable',


                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'name' => "required",
                    'product_name' => 'required|max:200|edit_unique_custom:suppliers,product_name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'code' => 'required|max:20|edit_unique_custom:suppliers,code,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'unit_id' => "required",
                    'composition' => "max:50",
                    'product_category_id' => "required",
                    'selling_price' => 'numeric|required',
                    'product_type' => "required",
                    'wholesale_price' => 'numeric|required',
                    'cost_price' => 'numeric|required',
                    'production_cost' => 'numeric|required',
                    'qty' => 'numeric|required',
                    'reorder_qty' => 'numeric|required',
                    'description' => 'nullable',
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

            'product_name.unique_custom' => 'The Product Name  Exist.',
            'product_name.edit_unique_custom' => 'The Product Name  Exist.',
            'code.unique_custom' => 'The code  exists',
            'code.edit_unique_custom' => 'The code  exists',
            'unit_id.required' => "Select unit"
        ];
    }
}
