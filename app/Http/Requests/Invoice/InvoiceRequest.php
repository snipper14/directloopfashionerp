<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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


                    'customer_id' => "required",
                    "invoice_date" => "required|date",
                    'department_id' => 'required',
                    'order_no' => "required",

                    'order_data' => 'present|array',
                    'sum_invoice' => "gt:0"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",

                    'customer_id' => "required",
                    "invoice_date" => "required|date",
                    'department_id' => 'required',
                    'order_no' => "required",

                    'order_data' => 'present|array',
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
