<?php

namespace App\Http\Requests\Assets;

use Illuminate\Foundation\Http\FormRequest;

class AssetMaintenanceRequest extends FormRequest
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
                    'asset_id' => 'required',
                    'priority' => 'required',
                    'type' => 'required',

                    'description' => 'nullable',
                    'maintenance_start_date' => 'required'



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

           
        ];
    }
}
