<?php

namespace App\Inventory;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\dept\Department;
use App\Scopes\BranchScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulkStockTake extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'stock_id',
        'department_id',
        'user_id',
        'branch_id',
        'stocktake_date',
        'current_qty',
        'counted_qty',
        'purchase_price',
        'selling_price',
        "stocktake_code"
    ];

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
        return $this->belongsTo(Stock::class)->withDefault(['stock' => ''])->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withDefault(['branch' => ''])->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['user' => ''])->withTrashed();
    }

    public static function filterAndGroupByDate($startDate = null, $endDate = null, $perPage = null)
    {

        $query = self::with(['user', 'department'])->selectRaw('*,SUM(current_qty) as total_current_qty, 
        SUM(counted_qty) as total_counted_qty,
        SUM(purchase_price*current_qty) AS sum_variation_current_purchase_price,
        SUM(purchase_price*counted_qty) AS sum_variation_counted_purchase_price,
        SUM(selling_price*counted_qty) AS sum_variation_counted_selling_price,
        SUM(selling_price*current_qty) AS sum_variation_current_selling_price')
            ->groupBy('stocktake_code')->orderBy('id', 'DESC');

        if ($startDate && $endDate) {
            $query->whereBetween('stocktake_date', [$startDate, $endDate]);
        }

        if ($perPage) {
            return  $query->paginate(30);
        }


        return $query->get();
    }


    public static  function fetchDetails()
    {


        $main_query = self::with([

            'user',
            'department',
            'branch',
            'stock',
            'stock.product_category',
            'stock.unit',
            'stock.tax_dept'
        ])
            ->when(request('search', '') != '', function ($query) {
                $query->whereHas('stock', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('unit', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('product_category', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                })
                    ->orWhereHas('department', function ($query) {
                        $query->where('department', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });


        if (request('variation_type') == 'positive_variance') {
            $main_query->whereRaw('(counted_qty-current_qty  ) > 0');
        } else if (request('variation_type') == 'negative_variance') {

            $main_query->whereRaw('(counted_qty-current_qty  ) < 0');
        } else if (request('variation_type') == 'zero_variance') {

            $main_query->whereRaw('(counted_qty-current_qty) = 0');
        } else if (request('stock_id')) {

            $main_query->where("stock_id", request("stock_id"));
        }

        $main_query->where('stocktake_code', request('stocktake_code'));


        $main_query->orderBy('id', 'DESC');
        $data = $main_query->get();
        //  print_r(DB::getQueryLog());
        if (request('page') > 0) {

            $data = $main_query->paginate(50);
        }
        return $data;
    }
}
