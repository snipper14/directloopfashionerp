<?php

namespace App\Stock;

use App\Branch\Branch;
use App\User;
use App\dept\Department;
use App\Scopes\BranchScope;
use App\Unit\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IssueStock extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stock_id',
        'branch_id',
        'issued_staff_id',
        'unit_id',
        'source_department_id',
        'department_id',
        'issue_no',
        'user_id',
        'qty_issued',
        'final_qty',
        'opening_stock',
        'internal_issue_no',
        'unit',
        'date_issued',
        'mapping_value',
        'purchase_price',
        'row_total',
        'status' //['complete','progress']
    ];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => '']);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed()->withDefault(['unit' => '']);
    }

    public function source_department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['source_department' => '']);
    }
    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault(['department' => '']);
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
