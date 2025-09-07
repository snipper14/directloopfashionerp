<?php

namespace App\Medical;

use App\User;
use App\Branch\Branch;
use App\Customer\Customer;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabResult extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'branch_id',
        'user_id',
        'customer_id',
        "entry_code",
        "patient_name",
        "blood_test",
        "imaging_results",
        "other_test_results",
        "lab_tech",
        "blood_pressure",
        "heart_rate",
        "respiratory_rate",
        "temperature",
        "height_and_weight",
        "status",//['pending','completed']
    ];
    protected $appends = ['imaging_path'];
    public  function getImagingPathAttribute()
    {
     return  App::make('url')->to('/') . '/upload/' . $this->imaging_results;
        //return public_path("upload" . '\\' . $this->imaging_results);
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
