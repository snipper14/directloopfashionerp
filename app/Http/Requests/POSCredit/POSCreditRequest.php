<?php

namespace App\Http\Requests\POSCredit;

use Illuminate\Foundation\Http\FormRequest;

class POSCreditRequest extends FormRequest
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
                    'all_sale_details_report_id' => "required",
                    'receipt_qty'=>'required|numeric',
                    'qty' => 'numeric|required',
                    'receipt_no' => "required",
                    'stock_id'=>'required',
                    'row_total'=>'numeric|required',
                   'row_vat'=>'numeric|required',
                   'credit_sp'=>'numeric|required',
                   'credit_vat'=>'numeric|required',
                   'credit_amount'=>'numeric|required',
                   'credit_date'=>'required',
                   'credit_no'=>'required',
                   'tax_rate'=>'numeric|required',
                   'cu_invoice_no'=>'nullable',
                   'deduct_stock'=>'required',
                   
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

            'location_id.required' => 'Select Location',
            'table_id.required' => 'Select Table',


        ];
    }
}
