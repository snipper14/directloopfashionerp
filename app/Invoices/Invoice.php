<?php

namespace App\Invoices;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Category\ProductCategory;
use App\dept\Department;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use App\Traits\TracksDeletions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes,TracksDeletions;
    protected $fillable = [
        'stock_id',
        'customer_id',
        'order_user_id',
        'user_id',
        'branch_id',
        'department_id',
        "invoice_date",
        "order_date",
        'invoice_no',
        'order_no',
        'qty',
        'purchase_price',
        'price',
        'row_total',
        'amount_paid',
        'unpaid_amount',
        'tax_amount',
        'discount',
        'product_category_id',
        'notes',
        'd_note',
        'batch_no',
        "deleted_by",'unique_id','cu_invoice_no'
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
    function department()
    {
        return $this->belongsTo(Department::class)->withDefault(['department' => ''])->withTrashed();
    }
    function product_category()
    {
        return $this->belongsTo(ProductCategory::class)->withDefault(['product_category' => ''])->withTrashed();
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withDefault(['stock' => ''])->withTrashed();
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withDefault(['customer' => ''])->withTrashed();
    }
    public function order_user()
    {
        return $this->belongsTo(User::class)->withDefault(['order_user' => ''])->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['user' => ''])->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withDefault(['branch' => ''])->withTrashed();
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
    
        // Add global scope
        static::addGlobalScope(new BranchScope);
    
        // Boot additional functionalities
        static::bootTracksDeletions();
    }
}
