<?php

namespace App\Http\Requests\Payment;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class CustomerPaymentRequest extends FormRequest
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

                    'customer_id' => 'required',

                    'amount_paid' => "numeric|required",
                    'amount_pending' => "numeric|required",
                    'ref_no' => 'required',
                    'date_paid' => "required|date",
                    'payment_details' => "nullable",
                    'pay_method' => 'required',
                    'invoice_id' => "nullable",
                    'invoice_amount' => "gte:0",
                    'description'=>"nullable",
                    'account_id'=>"required",
                    'invoice_data'=>'present|array'
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
