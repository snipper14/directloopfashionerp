<?php

namespace App\Http\Requests\SupplierCreditNote;

use Illuminate\Foundation\Http\FormRequest;

class SupplierCreditNoteRequest extends FormRequest
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

                    'supplier_id'=>"required",
                  'credit_no' => 'required', 
                  'supplier_invoice_id'=>'required',
                 
                    'credit_amount'=>"numeric|required|gte:0",
                    'invoice_total'=>"numeric|required|gte:0",
                    'credit_date'=>"required|date",
                    'details'=>"required",
                    'invoice_no'=>'required'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    
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

            'invoice_no.three_unique_custom' => 'The invoice number already exists  for this supplier',
           
        ];
    }
}
