<?php

namespace App\Http\Controllers\Expenses;

use App\Expenses\Expenses;
use App\Scopes\BranchScope;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Expenses\ExpenseType;
use App\Expenses\ExpenseLedger;
use App\Expenses\ExpensePayment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Expenses\ExpensesRequest;

class ExpensesController extends BaseController
{
    function create(ExpensesRequest $request)
    {
        DB::transaction(function () use ($request) {
            $unique_id = Parent::uniqueID();
            $this->debitCreditLedger($request, $unique_id);
            $res = Expenses::create($request->validated() + ['unpaid_balance' => $request->amount, 'unique_id' => $unique_id] + Parent::user_branch_id());
            $this->debitExpenseLedger($request, $res->id, $unique_id);
        }, 5);
    }
    function debitCreditLedger(Request $request, $unique_id)
    {
        $expense_type = ExpenseType::where(['id' => $request->expense_type_id])->first();
        $data = array(

            'supplier_id' => $request->supplier_id,
            'ref' => $request->details,
            'type' => 'Expenses',
            'entry_date' => $request->date_recorded,
            'description' => $expense_type->name,
            'credit' => $request->amount,
            'amount_due' => 0,
            'balance' => 0,
            'unique_id' => $unique_id
        );
        CreditLedger::create($data + Parent::user_branch_id());
    }
    function debitExpenseLedger(Request $request, $expense_id, $unique_id)
    {
        $expense_type = ExpenseType::where(['id' => $request->expense_type_id])->first();
        $data = array(

            'expense_id' => $expense_id,
            'ref' => $request->details,
            'type' => 'Expenses',
            'entry_date' => date('Y-m-d'),
            'description' => $expense_type->name,
            'debit' => $request->amount,
            'unique_id' => $unique_id

        );
        ExpenseLedger::create($data + Parent::user_branch_id());
    }
    public function fetch()
    {
        $from = request('from');
        $to = request('to');
        $raw_query = Expenses::when($from != '' && $to != '', function ($query) use ($to, $from) {
            $query->whereBetween('date_recorded', [$from, $to]);
        })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('expenseType', function ($subQuery) {
                            $subQuery->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('branch', function ($subQuery) {
                            $subQuery->where('branch', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('user', function ($subQuery) {
                            $subQuery->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('supplier', function ($subQuery) {
                            $subQuery->where('company_name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })
            ->with(['expenseType', 'branch', 'user', 'supplier']);
        $raw_query->leftJoin('users', 'users.id', '=', 'expenses.user_id');
        $raw_query->select('expenses.*');
        $raw_query->when(request('sort_amount'), function ($query) {
            $query->orderBy('amount', request('sort_amount'));
        })->when(request('sort_amount_paid'), function ($query) {
            $query->orderBy('amount_paid', request('sort_amount_paid'));
        })
            ->when(request('sort_user'), function ($query) {
                $query->orderBy('users.name', request('sort_user')); // ASC or DESC
            })

             ->when(request('sort_date_recorded'), function ($query) {
                $query->orderBy('date_recorded', request('sort_date_recorded')); // ASC or DESC
            })

            
            ->when(request('sort_unpaid_balance'), function ($query) {
                $query->orderBy('unpaid_balance', request('sort_unpaid_balance'));
            });

        $raw_query->leftJoin('expense_types', 'expense_types.id', '=', 'expenses.expense_type_id');

        $raw_query->when(request('sort_expense_type'), function ($query) {
            $query->orderBy('expense_types.name', request('sort_expense_type')); // ASC or DESC
        })
            ->orderBy('id', 'DESC');

        if (request('expense_type_id')) {
            $raw_query->where('expense_type_id', request('expense_type_id'));
        }

        if (!Parent::checkForPermission()) {

            $raw_query->where('user_id', Parent::user_id());
        }
        if (Parent::showBranchPermission()) {
            $raw_query->withoutGlobalScope(BranchScope::class);
        }
        $raw_query->when(request('branch_id') != '', function ($query) {
            $query->where('branch_id', request('branch_id'));
        });
        if (request('page') > 0) {
            $data = $raw_query->paginate(80);
        } else {
            $data = $raw_query->get();
        }

        return response()->json([
            'results' => $data,
            'total' => $this->expenseTotals(),
        ]);
    }

    public function expenseTotals()
    {
        $from = request('from');
        $to = request('to');
        $raw_query = Expenses::with('expenseType')
            ->when($from != '' && $to != '', function ($query) use ($to, $from) {
                $query->whereBetween('date_recorded', [$from, $to]);
            })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                        ->orWhereHas('expenseType', function ($subQuery) {
                            $subQuery->where('name', 'LIKE', '%' . request('search') . '%');
                        })
                        ->orWhereHas('branch', function ($subQuery) {
                            $subQuery->where('branch', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })
            ->selectRaw('SUM(amount) AS total_amount');

        if (request('expense_type_id')) {
            $raw_query->where('expense_type_id', request('expense_type_id'));
        }

        $data = $raw_query->first();

        return $data ? $data->total_amount : 0; // Return 0 if no results
    }
    function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            $id = $request->route('id');


            $res = Expenses::where('id', $id)
                ->whereNotNull('unique_id')
                ->first();

            if ($res) {
                CreditLedger::bulkDelete(['unique_id' => $res->unique_id]);
                ExpenseLedger::bulkDelete(['unique_id' => $res->unique_id]);
            }
            ExpensePayment::bulkDelete(['expense_id' => $id]);
            Expenses::withoutGlobalScope(BranchScope::class)->find($id)->delete();
        }, 5);
        return true;
    }
}
