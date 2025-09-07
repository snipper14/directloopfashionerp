<?php

namespace App\Http\Requests\Assembly;

use Illuminate\Foundation\Http\FormRequest;

class AssemblyRequisitionRequest extends FormRequest
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
                    'department_id'=>"required",
                    'stock_id'=>"required",
                    'qty'=>'numeric|required',
                    'date_due'=>"required",
                    
                    'req_id'=>"required",
                    'details'=>"nullable",

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'department_id'=>"required",
                    'stock_id'=>"required",
                    'qty'=>'numeric|required',
                    'date_due'=>"required",
                    
                    'req_id'=>"required",
                    'details'=>"nullable",
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
