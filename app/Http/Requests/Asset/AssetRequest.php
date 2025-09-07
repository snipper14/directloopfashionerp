<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
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
                    'asset_name' => 'required|unique:assets,asset_name,NULL,id,deleted_at,NULL',
                    'asset_code' => 'required',
                    'department_id' => 'required',
                    'asset_code'=>'required',
                    'qty' => 'numeric|gte:1',
                    'model_name' => 'nullable|max:200',
                    'serial_no' => 'nullable',
                    'asset_category_id' => 'required',
                    'purchase_date' => 'date',
                    'purchase_price' => 'numeric|required|gt:0',
                    'purchase_type' => 'required',
                    'monthly_depreciation' =>'numeric|gte:0',
                    'description' => 'nullable',
                   



                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'asset_name' => 'nullable|unique:assets,asset_name,' . $this->id . ',id,deleted_at,NULL',

                    'id' => 'required',
                    
                    'asset_code' => 'required',
                    'department_id' => 'required',
                    'asset_code'=>'required',
                    'qty' => 'numeric|gte:1',
                    'model_name' => 'nullable|max:200',
                    'serial_no' => 'nullable',
                    'asset_category_id' => 'required',
                    'purchase_date' => 'date',
                    'purchase_price' => 'numeric|required|gt:0',
                    'purchase_type' => 'required',
                    'monthly_depreciation' =>'numeric|gte:0',
                    'description' => 'nullable',

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
