<?php

namespace App\Rules;

use App\Stock\MixedStockMovt;
use Illuminate\Contracts\Validation\Rule;

class HasStoreStockMovtUpdated implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
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
        $report_date=request('report_date');
        $query = MixedStockMovt::where(['date_recorded' => $report_date])->get();
        if(!$query->isEmpty()){
            return false;
        }else{
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
        return 'Stock Movt has already been recorded.';
    }
}
