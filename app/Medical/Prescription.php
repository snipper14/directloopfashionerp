<?php

namespace App\Medical;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use SoftDeletes;
    protected $fillable = [
        
        'stock_id',
        'branch_id',
        'user_id',
        'customer_id',
        "product_name",
        "repeat_pattern",
        "note",
        'qty',
        "prescription_date_time",
        'patient_medical_record_id',
        'entry_code',
        "status",//['pending','completed']
    ];

   public static function fetchPrescription($entry_code=null,$customer_id=null){
        $res = self::where(['customer_id' => $customer_id, 'entry_code' => $entry_code])->get();
        return $res;  
    }
    function patient_medical_record()
    {
        return $this->belongsTo(PatientMedicalRecord::class)->withDefault(['patient_medical_record' => '']);
    }
    function stock()
    {
        return $this->belongsTo(Stock::class)->default(['stock' => ""])->withTrashed();
    }

    function customer()
    {
        return $this->belongsTo(Customer::class)->default(['customer' => ""])->withTrashed();
    }
    function user()
    {
        return $this->belongsTo(User::class)->default(['user' => ""])->withTrashed();
    }
    function branch()
    {
        return $this->belongsTo(Branch::class)->default(['branch' => ""])->withTrashed();
    }
   
}
