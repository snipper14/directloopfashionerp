<?php

namespace App\Stock;

use App\Branch\Branch;
use App\Stock\Stock;
use App\Location\Shelf;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branchshelf extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'shelf_id',
        'stock_id'
    ];
    function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => '']);
    }
    function shelf()
    {
        return $this->belongsTo(Shelf::class)->withDefault(['shelf' => '']);
    }
    function branch(){
        return $this->belongsTo(Branch::class)->withDefault(['branch'=>'']);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
