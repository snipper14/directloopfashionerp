<?php

namespace App\DirectRetailCredit;

use App\User;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Customer\Customer;
use App\Traits\AddsBranchScope;
use App\Traits\TracksDeletions;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DirectRetailsCredit extends Model
{
    use SoftDeletes, TracksDeletions, AddsBranchScope;
    protected  $fillable = [
        'branch_id',
        'user_id',
        'credit_date',
        'selling_price',
        'qty',
        'row_total',
        'row_vat',
        "image",
        'deleted_by',
        'status', //['progress','completed']
        'credit_no',
        'ref_no',
        'stock_id',
        'details',
        'customer_id',
      'entry_type',//['original','replacement'])->default('original');
        'receipt_no',
        'unique_code'

    ];
    protected $appends = ['img_url'];
    public  function getImgUrlAttribute()
    {
        return  App::make('url')->to('/') . '/uploads/' . $this->image;
    }
    function customer(){
        return $this->belongsTo(Customer::class)->withDefault(['customer' => ''])->withTrashed();
    }
    function stock(){
        return $this->belongsTo(Stock::class)->withDefault(['stock' => ''])->withTrashed();
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class)->withDefault(['branch' => ''])->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['user' => ""]);
    }
    protected static function bulkDelete(array $conditions)
    {
        $userId = auth()->id();

        // Update `deleted_by` for all matching records
        static::where($conditions)->update(['deleted_by' => $userId]);

        // Perform soft delete
        static::where($conditions)->delete();
    }
    protected static function boot()
    {
        parent::boot();

        static::bootAddsBranchScope();
        static::bootTracksDeletions();
        static::creating(function ($model) {
            $model->validateCreditNo();
        });

        static::updating(function ($model) {
            $model->validateCreditNo();
        });
        

    }
    public function validateCreditNo()
    {
        if ($this->status === 'progress') {
            $existingRecord = DirectRetailsCredit::where('customer_id', $this->customer_id)
                ->where('status', 'progress')
              // Exclude the current record during update
                ->first();

            if ($existingRecord && $existingRecord->credit_no !== $this->credit_no) {
                throw ValidationException::withMessages([
                    'credit_no' => 'The credit_no must be the same for the same customer when status is progress.'
                ]);
            }
        }
    }
}
