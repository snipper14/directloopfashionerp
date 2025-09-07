<?php

namespace App\Ingredient;

use App\Stock\Stock;
use Illuminate\Database\Eloquent\Model;

class ProductionCostDetails extends Model
{
    protected $fillable=[
       'stock_id',
       'user_id',
       'cost_price',
       'total_cost',
       'details',
       'issue_no',
       'department_id',
       'production_no',
       'production_status'//['progress','completed']
    ];
    public function stock(){
        return $this->belongsTo(Stock::class)->withDefault(['stock'=>''])->withTrashed();
    }
}
