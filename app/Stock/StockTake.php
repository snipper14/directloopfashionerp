<?php

namespace App\Stock;

use App\User;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class StockTake extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'stock_take_type', //['increment', 'decrement']
        'qty',
        'stock_id',
        'type',
    ];

    public function user()
    {
        return  $this->belongsTo(User::class)->withTrashed()->withDefault(['user'=>'']);
    }
    public function stock()
    {
        return  $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock'=>'']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
