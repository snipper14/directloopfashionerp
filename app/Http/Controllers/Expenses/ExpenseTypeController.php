<?php

namespace App\Http\Controllers\Expenses;

use App\Expenses\ExpenseType;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseTypeController extends BaseController
{
    public function create(Request $request)
    {
        $request->validate([
            'ledger_account_id' => 'required',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_types')->whereNull('deleted_at'),
            ],
        ]);
        $existing = ExpenseType::where('name', $request->name)->first();

        if (!$existing) {
            // Get the highest account_no >= 5000
            $last = ExpenseType::where('account_no', '>=', 50000)->orderByDesc('account_no')->first();
            $newAccountNo = $last ? $last->account_no + 1 : 50000;

            ExpenseType::create([
                'name' => $request->name,
                'account_no' => $newAccountNo,
                "ledger_account_id" => $request->ledger_account_id
            ]);
        }

        $res = ExpenseType::with(["ledgerAccount"])->get();
        return response()->json($res);
    }
    public function update(Request $request)
    {
        $request->validate([
              'ledger_account_id' => 'required',
            'id' => 'required',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_types')
                    ->ignore($request->id) // Ignore current record
                    ->whereNull('deleted_at'), // Exclude soft-deleted rows
            ],
        ]);

        $expense = ExpenseType::findOrFail($request->id);

        // Update name
        $expense->name = $request->name;
        $expense->ledger_account_id = $request->ledger_account_id;

        // If account_no is not set, generate the next available one
        if (empty($expense->account_no)) {
            $last = ExpenseType::where('account_no', '>=', 50000)
                ->orderByDesc('account_no')
                ->first();

            $expense->account_no = $last ? $last->account_no + 1 : 50000;
        }

        $expense->save();

        return response()->json(['message' => 'Expense type updated successfully', 'data' => $expense]);
    }


    function delete(Request $request)
    {
        $id = $request->route('id');
        ExpenseType::where('id', $id)->delete();
        return true;
    }
    function fetch()
    {


        return response()->json(ExpenseType::with(["ledgerAccount"])->latest()->get());
    }
}
