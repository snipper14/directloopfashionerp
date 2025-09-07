<?php

namespace App\CreditNote;

use App\User;
use App\Branch\Branch;
use App\Invoices\Invoice;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
  use SoftDeletes;
  protected $fillable=[
    'credit_no',
    'invoice_no',
    'invoice_id',
    'department_id',
    'qty',
    'price',
    'credit_date',
    'customer_id',
    'line_total',
    'row_vat',
    'user_id',
    'branch_id',
    'reduce_stock',//boolean
    'notes'
   ];
   public function invoice(){
       return $this->belongsTo(Invoice::class)->withDefault(['invoice'=>''])->withTrashed();
   }
   public function user(){
       return $this->belongsTo(User::class)->withDefault(["user"=>""])->withTrashed();
   }

   public function branch(){
    return $this->belongsTo(Branch::class);
}
 protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
