<?php

namespace App\Http\Controllers\Ledgers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Ledger\LedgerSubAccount;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;

class MainLedgerAccountController extends BaseController
{
    function create(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ledger_sub_accounts') // replace with your actual table if different
                    ->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string|max:255',
            'account_type' => 'required|string|max:50',
            'category' => 'required',

        ]);
        $accountNo = $this->generateAccountNumber($data['category']);
        LedgerSubAccount::create($data + ['account_no' => $accountNo] + Parent::user_branch_id());
        return response()->json(['message' => 'Account created successfully'], 201);
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ledger_sub_accounts') // 
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string|max:255',
            'account_type' => 'required|string|max:50',
            'category' => 'required|string',
        ]);

        $account = LedgerSubAccount::findOrFail($id);
        $account->update($data);

        return response()->json(['message' => 'Account updated successfully'], 201);
    }
    private function generateAccountNumber($category)
{
    $baseNumbers = [
        'asset' => 1,
        'liability' => 2,
        'equity' => 3,
        'income' => 4,
        'expense' => 5,
        'accrued_expenses' => 6,
        'other_expenses' => 7,
    ];

    $base = $baseNumbers[$category] ?? 0;

    if ($base === 0) {
        throw new \Exception("Invalid category: $category");
    }

    // Look for the last sub-account under this category
    $lastAccount = LedgerSubAccount::where('category', $category)
        ->where('account_no', 'like', "$base-%")
        ->orderByDesc(DB::raw("CAST(SUBSTRING_INDEX(account_no, '-', -1) AS UNSIGNED)"))
        ->first();

    if ($lastAccount) {
        $parts = explode('-', $lastAccount->account_no);
        $suffix = isset($parts[1]) ? ((int) $parts[1]) + 1 : 1;
    } else {
        $suffix = 1;
    }

    return "{$base}-{$suffix}";
}


    // Fetch all main ledger accounts
    public function fetch()
    {
        $main_query = LedgerSubAccount::with(['user'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('account_type', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('category', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC');
        if (request('page') > 0) {
            $data = $main_query->paginate(50);
        } else {
            $data =  $main_query->get();
        }
        return response()->json($data);
    }

    public function destroy($id)
    {

        $account = LedgerSubAccount::find($id);
        $account->delete();

        return response()->json(['message' => 'Account deleted successfully.'], 200);
    }
}
