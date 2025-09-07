<?php

namespace App\Http\Requests\Statutory;

use Illuminate\Foundation\Http\FormRequest;

class StatutoryRequest extends FormRequest
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
                 
                    'statutory_type'=>"required",
                    'salary_from'=>'numeric|required|between:0,999999999.99',
                    'salary_to'=>'numeric|required|between:0,999999999.99',
                    'amount_deducted'=>'numeric|between:0,999999999.99',
                    "percentage_deduction"=>'numeric|between:0,999999999.99',
                  
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                   'id'=>"required",
                   'statutory_type'=>"required",
                   'salary_from'=>'numeric|required|between:0,999999999.99',
                   'salary_to'=>'numeric|required|between:0,999999999.99',
                   'amount_deducted'=>'numeric|between:0,999999999.99',
                   "percentage_deduction"=>'numeric|between:0,999999999.99',
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }

   
}
