<?php

namespace App\Rules;

use App\Stock\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\Rule;

class ValidateSplitQty implements Rule
{
    protected $original_product_id;
    public function __construct($original_product_id)
    {
        $this->original_product_id = $original_product_id;
    }

    public function passes($attribute, $value)
    {
        DB::enableQueryLog();
        $res = Stock::where('qty', '>=', $value)->where([

            'id' => $this->original_product_id,

        ])->get();
        if ($res->isEmpty()) {
            return false;
        } else {
            return true;
        }
        print_r(DB::getQueryLog());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Product qty is lower than amount to split';
    }
}
