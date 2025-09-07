<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

class OnlineSaleRequest extends FormRequest
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
                    'card_pay' => 'numeric|min:0',
                    'mpesa_pay' => 'numeric|min:0',
                    'cash_pay' => 'numeric|min:0',
                    'payment_ref' => 'nullable',
                    'order_no' => 'required',
                    'receipt_no' => 'required',
                    'paid_date' => 'required',
                    'receipt_total' => "required|numeric|min:1",
                    'total_vat' => "numeric",


                    'order_date' => "required",

                    'receipt_balance' => "numeric",
                    'cashier_status' => "max:200"
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
