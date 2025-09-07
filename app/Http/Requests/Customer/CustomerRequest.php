<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CustomerRequest extends FormRequest
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
                    'company_name' => 'required|unique:customers,company_name,NULL,id,deleted_at,NULL',
                    'acc_code' => 'nullable',
                    'company_phone' => 'nullable',
                    'address' => 'nullable|max:200',
                    'postal_address' => 'nullable|max:200',
                    'city' => 'nullable|min:2|max:200',
                    'country' => 'nullable|min:2|max:200',
                    'company_email' => 'nullable',
                    'bank_name' => 'nullable',
                    'bank_branch' => 'nullable',
                    'account_no' => 'nullable',
                    'contact_person' => 'nullable',
                    'contact_email' => 'nullable|email',
                    'contact_phone' => 'sometimes',
                    'pin' => 'nullable',
                    'credit_limit' => 'numeric|required',
                    'vat_deduction' => 'required',
                    'price_group_id'=>'nullable',
                    'loyalty_program'=>'required',
                    'is_discount_qualified'=>'required'

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'company_name' => 'nullable|unique:customers,company_name,' . $this->id . ',id,deleted_at,NULL',

                    'id' => 'required',
                    'acc_code' => 'nullable',

                    'company_phone' => 'nullable',
                    'address' => 'nullable|max:200',
                    'postal_address' => 'nullable|max:200',
                    'city' => 'nullable',
                    'country' => 'nullable',
                    'company_email' => 'nullable',

                    'bank_name' => 'nullable',
                    'bank_branch' => 'nullable',
                    'account_no' => 'nullable',
                    'contact_person' => 'nullable',
                    'contact_email' => 'nullable',
                    'contact_phone' => 'nullable',
                    'pin' => 'nullable',
                    'credit_limit' => 'numeric|required',
                    'vat_deduction' => 'required',
                    'price_group_id'=>'nullable',
                    'loyalty_program'=>'required',
                    'is_discount_qualified'=>'required'
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

            'company_name.unique_custom' => 'The Customer  should be unique.',
            'company_name.edit_unique_custom' => 'The Customer  should be unique.',
            'acc_code.unique_custom' => 'The code  should be unique.',
            'acc_code.edit_unique_custom' => 'The code  should be unique.',
            'company_email.unique_custom' => 'The customer email should be unique.',
            'company_email.edit_unique_custom' => 'The customer email should be unique.',
        ];
    }
}
