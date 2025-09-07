<?php

namespace App\Http\Requests\Product;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class PayrollProductRequest extends FormRequest
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
                     
                    'name' => 'required|max:20|unique_custom:payroll_products,name,branch_id,' . Auth::user()->branch_id,
                    'product_code'=>"nullable",
                    'description'=>'nullable',
                    'size'=>'nullable',
                    'pay_rate'=>'numeric|required'
                
                  
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                   'name'=>"required",
                'name' => 'required|max:20|edit_unique_custom:payroll_products,name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                'product_code' => 'required|max:20|edit_unique_custom:payroll_products,product_code,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
               
                'description'=>'nullable',
                'size'=>'nullable',
                'pay_rate'=>'numeric|required'
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

            'name.unique_custom' => 'The Product Name  Exist.',
            'name.edit_unique_custom' => 'The Product Name  Exist.',
            'product_code.edit_unique_custom' => 'The Product code  Exist.',
            
          
        ];
    }
}
