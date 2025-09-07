<?php

namespace App\Rules;

use App\Sale\AllSaleDetailsReport;
use Illuminate\Contracts\Validation\Rule;

class ValidatePostSaleEtimsCodeAvailability implements Rule
{
   /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $items = [];
    public function __construct()
    {
        //
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
        $order_no = $value;
        $res = AllSaleDetailsReport::with(['stock'])->where(['order_no' => $order_no])->get();
        $count = 0;
        if (count($res) > 0) {
            foreach ($res as $value) {
                if (is_null($value['stock']['etims_code'])) {
                    $this->items[] = $value['stock']['product_name'];
                    $count++;
                }
            }
        }
        if ($count > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return json_encode($this->items).' Items not on etims';
    }
}
