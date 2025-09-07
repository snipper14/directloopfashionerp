<?php

namespace App\ProductionCost;

use App\Stock\Stock;
use Illuminate\Database\Eloquent\Model;

class ProductionCost extends Model
{
    protected $fillable=[
        'stock_id',
         'user_id',
        'qty_produced',
        'total_amount',
        'details',
        'issue_no',
        'department_id'

    ];

    public function stock(){
        return $this->belongsTo(Stock::class)->withDefault(['stock'=>''])->withTrashed();
    }
    
}
