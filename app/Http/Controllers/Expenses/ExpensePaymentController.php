<?php

namespace App\Http\Controllers\Expenses;

use App\Expenses\Expenses;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Expenses\ExpenseType;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use App\Expenses\ExpenseLedger;
use App\Expenses\ExpensePayment;
use App\Transaction\Transaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateRegisterActiveSales;
use App\Http\Requests\Expense\ExpensesPaymentRequest;

class ExpensePaymentController extends BaseController
{
    public function create(ExpensesPaymentRequest $request)
    {
     Validator::validate($request->all(), [
          
            'account_id' =>[
                'required',
                new ValidateRegisterActiveSales($request)
            ],
          
           
        ]);
        DB::transaction(function () use ($request) {

            ExpensePayment::create($request->validated() + Parent::user_branch_id());
            $this->deductCreditLedger($request);
             $this->creditExpenseLedger($request);
           if($request->pay_method!='Cash'){
            $this->debitGeneralLedger($request);
           }
             

            Expenses::where(['id' => $request->expense_id])->update(
                ['amount_paid' => DB::raw('amount_paid +' .  $request->amount_paid)] +
                    ['unpaid_balance' => DB::raw('unpaid_balance -' .  $request->amount_paid)]
            );
        }, 5);
        return true;
    }
    function creditExpenseLedger(Request $request)
    {

        $account = LedgerAccount::where(['id' => $request->account_id])->first()->account;
        $data = array(

            'expense_id' => $request->expense_id,
            'ref' => $request->ref_no,
            'type' => 'Payment',
            'entry_date' => $request->date_paid,
            'description' => $account,
            'credit' => $request->amount_paid,

        );
        ExpenseLedger::create($data + Parent::user_branch_id());
    }
    function debitGeneralLedger(Request $request)
    {
        $cust = Supplier::where('id', $request->supplier_id)->first();

        $date = $request->date_paid;
        $insert_data = array(
            'other_account_name' => $cust->company_name,
            'account_id' => $request->account_id,
            'account_type' => 'supplier',
            'entry_code' => $request->ref_no,
            'ref' => $request->ref_no,
            'details' => "Expenses Payment",
            'credit_amount' => $request->amount_paid,
            'description' => $request->description,
            'entry_date' => $date,

        );
        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    public function deductCreditLedger(Request $request)
    {
        $res =  CreditLedger::where(['supplier_id' => $request->supplier_id])->orderBy('created_at', 'desc')->first();
        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $credit_amount =  $request->amount_paid;
       
        $data = array(

            'supplier_id' => $request->supplier_id,
            'ref' => $request->ref_no,
            'type' => 'Expense Payment',
            'entry_date' => $request->date_paid,
            'description' => 'Expense Payments',
            'debit' => $credit_amount,
            'amount_due' => 0,
            'balance' => 0
        );
        CreditLedger::create($data + Parent::user_branch_id());

      
    }

    function getAmountOwed(Request $request)
    {
        $id = $request->expense_id;
        $res = Expenses::where('id', $id)->first();

        $pending_amount = 0;
        if ($res) {
            $pending_amount = $res->unpaid_balance;
        }
        return $pending_amount;
    }
    function reportQuery()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = ExpensePayment::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_details', 'LIKE', '%' . request('search') . '%')


                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('account', function ($query) {
                        $query->where('account', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with([
        'supplier',
        'user',
        'account',
        'branch',
        'expense' => function ($query) {
            $query->withoutGlobalScope(BranchScope::class)->with(['expenseType']);
        }
    ])
        //->with(['supplier', 'user', 'account', 'expense.expenseType'])


            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('date_paid', [$from, $to]);
                }
            )
            
            ->orderBy('id', 'ASC');
              if (!Parent::checkForPermission()) {
            
                $main_query->where('user_id', Parent::user_id());
           
        }
        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }
         $main_query->when(request('branch_id') != '', function ($query) {
            $query->where('branch_id', request('branch_id'));
        });
        return $main_query;
    }
    function fetchPaymentReport()
    {



        $main_query = $this->reportQuery();
        $data = $main_query->get();
        if (request('page') > 0) {
            $data = $main_query->paginate(20);
        }

        return response()->json($data);
    }
    function fetchPaymentTotal()
    {



        $main_query = $this->reportQuery();
        $main_query->selectRaw('SUM(amount_paid) AS total_amount_paid');
        $data = $main_query->first();

        return response()->json($data);
    }

    function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            $id = $request->route('id');
            $res = ExpensePayment::where('id', $id)->first();


            Expenses::where(['id' => $res->expense_id])->update(
                ['amount_paid' => DB::raw('amount_paid -' .  $res->amount_paid)] +
                    ['unpaid_balance' => DB::raw('unpaid_balance +' .  $res->amount_paid)]
            );


            ExpensePayment::where('id', $id)->delete();
        }, 5);
        return true;
    }
}
