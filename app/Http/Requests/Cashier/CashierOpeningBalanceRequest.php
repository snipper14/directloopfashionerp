<?php

namespace App\Http\Requests\Cashier;

use Illuminate\Foundation\Http\FormRequest;

class CashierOpeningBalanceRequest extends FormRequest
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
                    'opening_time' => "required",
                    'opening_amount' => 'numeric|required',

                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => 'required',
                    'opening_time' => "required",
                    'opening_amount' => 'numeric|required',

                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
