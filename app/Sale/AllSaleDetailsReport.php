<?php

namespace App\Sale;

use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Location\Table;
use App\dept\Department;
use App\Customer\Customer;
use App\Location\Location;
use App\Scopes\BranchScope;
use App\Traits\AddsBranchScope;
use App\Traits\TracksDeletions;
use App\Category\ProductCategory;
use App\Suppliers\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllSaleDetailsReport extends Model
{
    use SoftDeletes, TracksDeletions, AddsBranchScope;
    protected $fillable = [
        'branch_id',
        'user_id',
        'stock_id',
        'product_category_id',
        'unit_id',

        'department_id',
        'qty',
        'price',
        'row_total',
        'row_vat',
        'vat_rate',
        'cost_price',
        'cost_total',
        'notes',
        'order_date',
        'order_no',
        'customer_id',
        'customer_name',
        'batch_no',
        'discount',
        'deleted_by',
        'purchase_date',
        'item_discount',
        'supplier_id'

    ];
    public static function sumQtyAndPriceByStockId($stockId, $fromDate = null, $toDate = null)
    {
        $query = self::where('stock_id', $stockId)
            ->selectRaw('SUM(qty) as sum_qty, SUM(qty * price) as sum_qty_and_price');
        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }
        $result = $query->first();

        return [
            'sum_qty' => $result->sum_qty ?? 0,
            'sum_qty_and_price' => $result->sum_qty_and_price ?? 0,
        ];
    }
    function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id')->withDefault(['supplier' => '']);
    }
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class)->withDefault(["product_category" => ""]);
    }


    function department()
    {
        return $this->belongsTo(Department::class)->withTrashed()->withDefault([
            "department" => '',

        ]);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(["stock" => ""]);
    }
    public function  unit()
    {
        return $this->belongsTo(Unit::class)->withDefault(['unit' => ''])->withTrashed();
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withDefault(['customer' => '']);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['user' => ''])->withTrashed();
    }
    public function cashier()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['cashier' => '']);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed()->withDefault(['branch' => '']);
    }
    protected static function boot()
    {
        parent::boot();

        static::bootAddsBranchScope();
        static::bootTracksDeletions();
    }
    protected static function bulkDelete(array $conditions)
    {
        $userId = auth()->id();

        // Update `deleted_by` for all matching records
        static::where($conditions)->update(['deleted_by' => $userId]);

        // Perform soft delete
        static::where($conditions)->delete();
    }
}
