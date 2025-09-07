<?php

namespace App\CreditNote;

use App\User;
use App\Branch\Branch;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use App\Stock\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectCreditNote extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'credit_no',

        'notes',

        'customer_id',
        'stock_id',
        'qty',
        'row_vat',

        'price',
        'line_total',
        'credit_date',
        'status' //['complete', 'progress']
    ];
    public function stock()
    {
        return $this->belongsTo(Stock::class)->withDefault(["stock" => ""])->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(["user" => ""])->withTrashed();
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withDefault(["customer" => ""])->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
