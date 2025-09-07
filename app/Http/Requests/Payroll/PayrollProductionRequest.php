<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class PayrollProductionRequest extends FormRequest
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

                    'produced_on' => 'required',
                    'stock_id' => 'required',
                    'employee_id' => 'required',
                    'qty' => 'numeric|required',
                    'production_cost' => "required|gt:0",
                    'total_pay' => "required|gt:0",
                    'description' => 'max:200'


                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'produced_on' => 'required',
                    'payroll_product_id' => 'required',
                    'employee_id' => 'required',
                    'qty' => 'numeric|required',
                    'size' => 'max:200',
                    'colour' => 'max:200',
                    'total_pay' => "max:200",
                    'description' => 'max:200'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
