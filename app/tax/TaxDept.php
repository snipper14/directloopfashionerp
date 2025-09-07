<?php

namespace App\tax;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxDept extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'tax_rate',
        'tax_code',
        'formulae',
        'type'
     ];
}
