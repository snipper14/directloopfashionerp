<?php

namespace App\Http\Requests\Refund;

use Illuminate\Foundation\Http\FormRequest;

class RetailsSaleRefundRequest extends FormRequest
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

                    'account_id' => 'required',
                    'refund_amount' => 'required|numeric|gt:0',
                    'ref_details' => 'required',
                    'credit_date' => 'required',
                    'comments' => 'nullable',
                    'receipt_no' => 'required',
                    'credit_no' => 'required',
                     'pay_method'=>'required'
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
}
