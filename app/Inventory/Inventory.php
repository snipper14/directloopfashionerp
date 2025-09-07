<?php

namespace App\Inventory;

use App\Stock\Stock;
use App\Branch\Branch;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stock_id',
        'department_id',
        'branch_id',
        'qty',

    ];
    public function scopeExcludeSoftDeletedStock(Builder $query)
    {
        return $query->whereHas('stock', function ($query) {
            $query->whereNull('deleted_at');
        });
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withDefault(['department' => ''])->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withDefault(['stock' => ''])->withTrashed();//->whereNull('stocks.deleted_at');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withDefault(['branch' => ''])->withTrashed();
    }

   
}
