<?php

namespace App\Rules;

use App\Stock\Accompaniment;
use Illuminate\Contracts\Validation\Rule;

class CheckIfAccompanimentIsUnique implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $stock_id;
    public function __construct($stock_id)
    {
        $this->stock_id = $stock_id;
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
            $res = Accompaniment::where(['stock_id' => $this->stock_id, 'accompaniment_id' => $item_value])->first();

            if ($res) {
                $count += 1;
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
        return 'Duplicate items added.';
    }
}
