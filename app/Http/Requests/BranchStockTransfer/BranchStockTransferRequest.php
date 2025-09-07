<?php

namespace App\Http\Requests\BranchStockTransfer;

use Illuminate\Foundation\Http\FormRequest;

class BranchStockTransferRequest extends FormRequest
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
                    'stock_id' => "required",
                    'department_id' => 'required',
                    'note' => 'nullable',
                    'branch_to_id' => "required",
                    'qty' => "numeric|required",
                    'transfer_date' => "required",
                    'reference_code' => "nullable",
                   
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

            'stock_id.required' => 'Stock required',
            'department_id.required' => 'TSelect original stock location',
            'branch_to_id.required' => 'Select destination branch.',
            'acc_code.edit_unique_custom' => 'The code  should be unique.',
            'company_email.unique_custom' => 'The customer email should be unique.',
            'company_email.edit_unique_custom' => 'The customer email should be unique.',
        ];
    }
}
