<?php

namespace App\Ledgers;

use App\Accounts\ChartAccount;
use App\User;
use App\Branch\Branch;
use App\Ledger\LedgerSubAccount;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerAccount extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'branch_id',
        'user_id',
        'account_no',
        'ledger_sub_account_id',
        "account",
        "description",
        'account_type',
        'category', //['equity','liability','asset',income,expense]
    ];
    //    { label: "Current Asset", value: "Current Asset" },
    //    { label: "Other Current Asset", value: "Other Current Asset" },
    //    { label: "Inventory-Asset", value: "Inventory" },
    //    { label: "Fixed Asset", value: "Fixed Asset" },
    //    { label: "Current Liability", value: "Current Liability" },
    //    { label: "Long Term Liability", value: "Long Term Liability" },
    //    { label: "Equity", value: "Equity" },
    //    { label: "Input VAT-Asset ( Purchases)", value: "input VAT" },
    //    { label: "Output VAT-Liability ( Sales)", value: "output VAT" },
    //    {
    //        label: "Late Delivery Charges",
    //        value: "Late Delivery Charges",
    //    },
    //    { label: "Withholding Tax-WHT", value: "WHT" },
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope(new BranchScope);
    // }
protected $appends = ['original_account','account_no_formatted'];
   public function getOriginalAccountAttribute()
{
    return $this->attributes['account'] ?? null;
}
    public function getAccountAttribute($value)
    {
        return $this->account_no ? " {$this->formatAccountNo($this->account_no)} . {$value} " : $value;
    }
    function formatAccountNo($value){
     $parts = explode('-', $value);

    // If first part is only 1 digit, append '00'
    if (isset($parts[0]) && strlen($parts[0]) === 1) {
        $parts[0] .= '00';
    }

    // Join back into final account format
    $formatted_account_no = implode('-', $parts);
   return $formatted_account_no;
}
 public function getAccountNoFormattedAttribute(): ?string
    {
        $raw = $this->attributes['account_no'] ?? null;
        return $this->formatAccountNoToHundreds($raw);
    }

    protected function formatAccountNoToHundreds(?string $value): ?string
    {
        if (!$value) return null;

        // Split & trim each segment
        $parts = array_map('trim', explode('-', $value));
        if (!isset($parts[0]) || $parts[0] === '') return $value;

        // Keep only digits from first part, multiply by 100
        $firstDigits = preg_replace('/\D+/', '', $parts[0]);
        if ($firstDigits === '') return $value;

        $parts[0] = (string) ((int) $firstDigits * 100);

        return implode('-', $parts);
    }
    function ledgerSubAccount()
    {
        return $this->belongsTo(LedgerSubAccount::class, 'ledger_sub_account_id', 'id')->withDefault(['ledger_sub_account' => '']);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public  static function getAccountById()
    {
        $res1 = self::where('id', request('cashdrawer_account_id'))->first();
        $res2 = self::where('id', request('account_id'))->first();
        $data = ['cashdrawer_account' => $res1->account, 'deposit_account' => $res2->account];
        return $data;
    }
    public  static function getMpesaAccountById()
    {
        $res1 = self::where('id', request('mpesa_account_id'))->first();
        $res2 = self::where('id', request('account_id'))->first();
        $data = ['mpesa_account' => $res1->account, 'deposit_account' => $res2->account];
        return $data;
    }
}
