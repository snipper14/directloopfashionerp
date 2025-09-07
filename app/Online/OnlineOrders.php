<?php

namespace App\Online;

use App\Customer\Customer;
use App\Stock\Stock;
use App\Unit\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineOrders extends Model
{
    protected $fillable = [
        'stock_id',
        'unit_id',
        'customer_id',
        'qty',
        'row_total',
        'row_vat',
        'selling_price',
       
        'notes',
        'orderPrinted',
        'order_date',
        'order_no',
        'isConfirmed', // ['1', '0'])->default('0');
        'accompaniment_id',
        'customer_name',
        'customer_address',
        'customer_phone',
        'email',
        'delivery_time',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class)->withDefault(['customer'=>'']);
    }

    function stock(){
        return $this->belongsTo(Stock::class)->withDefault(['stock'=>'']);
    }

    function accompaniment(){
        return $this->belongsTo(Stock::class)->withDefault(['accompaniment'=>'']);
    }

    function unit(){
        return $this->belongsTo(Unit::class);
    }
}
