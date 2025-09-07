<?php

namespace App\Stock;

use App\User;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MergeStock extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'stock_id',
        'deleted_stock_id',
        'qty_merged',
        'branch_id',
        'user_id',
    ];

    function stock(){
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(["stock"=>""]);
    }
    function deletedStock(){
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(["deleted_stock"=>""]);
    }
    function user(){
        return $this->belongsTo(User::class)->withDefault(["user"=>'']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
