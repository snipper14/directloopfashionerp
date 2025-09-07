<?php

namespace App\Rules;

use App\Stock\SplitProductSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Rule;

class ValidateUniqueSplitProduct implements Rule
{
    protected $original_product_id;
    public function __construct($original_product_id)
    {
        $this->original_product_id = $original_product_id;
    }

    public function passes($attribute, $value)
    {
        $res = SplitProductSetting::where([
            'final_product_id' => $value,
            'original_product_id' => $this->original_product_id,
            'branch_id' => Auth::user()->branch_id
        ])->get();
        if ($res->isEmpty()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Product   already exists';
    }
}
