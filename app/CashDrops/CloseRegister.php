<?php

namespace App\CashDrops;

use App\User;
use Carbon\Carbon;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use App\CashDrops\CloseRegisterCategory;
use App\CashDrops\CloseRegisterHourlySale;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CloseRegister extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'opening_mpesa_amount',
        'opening_cash_amount',
        'cash_payments',
        'mpesa_payments',
        'card_payments',
        'loyalty_payments',
        'credit_payments',
        'return_sales',
        'cash_drops',
        'mpesa_drops',
        'cash_supplier_payments',
        'mpesa_supplier_payments',
        'cash_expenses_payments',
        'mpesa_expenses_payments',
        'cash_customer_payments',
        'mpesa_customer_payments',

      
       
        'expected_cash',
        'expected_mpesa',
        'expected_card',
        'closing_cash',
        'closing_mpesa',
        'closing_card',
        'cash_shortover',
        'mpesa_shortover',
        'card_shortover',
        'tax_collected',
        'zero_rated',
        'user_id',
        'branch_id',
        'opened_time','cashier_left_cash'
    ];
    public function getCreatedAtAttribute($value)
    { return Carbon::parse($value)->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
       // return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
    public function closeRegisterCategories()
    {
        return $this->hasMany(CloseRegisterCategory::class);
    }
    public function closeRegisterHourlySales()
    {
        return $this->hasMany(CloseRegisterHourlySale::class);
    }
    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
