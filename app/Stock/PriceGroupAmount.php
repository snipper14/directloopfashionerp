<?php

namespace App\Stock;

use App\Stock\Stock;
use App\Stock\PriceGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceGroupAmount extends Model
{
   
    protected $fillable = [
        'stock_id',
        'price_group_id',
        'selling_price'

    ];


    function stock()
    {
        return $this->belongsTo(Stock::class)->withTrashed()->withDefault(['stock' => '']);
    }
    function price_group()
    {
        return $this->belongsTo(PriceGroup::class)->withDefault(['price_group' => '']);
    }
}
