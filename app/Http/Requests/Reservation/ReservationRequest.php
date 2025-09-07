<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class ReservationRequest extends FormRequest
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

                    'guest_id' => 'required',
                    'guest_company_id'=>"required",
                    'name' => 'required',
                    'id_no' => 'nullable',
                    'phone' => 'nullable',
                    'gender' => 'required',
                    'no_guest' => 'numeric|min:1',
                    'from' => 'required',
                    'to' => 'required',
                    'details' => 'nullable',
                    'paid' => "required",
                    'price' => 'required|numeric|min:1',
                    'total' => "required|numeric|min:1",
                    'qty' => "numeric|required|min:1",
                    'house_keeper_id' => "nullable",
                    'waiter_id' => "nullable",
                    'room_id' => 'required',
                    'room_rate_id' => "required",

                    'ref_no' => 'nullable',
                    'room_package_id' => "required",
                    'tax_amount' => "numeric|required",
                    'tax_rate' => "numeric|required",
                    'amount_paid' => "numeric|required",

                    'cash_paid' => "numeric|required",
                    'mobile_money_paid' => "numeric|required",
                    'online_paid' => "numeric|required",
                    'card_paid' => "numeric|required",
                    'cheque_paid' => "numeric|required",
                    'bank_transfer_paid' => "numeric|required",
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'guest_company_id'=>"nullable",
                    
                    'id_no' => 'nullable',
                    'phone' => 'nullable',
                  
                    'no_guest' => 'numeric',
                   
                    'from' => 'required',
                    'to' => 'required',
                   
                    'paid' => "required",
                    'price' => 'required|numeric',
                    'total' => "required|numeric",
                    'qty' => "numeric|required|min:1",
                    'house_keeper_id' => "nullable",
                    'waiter_id' => "nullable",
                   
                    'tax_amount' => "numeric|required",
                    
                    'amount_paid' => "numeric|required",

                    'cash_paid' => "numeric|required",
                    'mobile_money_paid' => "numeric|required",
                    'online_paid' => "numeric|required",
                    'card_paid' => "numeric|required",
                    'cheque_paid' => "numeric|required",
                    'bank_transfer_paid' => "numeric|required",
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
            'guest_company_id.required'=>"Select company ",
            'guest_id.required' => 'The guest has not been selected',
            'house_keeper_id.required' => 'Select house keeper',
            'waiter_id.required' => 'Assign waiter.',

        ];
    }
}
