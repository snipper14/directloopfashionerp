<?php

namespace App\Sale;

use App\User;
use App\Unit\Unit;
use App\Branch\Branch;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use App\Traits\AddsBranchScope;
use App\Traits\TracksDeletions;
use App\Card\CardTerminalAccount;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllSalesTotalReport extends Model
{
    use SoftDeletes, TracksDeletions, AddsBranchScope;
    protected $fillable = [
        'order_no',
        'receipt_no',
        'user_id',
        'cashier_id',
        'branch_id',
        'department_id',
        'order_date',
        'paid_date',
        'qty',
        'receipt_balance',
        'receipt_total',
        'total_vat',
        'cash_pay',
        'mpesa_pay',
        'card_pay',
        'credit_pay',
        'order_type',
        'payment_ref',
        'customer_id',
        'customer_name',
        'card_terminal_account_id',
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
        //vscu
        'signature',
        'scdcId',
        'scuReceiptDate',
        'scuReceiptNo',
        'invoiceVerificationUrl',
        'exchangeRate',
        'loyalty_points',
        'deleted_by',
        'discount_pay',
        'discount_percent',
        'external_ref'

    ];

    public function details()
    {
        return $this->hasMany(AllSaleDetailsReport::class, 'order_no', 'order_no')->withoutGlobalScope(BranchScope::class)
       ;
    }
    public function card_terminal_account()
    {
        return $this->belongsTo(CardTerminalAccount::class, 'card_terminal_account_id', 'id')->withDefault(['card_terminal_account' => ''])->withTrashed();
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed()->withDefault(["customer" => ""]);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(["user" => ""]);
    }
    public function cashier()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(["cashier" => ""]);;
    }
    public function waiter()
    {
        return $this->belongsTo(User::class)->withTrashed()->withDefault(["waiter" => ""]);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed()->withDefault(["branch" => ""]);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class)->withTrashed()->withDefault(["unit" => ""]);
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
