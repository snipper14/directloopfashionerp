<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateCompleteIssueInternalStoreQty implements Rule
{

    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $count = 0;
        foreach ($value as $item_value) {
            $res = Stock::where('store_qty', '<', $item_value['qty_issued'])->where([
                'id' => $item_value['stock_id'],
                'composition' => 'whole',
                'branch_id' => Auth::user()->branch_id
            ])->first();

            if ($res) {
                $count++;
                // return false;
            }
        }
        if ($count > 0) {
            return false;
        } else {
            return true;
        }
        // foreach ($value as $item_value) {
        //     $res = Stock::where('store_qty', '>=', $item_value['qty_issued'])->where(['id' => $item_value['stock_id'], 'branch_id' => Auth::user()->branch_id])->first();

        //     if ($res) {

        //         return true;
        //     }
        // }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute  has less stock in database';
    }
}
