<?php

namespace App\Http\Requests\Cashier;

use Illuminate\Foundation\Http\FormRequest;

class CashierClosingBalanceRequest extends FormRequest
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

                    'closing_time' => 'required',
                    'opening_time' => 'nullable',
                    'closing_amount' => 'numeric|required',
                    'pending_closing' => "required",
                    'sold_amount' => 'numeric|required',
                    'details' => 'nullable',
                    'cash_collected' => "numeric|required",
                    'mobile_money_collected' => 'numeric|required',
                    'card_collected' => 'numeric|required',

                    'cash_sold_amount' => "numeric|required",
                    'mobile_money_sold_amount' => 'numeric|required',
                    'card_sold_amount' => 'numeric|required',
                    'conference_sales'=>"numeric|required",
                    'cash_left_amount'=>'numeric|required',
                    'closing_cash_amount'=>'numeric|required',
                    'opening_amount'=>'numeric|required',
                    'invoice_payments'=>"numeric|required",
                    'cash_sales_amount'=>"numeric|required",
                    'expense_payments'=>"numeric|required"

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
