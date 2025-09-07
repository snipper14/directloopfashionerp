<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

class TakeAwaySaleRequest extends FormRequest
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
                    'order_no' => "required",
                    'receipt_no' => "required",
                    'customer_id' => "required",
                    'user_id' => "required",

                    'order_date' => "required",
                    'paid_date' => "required",
                    'qty' => "numeric",

                    'receipt_total' => "numeric|required",
                    'total_vat' => "numeric|required",

                    'cash_pay' => 'numeric|required',
                    'mpesa_pay' => 'numeric|required',
                    'card_pay' => 'numeric|required',
                    'credit_pay' => 'numeric|required',
                    'order_type' => "required",
                    'payment_ref' => "nullable",
                    'receipt_balance' => 'numeric'

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
