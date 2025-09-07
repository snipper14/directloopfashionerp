<?php

namespace App\Http\Requests\SupplierInvoice;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class SupplierInvoiceRequest extends FormRequest
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
                  'invoice_no' => 'required|three_unique_custom:supplier_invoices,invoice_no,branch_id,' . Auth::user()->branch_id . ',supplier_id,' . $this->supplier_id, 
                    'invoice_total'=>"numeric|required|gte:0",
                    'invoice_date'=>"required|date",
                    'description'=>"required",
                    'po_no'=>'nullable'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'supplier_id'=>"required",
                    'invoice_no'=>'required|edit_unique_custom:supplier_invoices,invoice_no,branch_id,' . Auth::user()->branch_id.',supplier_id,'.$this->supplier_id,
                     'invoice_total'=>"numeric|required|between:1,99999999999999",
                    'invoice_date'=>"required|date",
                    'description'=>"nullable",
                    'po_no'=>'nullable'
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
