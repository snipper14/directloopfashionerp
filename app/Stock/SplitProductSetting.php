<?php

namespace App\Stock;

use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SplitProductSetting extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'original_product_id',
        'final_product_id',
        'final_qty',
        'user_id',
        'branch_id'

    ];
    public function original_product()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function final_product()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
