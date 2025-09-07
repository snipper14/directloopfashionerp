<?php

namespace App\Http\Requests\Reception;

use Illuminate\Foundation\Http\FormRequest;

class ReceptionClosingBalanceRequest extends FormRequest
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
                $data = json_decode($this['data'], true);

                $this->rules = [
                    'closing_time' => "nullable",
                    'opening_time' => "nullable",

                    'closing_amount' => 'numeric|required|min:1',
                    'pending_closing' => "required|numeric",
                    'sold_amount' => 'numeric|required|min:1',
                    "cash_amount" => "numeric|required",
                    "bank_transfer_amount" => "numeric|required",
                    "mobile_money" => "numeric|required",
                    "online_paid" => "numeric|required",
                    "credit_card" => "numeric|required",
                    "cheque" => "numeric|required",
                    'deficit_amount' => "numeric|required",
                    'details' => 'nullable',
                    'total_amount' => "numeric|required",

                    'cash_sold_amount' => "numeric|required",
                    'mobile_money_sold_amount' => "numeric|required",
                    'online_sold_amount'=>"numeric|required",
                    'card_sold_amount' => "numeric|required",
                    'bank_transfer_sold_amount' => "numeric|required",
                    'cheque_sold_amount' => "numeric|required",
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',

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

            'id.required' => 'There was not corresponding opening balance from this .user.',

        ];
    }
}
