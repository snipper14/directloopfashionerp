<?php

namespace App\Http\Controllers\LedgerAccount;

use Illuminate\Http\Request;
use App\Ledgers\LedgerAccount;
use App\Ledger\LedgerSubAccount;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\LedgerAccount\LedgerAccountRequest;

class LedgerAccountController extends BaseController
{
    function create(LedgerAccountRequest $request)
    {
        $validated = $request->validated();
        LedgerAccount::create($validated + [
            'account_no' => $this->generateAccountNo($validated['ledger_sub_account_id'])
        ] + Parent::user_branch_id());
        return true;
    }
    private function generateAccountNo($ledgerSubAccountId)
    {
        // Get the parent sub account
        $parent = LedgerSubAccount::findOrFail($ledgerSubAccountId);
        $parentNo = $parent->account_no; // e.g., "1-1"

        // Find last child account with prefix like "1-1-%"
        $lastChild = LedgerAccount::where('ledger_sub_account_id', $ledgerSubAccountId)
            ->where('account_no', 'like', "{$parentNo}-%")
            ->orderByDesc(DB::raw("CAST(SUBSTRING_INDEX(account_no, '-', -1) AS UNSIGNED)"))
            ->first();

        if ($lastChild) {
            $parts = explode('-', $lastChild->account_no);
            $lastSuffix = isset($parts[2]) ? (int)$parts[2] : 0;
            $nextSuffix = $lastSuffix + 1;
        } else {
            $nextSuffix = 1;
        }

        return "{$parentNo}-{$nextSuffix}"; // e.g., "1-1-1"
    }


    public function update(LedgerAccountRequest $request)
    {
        $validated = $request->validated();

        $account = LedgerAccount::findOrFail($request->id);

        // Generate only if account_no is null or empty
        if (empty($account->account_no)) {
            $validated['account_no'] = $this->generateAccountNo($validated['ledger_sub_account_id']);
        }

        $account->update($validated);

        return response()->json(['message' => 'Account updated successfully'], 200);
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        LedgerAccount::find($id)->delete();
        return true;
    }
    public function fetch()
    {


        $main_query = LedgerAccount::with(['user', 'ledgerSubAccount'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {

                $q->where('account', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('account_type', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('category', 'LIKE', '%' . request('search') . '%')
                     ->orWhereHas('ledgerSubAccount', function ($qq)  {
                      $qq->where('name', 'LIKE', "%request('search')%");
                  })
                ;
            });
        });
        $main_query->when(request('sort_user'), function ($query) {

            $query->leftJoin('users', 'users.id', '=', 'ledger_accounts.user_id')
                ->orderBy('users.name', request('sort_user'));
        });
        $main_query->when(request('sort_main_sub_account'), function ($query) {
            $query->leftJoin('ledger_sub_accounts', 'ledger_sub_accounts.id', '=', 'ledger_accounts.ledger_sub_account_id')
                ->orderBy('ledger_sub_accounts.name', request('sort_main_sub_account'))
                ->select('ledger_accounts.*'); // ✅ critical to avoid integrity issues
        });
        $main_query->when(request('sort_category'), function ($query) {
            $query->orderBy('category', request('sort_category'));
        });
        $main_query->when(request('sort_account_name'), function ($query) {
            $query->orderBy('account', request('sort_account_name'));
        });

        $main_query->when(request('sort_account_no'), function ($query) {
            $query->orderBy('account_no', request('sort_account_no'));
        });

        $main_query->orderBy('id', 'DESC');

        if (request('page') > 0) {
            $data = $main_query->paginate(50);
        } else {
            $data =  $main_query->get();
        }
        return response()->json($data);
    }

    function fetchExpenseTypes()
    {
        $res = LedgerAccount::where('account_type', 'expenses')->orderBy('id', 'DESC')->get();

        return response()->json($res);
    }
}
