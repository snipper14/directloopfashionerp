<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest
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
                    'name' => "required|unique:product_categories,name,NULL,id,deleted_at,NULL",
                    'department_id' => "required",
                    'details' => "max:250"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                    'department_id' => "required",
                    'name' => "required|unique:product_categories,name,{$this->id},id,deleted_at,NULL",
                    'details' => "max:250"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
