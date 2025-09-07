<?php

namespace App\PreOrder;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Customer\Customer;
use App\Traits\AddsBranchScope;
use App\Traits\TracksDeletions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Preorder extends Model
{
    use SoftDeletes, TracksDeletions, AddsBranchScope;
    protected $fillable = [
        'stock_id',
        'qty',
        'purchase_price',
        'selling_price',
        'row_total',
        'tax_rate',
        'row_vat',

        'branch_id',
        'user_id',
        'customer_id',
        "order_date",
        'order_status', //['progress','completed','ready','delivered']
        'order_no',
        'deleted_by',
        'customer_name',
        'customer_phone',
        'unique_code'
    ];
    function customer()
    {
        return $this->belongsTo(Customer::class)->withDefault(['stock' => '']);
    }
    function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => '']);
    }
    function branch()
    {
        return $this->belongsTo(Branch::class)->withDefault(['branch' => '']);
    }

    function user()
    {
        return $this->belongsTo(User::class)->withDefault(['user' => '']);
    }
    protected static function bulkDelete(array $conditions)
    {
        $userId = auth()->id();

        // Update `deleted_by` for all matching records
        static::where($conditions)->update(['deleted_by' => $userId]);

        // Perform soft delete
        static::where($conditions)->delete();
    }
    protected static function boot()
    {
        parent::boot();

        static::bootAddsBranchScope();
        static::bootTracksDeletions();
    }
}
