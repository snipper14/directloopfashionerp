<?php

namespace App\Disassembly;

use App\Stock\Stock;
use App\dept\Department;
use App\Scopes\BranchScope;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockDisassembly extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'child_stock_id',
        'stock_id',
        'child_qty',
        'child_cost',
        'child_totalcost',
        'parent_qty',
        'parent_cost_price',
        'parent_totalcost',
        'issue_no',
        'parent_department_id',
        'child_department_id',
        'disassembly_date',
        'production_no',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function child_stock()
    {
        return $this->belongsTo(Stock::class,'child_stock_id')->withDefault(['child_stock'=>""])->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class,'stock_id')->withDefault(['stock'=>""])->withTrashed();
    }
    public function parent_department()
    {
        return $this->belongsTo(Department::class,'parent_department_id')->withDefault(['parent_department'=>""])->withTrashed();
    }
    public function child_department()
    {
        return $this->belongsTo(Department::class,'child_department_id')->withDefault(['child_department'=>""])->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id')->withDefault(['user'=>""])->withTrashed();
    }
}
