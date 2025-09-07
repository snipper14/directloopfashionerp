<?php

namespace App\SupplierInvoiceParticulars;

use App\Stock\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierInvoiceParticular extends Model
{
    use SoftDeletes;
    protected $fillable  = [
        'supplier_invoice_id',
        'invoice_no',
        'stock_id',
        'line_total', 
        'price',
        'qty'
     ];

     public function stock(){
         return $this->belongsTo(Stock::class)->withTrashed();
     }
}
