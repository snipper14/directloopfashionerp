<?php

namespace App\Http\Requests\DispatchReq;

use Illuminate\Foundation\Http\FormRequest;

class DispatchReqRequest extends FormRequest
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

                    'stock_id' => 'required',
                    'user_id' => "required",

                    'order_qty' => 'numeric|required|between:0,999999999.99',
                    'pending_qty' => 'numeric|required|between:0,999999999.99',
                    'dispatch_qty' => 'numeric|required',
                    'issue_date' => 'required',
                    'decrementStock' => "nullable",
                    'details' => 'nullable',
                    'req_id' => 'required'
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                    'id' => "required",
                    'stock_id' => 'required',
                    'user_id' => "required",
                    'decrementStock' => "nullable",
                    'order_qty' => 'numeric|required|between:0,999999999.99',
                    'pending_qty' => 'numeric|required|between:0,999999999.99',
                    'dispatch_qty' => 'numeric|required',
                    'issue_date' => 'required',

                    'details' => 'nullable',
                    'req_id' => 'required'
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }
}
