<?php

namespace App\CashDrops;

use App\User;
use App\Scopes\BranchScope;
use App\Category\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CloseRegisterCategory extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'product_category_id',
        'user_id',
        'branch_id',
        'amount',
        'close_register_id'
    ];

    function productCategory(){
        return $this->belongsTo(ProductCategory::class)->withTrashed()->withDefault(['product_category'=>""]);
    }
    function user(){
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user'=>""]);
    }
        protected static function boot()
        {
            parent::boot();
    
            static::addGlobalScope(new BranchScope);
        }
}
