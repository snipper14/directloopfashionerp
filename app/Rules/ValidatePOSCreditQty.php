<?php

namespace App\Rules;

use App\POS\POSCredit;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Contracts\Validation\Rule;

class ValidatePOSCreditQty implements Rule
{
    protected $all_sale_details_report_id;
    protected $receipt_qty;

    public function __construct($all_sale_details_report_id, $receipt_qty)
    {
        $this->all_sale_details_report_id = $all_sale_details_report_id;
        $this->receipt_qty = $receipt_qty;
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

        if ($this->receipt_qty < $value) {

            return false;
        } else {

            $res_cr = POSCredit::where(['all_sale_details_report_id' => $this->all_sale_details_report_id])->selectRaw('SUM(qty) AS tt_qty')->first();
            if ($res_cr) {
                $sum_qty = $res_cr->tt_qty + $value;
                if ($this->receipt_qty >= $sum_qty) {
                    //echo $this->receipt_qty . ">>>" . $sum_qty;
                    return true;
                } else {
                   
                    return false;
                }
            } else {

                return true;
            }
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Credit amount surpassed receipt amount';
    }
}
