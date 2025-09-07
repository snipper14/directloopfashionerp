<?php

namespace App\Http\Requests\RoomOrder;

use Illuminate\Foundation\Http\FormRequest;

class RoomOrderRequest extends FormRequest
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

                    'guest_id'=>"required",
                    'room_id'=>"required",
                    'isBar'=>"required",
                    'room_package_id'=>"required",
                    'stock_id'=>"required",
                    'unit_id'=>"required",
                    'room_reservation_id'=>"required",
                    'qty'=>"required|numeric|min:1",
                    'price'=>"required|numeric",
                    'row_total'=>"required|numeric",
                    'row_vat'=>"required|numeric",
                    'vat_rate'=>"required|numeric",
                    'no_guest'=>"required|numeric",
                    'department_id'=>"required",
                    'notes'=>"nullable",
                    'order_date'=>"required",
                    'order_no'=>"required",
                    'prev_order_no'=>"nullable",
                    'order_type'=>"required",
                    'service_charge_amount'=>"numeric|required",
                    'service_charge_rate'=>"numeric|required",
                    'cost_price'=>"min:0",
                    'cost_total'=>"min:0",
                    'sub_total'=>"numeric|required",
                    'accompaniment_id'=>"nullable"
                ];
                break;
                // in case of edit
            case 'PUT':
                $this->rules = [
                   'id'=>"required",
                   'guest_id'=>"required",
                   'room_id'=>"required",
                   'room_package_id'=>"required",
                   'stock_id'=>"required",
                   'unit_id'=>"required",
                   'room_reservation_id'=>"required",
                   'qty'=>"required|numeric|min:1",
                   'price'=>"required|numeric",
                   'row_total'=>"required|numeric",
                   'row_vat'=>"required|numeric",
                   'vat_rate'=>"required|numeric",
                   'no_guest'=>"required|numeric",
                   'notes'=>"nullable",
                   'order_date'=>"required",
                   'order_no'=>"required",
                   'prev_order_no'=>"nullable",
                   'order_type'=>"required",
                   'service_charge_amount'=>"numeric|required",
                    'service_charge_rate'=>"numeric|required",
                    'cost_price'=>"min:0",
                    'cost_total'=>"min:0",
                    'sub_total'=>"numeric"
                ];
                break;

            default:
                break;
        }

        return $this->rules;
    }

}
