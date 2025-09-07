<?php

namespace App\Medical;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Customer\Customer;
use App\Scopes\BranchScope;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientMedicalRecord extends Model
{
    use SoftDeletes;
    protected $fillable = [

        'branch_id',
        'user_id',
        'customer_id',
        'pre_existing_medical_conditions',
        'allergies',
        'past_surgeries_medical_procedures',
        'family_medical_history',
        'current_medication',
        'blood_test',
        'imaging_results',
        'other_test_results',
        'date_time_appointments',
        'visiting_health_provider',
        'purpose_of_visit',
        'treatment_plans',
        'healthcare_provider_report',
        'symptoms_complaints',
        'blood_pressure',
        'heart_rate',
        'respiratory_rate',
        'temperature',
        'height_and_weight',
        'entry_code'
    ];
    protected $appends = ['imaging_path'];
    public  function getImagingPathAttribute()
    {
        return  App::make('url')->to('/') . '/upload/' . $this->imaging_results;
        //return public_path("upload" . '\\' . $this->imaging_results);
    }


    function customer()
    {
        return $this->belongsTo(Customer::class)->withDefault(['customer' => ""])->withTrashed();
    }
    function user()
    {
        return $this->belongsTo(User::class)->withDefault(['user' => ""])->withTrashed();
    }
    function branch()
    {
        return $this->belongsTo(Branch::class)->withDefault(['branch' => ""])->withTrashed();
    }
}
