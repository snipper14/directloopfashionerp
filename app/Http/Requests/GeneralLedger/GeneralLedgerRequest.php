<?php

namespace App\Http\Requests\GeneralLedger;

use Illuminate\Foundation\Http\FormRequest;

class GeneralLedgerRequest extends FormRequest
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
                    'credit_account_name' => 'required',
                    'debit_account_name' => "required",
                    'details'=>'required',
                    'amount' => 'required|gt:0',
                    'entry_date' => 'required',
                    "entry_code" => 'required',
                    "credit_account_type" => "required",
                    "debit_account_type" => "required",
                    "debit_account_id" => "required",
                    "credit_account_id" => "required"

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

            'debit_account_id.required' => 'Debit Account required',
            'credit_account_id.required' => 'Credit Account required'

        ];
    }
}
