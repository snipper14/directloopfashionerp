<?php

namespace App\Http\Requests\SupplierInvoiceParticulars;

use Illuminate\Foundation\Http\FormRequest;

class SupplierInvoiceParticularsRequest extends FormRequest
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

                    'supplier_invoice_id' => "required",
            'invoice_no' => "required",
            'stock_id' => "required",
          
            'line_total' => "numeric|required|between:1,99999999999999",
            'price' => "numeric|required|between:1,99999999999999",
            'qty' => "numeric|required|between:1,99999999999999",
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'supplier_invoice_id' => "required",
                    'invoice_no' => "required",
                    'stock_id' => "required",
                  
                    'line_total' => "numeric|required|between:1,99999999999999",
                    'price' => "numeric|required|between:1,99999999999999",
                    'qty' => "numeric|required|between:1,99999999999999",
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
            'stock_id.required' => 'Product is missing!!!',
          
            'supplier_invoice_id.required'=>'Save invoice first in order to add particulars'
        ];
    }
}
