<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
                    //'company_name' => 'required|max:250|unique_custom:suppliers,company_name,branch_id,' . Auth::user()->branch_id,
                    'company_name' => 'required|unique:suppliers,company_name,NULL,id,deleted_at,NULL',
                    'company_phone' => 'nullable',
                    'address' => 'nullable|max:200',
                    'postal_address' => 'nullable|max:200',
                    'city' => 'nullable|max:200',
                    'country' => 'nullable|max:200',
                    'company_email' => 'nullable',
                    // 'company_email' => 'nullable|email|unique_custom:suppliers,company_email,branch_id,' . Auth::user()->branch_id,
                    'bank_name' => 'nullable',
                    'bank_branch' => 'nullable',
                    'account_no' => 'nullable',
                    'contact_person' => 'max:200',
                    'contact_email' => 'nullable',
                    'contact_phone' => 'max:200',
                    'pin' => 'nullable',
                    'credit_limit'=>'numeric|required',
                    'deduct_wht'=>'max:20'

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'company_name' => 'required|unique:suppliers,company_name,' . $this->id . ',id,deleted_at,NULL',
                    //'company_name' => 'required|max:250|edit_unique_custom:suppliers,company_name,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
                    'id' => 'required',
                    'company_phone' => 'max:200',
                    'address' => 'nullable|max:200',
                    'postal_address' => 'nullable|max:200',
                    'city' => 'max:200',
                    'country' => 'max:200',
                    'company_email' => 'nullable',
                    'bank_name' => 'nullable',
                    'bank_branch' => 'nullable',
                    'account_no' => 'nullable',
                    'contact_person' => 'max:200',
                    'contact_email' => 'nullable|email',
                    'contact_phone' => 'max:200',
                    'pin' => 'nullable',
                    'credit_limit'=>'numeric|required',
                    'deduct_wht'=>'max:20'
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

            'company_name.unique_custom' => 'The company_name  should be unique.',
            'company_name.edit_unique_custom' => 'The company_name  should be unique.',
            'acc_code.unique_custom' => 'The code  should be unique.',
            'acc_code.edit_unique_custom' => 'The code  should be unique.',
            'company_email.unique_custom' => 'The company email should be unique.',
            'company_email.edit_unique_custom' => 'The company email should be unique.',
        ];
    }
}
