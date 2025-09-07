<?php

namespace App\Http\Requests\Sale;

use App\Rules\ValidateRegisterActiveSales;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class POSSaleRequest extends FormRequest
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
        $branch =Auth::user()->branch; // Make sure 'branch_id' exists in the request
        
        // Check the enforce_customer_retail value
        $enforceCustomer = $branch && $branch->enforce_customer_retail == 1;
        switch ($method) {

            case 'POST':
                $this->rules = [
                    'card_pay' => 'numeric|min:0',
                    'mpesa_pay' => 'numeric|min:0',
                    'cash_pay' => 'numeric|min:0',
                    'credit_pay' => 'numeric|min:0',
                    'loyalty_points' => 'numeric|min:0',
                    'payment_ref' => 'nullable',
                    'order_no' => 'required',
                    'receipt_no' => 'required',
                    'paid_date' => 'required',
                    'receipt_total' => "required|numeric|gt:0",
                    'total_vat' => "numeric",
                    'discount_pay' => "numeric",
                    'discount_percent' => "numeric",


                    'order_date' => "nullable",
                    'card_terminal_account_id' => "nullable",
                    'receipt_balance' => "numeric",
                    'customer_id' =>  $enforceCustomer ? 'required' : 'nullable',
                    'customer_name' => "nullable",
                    'digitax_id'=>'nullable',
                    'external_ref' => [ new ValidateRegisterActiveSales($this)],
                    

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
    public function messages()
    {
        return [

            'customer_id.required' => 'Please select customer first',

        ];
    }
}
