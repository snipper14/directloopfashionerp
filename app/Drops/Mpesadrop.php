<?php

namespace App\Drops;

use App\User;
use App\Scopes\BranchScope;
use App\Ledgers\LedgerAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mpesadrop extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',

        'branch_id',
        'mpesa_account_id',
        'account_id',
        'note',
        'ref_no',
        'mpesa_amount'
    ];

    public static function fetchMpesadrops()
    {
        $searchTerm = request('search');
        $fromDate = request('from');
        $toDate = request('to');
        // Start building the query
        $query = self::query();

        // Add search condition if search term is provided
        if ($searchTerm !== '') {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('note', 'like', '%' . $searchTerm . '%')
                    ->orWhere('ref_no', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('account', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('account', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('mpesadrawer_account', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('account', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                    });
                // Add more search conditions as needed
            });
        }

        // Add date range filter if provided
        if ($fromDate && $toDate) {
            $query->whereBetween('created_at', [$fromDate, $toDate]);
        }

        // Perform the pagination
        $query->with(['account', 'mpesadrawer_account', 'user'])->latest();
        $cashdrops = $query->get();
        if (request('page') > 0) {
            $cashdrops = $query->paginate(30);
        }
        return $cashdrops;
    }
    public function account()
    {
        return $this->belongsTo(LedgerAccount::class, 'account_id', 'id')->withTrashed();
    }
    public function mpesadrawer_account()
    {
        return $this->belongsTo(LedgerAccount::class, 'mpesa_account_id', 'id')->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new BranchScope);
    }
}
