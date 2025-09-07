<?php

namespace App\Http\Requests\LedgerAccount;

use Illuminate\Foundation\Http\FormRequest;

class LedgerAccountRequest extends FormRequest
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
                    'account' => 'required|unique:ledger_accounts,account,NULL,id,deleted_at,NULL',
                    'description' => 'nullable',

                    'account_type' => 'required',
                    'category' => 'required',
                    'ledger_sub_account_id'=>"required"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'account' => 'nullable|unique:ledger_accounts,account,' . $this->id . ',id,deleted_at,NULL',
                    'id' => 'required',
                    'description' => 'nullable',
                    'account_type' => 'required',
                    'category' => 'required',
                     'ledger_sub_account_id'=>"required"


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

            'account.required' => 'The Account  already in created',

        ];
    }
}
