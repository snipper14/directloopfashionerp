<?php

namespace App\Quote;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable=[
        'order_no',
        'stock_id',
        'department_id',
        'customer_id',
        'user_id',
        'branch_id',
        'selling_price',
        'row_total',
        'order_date',
        'qty',
       'tax_amount',
       'discount',
       'product_name','product_code',
       'status'//[pending,complete]
     ];
     protected static function boot()
     {
         parent::boot();
 
         static::addGlobalScope(new BranchScope);
     }
 
     public function stock(){
       return  $this->belongsTo(Stock::class)->withTrashed();
     }
     public function customer(){
       return  $this->belongsTo(Customer::class)->withTrashed();
     }
     public function user(){
        return $this->belongsTo(User::class)->withTrashed();
     }
     public function branch(){
      return $this->belongsTo(Branch::class)->withDefault(['branch'=>''])->withTrashed();
   }
}
