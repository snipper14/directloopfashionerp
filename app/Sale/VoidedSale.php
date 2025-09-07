<?php

namespace App\Sale;

use App\User;
use App\Unit\Unit;
use App\Stock\Stock;
use App\Location\Table;
use App\Customer\Customer;
use App\Location\Location;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VoidedSale extends Model
{
   use SoftDeletes;
   protected $fillable=[
   'order_no',
    'stock_id',
    'unit_id',
    'guest_id',
    'user_id',
    'cashier_id',
    'branch_id',
    'location_id',
    'table_id',
  
    'order_date',
    'cost_price', 
    'cost_total', 
    'qty',
    'price', 
    'row_total', 
    'vat_rate', 
    'row_vat', 
    'no_guest',
    'order_type',
    'notes',
    'accompaniment_id'
   ];


   public function accompaniment(){
    return $this->belongsTo(Stock::class)->withTrashed()->withDefault(["accompaniment"=>""]);
   }
   public function table()
   {
       return $this->belongsTo(Table::class)->withTrashed()->withDefault(["table"=>""]);
   }
   public function location()
   {
       return $this->belongsTo(Location::class)->withTrashed()->withDefault(["location"=>""]);
   }
   public function stock()
   {
       return $this->belongsTo(Stock::class)->withTrashed()->withDefault(["stock"=>""]);;
   }
   public function  unit()
   {
       return $this->belongsTo(Unit::class)->withTrashed()->withDefault(["unit"=>""]);
   }
   public function customer()
   {
       return $this->belongsTo(Customer::class)->withTrashed()->withDefault(["customer"=>""]);
   }
   public function user(){
       return $this->belongsTo(User::class)->withTrashed()->withDefault(["user"=>""]);
   }
   public function cashier(){
    return $this->belongsTo(User::class)->withTrashed()->withDefault(["cashier"=>""]);
}

protected static function boot()
{
    parent::boot();

    static::addGlobalScope(new BranchScope);
}
}
