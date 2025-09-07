<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockIssueConversionRequest extends FormRequest
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
                    'issued_staff_id' => "required",
                    'parent_stock_id' => "required",
                    'mapping_value' => 'numeric|required',
                    'department_id' => "required",
                    'department_to_id' => 'required',
                    'issue_no' => "required",
                    'qty_issued' => "numeric|gt:0",
                    'parent_issue_qty' => "numeric|required",
                    'date_issued' => "required"

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'stock_id' => "required",

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
            "department_id.required" => "Department required",
            "department_to_id.required" => "Destination department required",
            "stock_id.required" => "child product required",
            "parent_stock_id.required" => "Parent product required",
            "issued_staff_id.required" => "Staff required"
        ];
    }
}
