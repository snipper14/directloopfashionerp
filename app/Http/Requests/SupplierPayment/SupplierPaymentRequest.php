<?php

namespace App\Http\Requests\SupplierPayment;

use Illuminate\Foundation\Http\FormRequest;

class SupplierPaymentRequest extends FormRequest
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
                    'amount_paid'=>"numeric|required",
                    'outstanding_balance'=>"required",
                    "ref_no"=>"required",
                    'date_paid'=>"date|required",
                    'payment_details'=>"nullable",
                    'pay_method'=>"required",
                    "supplier_invoice_id"=>"nullable",
                    "invoice_amount"=>"nullable",
                    'invoice_data'=>'present|array',
                    'description'=>"nullable",
                    'account_id'=>"required",
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'supplier_id'=>"required",
                    'amount_paid'=>"numeric|required|between:1,100000000",
                    'outstanding_balance'=>"required",
                    "ref_no"=>"required",
                    'date_paid'=>"date|required",
                    'payment_details'=>"nullable",
                    'pay_method'=>"required"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }

 
}
