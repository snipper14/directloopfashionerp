<?php

namespace App\Http\Requests\CreditNote;

use Illuminate\Foundation\Http\FormRequest;

class CreditNoteRequest extends FormRequest
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
                    'invoice_no' => "required",
                    'invoice_id' => "required",
                    'department_id' => 'required',
                    'qty' => "numeric|required|gt:0",
                    'price' => "required|numeric|gt:0",
                    'line_total' => "required|numeric|gt:0",
                    'reduce_stock' => 'required',
                    'customer_id' => 'required',
                    'credit_date' => 'required|date',
                    'row_vat'=>"required|numeric|gte:0"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'invoice_no' => "required",
                    'invoice_id' => "required",

                    'qty' => "numeric|required|between:1,999999999999",
                    'price' => "required|numeric|between:1,99999999999999",
                    'line_total' => "required|numeric|between:1,99999999999999",
                    'row_vat'=>"required|numeric|gte:0"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
