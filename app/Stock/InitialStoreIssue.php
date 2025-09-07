<?php

namespace App\Stock;

use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\Branch\Branch;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InitialStoreIssue extends Model
{
    use SoftDeletes;
    protected $fillable =[
        'issue_no',
        'stock_id',
        'branch_id',
        'unit_id',
        'user_id',
        'opening_stock',
        'status', //['complete', 'progress'])->default('progress');
        'qty_issued',
       
        'qty_received',
        'row_total',
        'unit',
        'date_issued',
        'date_received',
        'purchase_price',
        'isReceived',//[0,1]
        'isSync'//[0,1]
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock'=>'']);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed()->withDefault(['unit'=>'']);
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department'=>'']);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user'=>'']);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed()->withDefault(['branch'=>'']);
    }
}
