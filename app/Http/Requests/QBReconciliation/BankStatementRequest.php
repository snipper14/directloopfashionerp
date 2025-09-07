<?php

namespace App\Http\Requests\QBReconciliation;

use Illuminate\Foundation\Http\FormRequest;

class BankStatementRequest extends FormRequest
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
                    'account_id' => 'required',
                    'transaction_date' => 'date',
                    'credit_amount' => 'required|numeric',
                    'debit_amount' => 'required|numeric',
                    'ref_no' => 'required',

                    'details' => 'required'


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

            'bank_recon_id.required' => 'selected transaction is incorrect',

        ];
    }
}
