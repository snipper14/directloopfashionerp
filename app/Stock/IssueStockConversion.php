<?php

namespace App\Stock;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IssueStockConversion extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'issue_no',
        'parent_stock_id',
        'stock_id',
        'branch_id',

        'department_id',
        'department_to_id',
        'user_id',
        'issued_staff_id',
        'status', //['complete', 'progress'])->default('progress');
        'qty_issued',
        'parent_issue_qty',

        'date_issued',
        'mapping_value',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function parent_stock()
    {
        return $this->belongsTo(Stock::class, 'parent_stock_id')->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department' => ""]);
    }
    public function department_to()
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => '']);
    }
    public function issued_staff()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['issued_staff' => '']);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
}

