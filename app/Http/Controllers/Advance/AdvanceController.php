<?php

namespace App\Http\Controllers\Advance;

use Carbon\Carbon;
use App\Branch\Branch;
use App\Advance\Advance;
use App\Employee\Employee;
use App\Expenses\Expenses;
use Illuminate\Http\Request;
use App\Expenses\ExpenseType;
use App\Ledgers\GeneralLedger;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Advance\AdvanceRequest;
use App\Ledgers\LedgerAccount;

class AdvanceController extends BaseController
{
    public function create(AdvanceRequest $request)
    {
        DB::transaction(function () use ($request) {
            $amount = $request->amount;
            $advance =  Advance::create($request->validated() + Parent::user_branch_id());

           
            $employee = Employee::find($request->employee_id);
          
                $insert_data = array(
                    'other_account_name' => $employee->first_name . ' ' . $employee->last_name,
                    'account_id' => $request->payroll_account_id,
                    'account_type' => 'SALARY ADVANCE',
                    'entry_code' =>  "ADVANCE_ID_" . $advance->id,
                    'ref' => $request->ref_no,
                    'details' => "Advance" . $request->payment_month,
                    'credit_amount' => $amount,
                    'description' => 'SALARY ADVANCE',
                    'entry_date' => Parent::currentDate(),

                );


                GeneralLedger::create($insert_data + Parent::user_branch_id());
                $expenseType = ExpenseType::firstOrCreate([
                    'name' => "SALARY ADVANCE"
                ], []);
                Expenses::create([
                    'expense_type_id' => $expenseType->id,
                    'amount' => $amount,
                    'date_recorded' => Parent::currentDate(),
                    'details' => "Salary Advance" . $request->payment_month,
                    'ref_no' => "ADVANCE_ID_" . $advance->id,
                    'unpaid_balance' => 0,
                    'amount_paid' => $amount
                ] + Parent::user_branch_id());
           
        }, 5);
        return true;
    }

    public function update(AdvanceRequest $request)
    {
        Advance::where('id', $request->id)->update($request->validated());
        return true;
    }

    public function fetch()
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $raw_query = Advance::with('payroll_account')->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('payment_month', [$from, $to]);
        })->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('amount', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_month', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('employee', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('payroll_account', function ($query) {
                        $query->where('account', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['employee',])->orderBy('id', 'DESC');
        $data = $raw_query->get();
        if (request('page') > 0) {
            $data = $raw_query->paginate(20);
        }
        return response()->json(['results' => $data]);
    }



    public function destroy(Request $request)
    {
        $id = $request->route('id');
        $id = Advance::find($id);
        $id->delete();
        return response()->json(['results' => 'deleted']);
    }
    public function fetchTotals()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $unpaid_query = Advance::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('payment_month', [$from, $to]);
        });
        $res =  $unpaid_query->where('isDeducted', 0)->selectRaw("SUM(amount) AS total_unpaid")->first();

        $paid_query = Advance::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('payment_month', [$from, $to]);
        });
        $paid_res =  $paid_query->where('isDeducted', 1)->selectRaw("SUM(amount) AS total_paid")->first();
        return response()->json(["total_unpaid" => $res->total_unpaid, "total_paid" => $paid_res->total_paid]);
    }
}
