<?php

namespace App\Http\Requests\Commission;

use Illuminate\Foundation\Http\FormRequest;

class CommissionRequest extends FormRequest
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
                    'employee_id'=>"required",
                    'payment_month'=>"required",
                    'description'=>'required|min:3|max:200',
                    'amount'=>'required|numeric',
                    'type'=>'required|min:3|max:200',

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'employee_id'=>"required",
                    'payment_month'=>"required",
                    'description'=>'required|min:3|max:200',
                    'amount'=>'required|numeric',
                    'type'=>'required|min:3|max:200',
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
