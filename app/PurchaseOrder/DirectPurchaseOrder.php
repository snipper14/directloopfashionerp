<?php

namespace App\PurchaseOrder;

use App\Branch\Branch;
use App\dept\Department;
use App\Stock\Stock;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectPurchaseOrder extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'approver_id',
        'branch_id',
        'department_id',

        'order_no',

        'order_deadline',
        'order_date',
        'stock_id',
        'qty',
        'qty_delivered',
        'unit_price',
        'taxes',
        'tax_rate',
        'sub_total',
        'progress', //['progress', 'completed'])->default('progress');
        'internal_confirmation', //['confirmed', 'pending'])->default('pending');

    ];
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed();
    }

    public function department()
    {
        return  $this->belongsTo(Department::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function approver()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }
}
