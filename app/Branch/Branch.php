<?php

namespace App\Branch;

use App\Ledgers\LedgerAccount;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
  use SoftDeletes;
  protected $fillable = [
    'branch',
    'details',
    'business_name',

    'branch_code',
    'address',
    'postal_address',
    'logo',
    'country',
    'town',
    'kra_pin',
    'phone_no',
    'email',
    'details',
    'till_no',
    'kitchen_printer',
    'bill_printer',
    'invoice_printer',
    'print_type', //['dialogue', 'server', 'offline']
    'enforce_pos_qty', //boolean

    'fisc_api',
    'cu_ip',
    'cu_port',
    'cu_password',
    'cu_serial',
    'account_id',
    'mpesa_account_id',
    'etims_apikey',
    'footer_text',
    'min_profit_margin',//default 0
    'payroll_account_id',
    'logo_height','logo_width','strip_color',
    'enforce_customer_retail',//bollean 0
    'etims_type',//['oscu', 'vscu', 'none']
    'print_copies',
    'project_level',//['production','development']
    'logo_type',//['image','name']
    "cashier_options",//['independent','consolidated']
    "selling_price_type",//,['fixed','flexible']
    'header_height',
    'loyalty_points_rate',
    'is_discount_allowed', // ['yes', 'no']
  ];
  protected $appends = ['img_url', 'img_path'];
  public  function getImgUrlAttribute()
  {
    return  App::make('url')->to('/') . '/upload/' . $this->logo;
  }
  public  function getImgPathAttribute()
  {
    return  App::make('url')->to('/') . '/upload/' . $this->logo;
      return public_path("upload" . '\\' . $this->logo);
  }
  protected $hidden = [
   // 'etims_apikey',
];

  public function account()
  {
      return $this->belongsTo(LedgerAccount::class, 'account_id','id')->withDefault(['account'=>''])->withTrashed();
  }
  
  public function mpesa_account()
  {
      return $this->belongsTo(LedgerAccount::class, 'mpesa_account_id','id')->withDefault(['account'=>''])->withTrashed();
  }
  
}
