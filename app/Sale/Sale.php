<?php

namespace App\Sale;

use App\Stock\Stock;
use App\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
  use SoftDeletes;
  protected $fillable = [
    'order_no',
    'stock_id',
    'customer_id',
    'user_id',
    'branch_id',
    'order_date',
    'qty',
    'item_price',
    'row_total',
    'order_total',
    'department_id',
    'tax_amount',
    'purchase_price',
    'product_category_id',
    'discount',
    'batch_no'

  ];



  public function customer()
  {
    return $this->belongsTo(Customer::class)->withTrashed();
  }
  public function stock()
  {
    return $this->belongsTo(Stock::class)->withTrashed();
  }
}
