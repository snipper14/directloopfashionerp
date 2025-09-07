<?php

namespace App\Stock;

use App\User;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SplitProduct extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'branch_id',
        'original_product_id',
        'final_product_id',
        'split_product_setting_id',
        'qty_split',
        'final_qty'
    ];
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function original_product()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function final_product()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function split_product_setting()
    {
        return $this->belongsTo(SplitProductSetting::class)->withTrashed();
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
