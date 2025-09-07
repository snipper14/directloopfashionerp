<?php

namespace App\Http\Controllers\BranchAccount;

use Illuminate\Http\Request;
use App\Ledgers\LedgerAccount;
use Illuminate\Validation\Rule;
use App\BranchAccount\BranchAccount;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;

class BranchAccountController extends BaseController
{
    //

    function create(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'account_id' => [
                'required',
                'integer',

                Rule::unique('branch_accounts')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->branch_id);
                }),
            ],
        ]);

        // Create the record
        $branchAccount = BranchAccount::create([
            'branch_id' => $validated['branch_id'],
            'account_id' => $validated['account_id'],
            'user_id' => Parent::user_id()
        ]);

        return response()->json(['message' => 'Branch account created', 'data' => $branchAccount]);
    }

    function fetch()
    {
        $branchId = request('branch_id');

        $res = BranchAccount::with(['account', 'branch', 'user'])
            ->where('branch_id', $branchId)
            ->get();

        return response()->json($res);
    }
    public function fetchBranchAccounts()
    {
          $accountIds = BranchAccount::where('branch_id', Parent::branch_id())
        ->pluck('account_id')
        ->filter() // removes nulls if any
        ->toArray();

    // Step 2: Build base query
    $main_query = LedgerAccount::with(['user']);

    // Step 3: Apply account filter only if branch has accounts
    if (!empty($accountIds)) {
        $main_query->whereIn('id', $accountIds);
    }

    // Step 4: Add optional search filter
    if (request('search', '') !== '') {
        $search = request('search');
        $main_query->where(function ($q) use ($search) {
            $q->where('account', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhere('account_type', 'LIKE', "%{$search}%")
                ->orWhere('category', 'LIKE', "%{$search}%");
        });
    }

    // Step 5: Order and paginate or get all
    $main_query->orderBy('id', 'DESC');

    $data = request('page') > 0
        ? $main_query->paginate(50)
        : $main_query->get();

    return response()->json($data);
    }
    function destroy($id)
    {
        BranchAccount::find($id)->delete();
        return true;
    }
}
