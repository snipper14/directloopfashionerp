<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class AssetAllocationRequest extends FormRequest
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
                    'allocation_code' => 'required|unique:asset_allocations,allocation_code,NULL,id,deleted_at,NULL',
                    'asset_id' => 'required',
                    'employee_id' => 'required',
                    'allocated_from' => 'required',
                    'allocated_to' => 'required',
                    'qty' => 'numeric|gte:1',

                    'reason' => 'nullable',




                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'allocation_code' => 'nullable|unique:asset_allocations,allocation_code,' . $this->id . ',id,deleted_at,NULL',

                    'id' => 'required',

                    'asset_id' => 'required',
                    'employee_id' => 'required',
                    'allocated_from' => 'required',
                    'allocated_to' => 'required',
                    'qty' => 'numeric|gte:1',

                    'reason' => 'nullable',

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
