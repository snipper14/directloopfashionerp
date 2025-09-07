<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

class RoomServiceSaleRequest extends FormRequest
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
                    'guest_id' => "required",
                    'user_id' => "required",

                    'order_date' => "required",
                    'paid_date' => "required",
                    'qty' => "numeric",

                    'receipt_total' => "numeric|required",
                    'total_vat' => "numeric|required",
                    'service_charge_total' => "numeric",
                    'cash_pay' => 'numeric|min:0',
                    'mpesa_pay' => 'numeric|min:0',
                    'card_pay' => 'numeric|min:0',
                    'credit' => 'numeric|min:0',
                    'order_type' => "required",
                    'payment_ref' => "nullable",
                    'receipt_balance' => 'numeric',
                    'cashier_status'=>"max:200"

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
