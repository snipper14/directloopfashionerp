<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockIssueRequest extends FormRequest
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
                    'unit_id' => "required",
                    'mapping_value' => 'numeric|required',
                    'purchase_price' => "numeric|required",
                    'unit' => "required",
                    'row_total' => "required|numeric",
                    'department_id' => "required",
                    'source_department_id'=>'required',
                    'issue_no' => "required",
                    'qty_issued' => "numeric",
                    'final_qty' => "numeric|required",
                    'opening_stock' => "numeric",
                    'product_department_id'=>"required",
                    'internal_issue_no'=>'nullable'
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
}
