<?php

namespace App\POS;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Sale\AllSaleDetailsReport;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class POSCredit extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'stock_id',
        'all_sale_details_report_id',
        'receipt_qty',
        'qty',
        'selling_price',
        'row_total',
        'row_vat',
        'credit_sp',
        'credit_vat',
        'credit_amount',
        'credit_date',
        'receipt_no',
        'credit_no',
        'tax_rate',
        'cu_invoice_no',
        'deduct_stock',
        'status', //'completed','pending'


        //etims
        'etims_timestamp',
                'etims_date',
                'etims_time',
                'trader_invoice_number',
                'digitax_id',
                'serial_number',
                'receipt_number',
                'sale_detail_url',
                'etims_customer_name',
                'etims_customer_pin',
                'taxable_amount_a',
                'taxable_amount_b',
                'taxable_amount_c',
                'taxable_amount_d',
                'taxable_amount_e',
                'tax_rate_a',
                'tax_rate_b',
                'tax_rate_c',
                'tax_rate_d',
                'tax_rate_e',
                'tax_amount_a',
                'tax_amount_b',
                'tax_amount_c',
                'tax_amount_d',
                'tax_amount_e',
    ];
    public function all_sale_details_report()
    {
        return $this->belongsTo(AllSaleDetailsReport::class)
            ->withDefault(['all_sale_details_report' => '']);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed()->withDefault(['branch' => '']);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => ""]);
    }


    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(['user' => ""]);
    }


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
