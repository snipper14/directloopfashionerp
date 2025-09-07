<?php

namespace App\Http\Requests\CreditNote;

use Illuminate\Foundation\Http\FormRequest;

class DirectCreditNoteRequest extends FormRequest
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
                    'credit_no' => "required",
                   
                    'qty' => "numeric|required|gt:0",
                    'price' => "required|numeric|gt:0",
                    'line_total' => "required|numeric|gt:0",
                   'stock_id'=>"required",
                    'customer_id' => 'required',
                    'credit_date' => 'required|date',
                    'row_vat'=>"required|numeric|gte:0"
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
}
