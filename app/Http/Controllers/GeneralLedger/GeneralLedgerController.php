<?php

namespace App\Http\Controllers\GeneralLedger;

use Carbon\Carbon;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Database\Console\Migrations\BaseCommand;
use App\Http\Requests\GeneralLedger\GeneralLedgerRequest;

class GeneralLedgerController extends BaseController
{
    function create(GeneralLedgerRequest $request)
    {
        DB::transaction(function () use ($request) {

            if ($request->credit_account_type == 'account' && $request->debit_account_type == 'account') {
                GeneralLedger::create($request->validated() +
                    [
                        'account_id' => $request->debit_account_id,
                        'debit_amount' => $request->amount,
                        'other_account_name' => $request->credit_account_name,
                        'account_type' => 'account'
                    ]
                    + Parent::user_branch_id());

                GeneralLedger::create($request->validated() +
                    [
                        'account_id' => $request->credit_account_id,
                        'credit_amount' => $request->amount,
                        'other_account_name' => $request->debit_account_name,
                        'account_type' => 'account'
                    ]
                    + Parent::user_branch_id());
            }
            if ($request->credit_account_type == 'supplier') {
                GeneralLedger::create($request->validated() +
                    [
                        'account_id' => $request->debit_account_id,
                        'debit_amount' => $request->amount,
                        'other_account_name' => $request->credit_account_name,
                        'account_type' => $request->credit_account_type
                    ]
                    + Parent::user_branch_id());
                $data = array(

                    'supplier_id' => $request->credit_account_id,
                    'ref' => $request->entry_code,
                    'type' => 'GL',
                    'entry_date' => $request->entry_date,
                    'description' => $request->debit_account_name.' / '.$request->details,
                    'credit' => $request->amount,
                    'amount_due' => 0,
                    'balance' => 0
                );
                  CreditLedger::create($data + Parent::user_branch_id());
            }
            if ($request->debit_account_type == 'supplier') {
                GeneralLedger::create($request->validated() +
                    [
                        'account_id' => $request->credit_account_id,
                        'other_account_name' => $request->debit_account_name,
                        'account_type' => $request->debit_account_type,
                        'credit_amount' => $request->amount
                    ] + Parent::user_branch_id());

                $data = array(

                    'supplier_id' => $request->debit_account_id,
                    'ref' => $request->entry_code,
                    'type' => 'GL',
                    'entry_date' => $request->entry_date,
                    'description' => $request->credit_account_name.' / '.$request->details,
                    'debit' => $request->amount,
                    'amount_due' => 0,
                    'balance' => 0
                );
                  CreditLedger::create($data + Parent::user_branch_id());
            }
            if ($request->credit_account_type == 'customer') {
                GeneralLedger::create($request->validated() +
                    [
                        'account_id' => $request->debit_account_id,
                        'debit_amount' => $request->amount,
                        'other_account_name' => $request->credit_account_name,
                        'account_type' => $request->credit_account_type,
                    ] + Parent::user_branch_id());

                $insert_data = array(

                    'customer_id' => $request->credit_account_id,
                    'ref' => $request->entry_code,
                    'type' => 'GL',
                    'entry_date' => $request->entry_date,
                    'description' => $request->debit_account_name.' / '.$request->details,
                    'credit' => $request->amount,
                    'amount_due' => 0,
                    'balance' => 0
                );
                 CustomerLedger::create($insert_data + Parent::user_branch_id());
            }
            if ($request->debit_account_type == 'customer') {
                GeneralLedger::create($request->validated() +
                    [
                        'account_id' => $request->credit_account_id,
                        'other_account_name' => $request->debit_account_name,
                        'account_type' => $request->debit_account_type,
                        'credit_amount' => $request->amount
                    ] + Parent::user_branch_id());

                $data = array(

                    'customer_id' => $request->debit_account_id,
                    'ref' => $request->entry_code,
                    'type' => 'GL',
                    'entry_date' => $request->entry_date,
                    'description' => $request->credit_account_name.' / '.$request->details,
                    'debit' => $request->amount,
                    'amount_due' => 0,
                    'balance' => 0
                );
                 CustomerLedger::create($data + Parent::user_branch_id());
            }
        }, 5);
        return $this->queryPendingLedgers($request);
    }
    public function queryPendingLedgers(Request $request)
    {
        $res = GeneralLedger::with(['account'])->where('entry_code', $request->entry_code)->get();
        return  $res;
    }
    function fetchPending()
    {
        $res = GeneralLedger::where('entry_code', request('entry_code'))->get();
        return response()->json($res);
    }
    public function fetchEntryNo()
    {
        return  response()->json($this->entryCode());
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        GeneralLedger::find($id)->delete();
        return true;
    }

    public function fetchLedgerGrouped()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = GeneralLedger::with('account')->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('entry_code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('details', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('ref', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('other_account_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('account', function ($query) {
                        $query->where('account', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )
            ->when(
                request('account_id') != '',
                function ($query) use ($from, $to) {

                    $query->where('account_id', request('account_id'));
                }
            )


            ->when(
                request('credit_account_type') != '',
                function ($query) use ($from, $to) {

                    $query->where('credit_account_type', request('credit_account_type'));
                }
            )
            ->with(["user"])->where("status", "completed");
           $raw_query ->when(request('sort_account'), function ($query) {
                $query->orderBy('account.account', request('sort_account'));
            });
         $raw_query->orderBy('id', 'DESC');
      
        if (request('page') > 0) {
            $data =  $raw_query->paginate(15);
        }else{
              $data =  $raw_query->get();
        }
        return response()->json($data);
    }
    public function fetchLedgerTotal()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = GeneralLedger::with('account')->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('entry_code', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('account', function ($query) {
                        $query->where('account', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )
            ->when(
                request('account_id') != '',
                function ($query) use ($from, $to) {

                    $query->where('account_id', request('account_id'));
                }
            )


            ->when(
                request('credit_account_type') != '',
                function ($query) use ($from, $to) {

                    $query->where('credit_account_type', request('credit_account_type'));
                }
            )
            ->with(["user"])->where("status", "completed");
        $raw_query->selectRaw(' SUM(credit_amount) as sum_credit_amount,SUM(debit_amount) as sum_debit_amount'); //->groupBy("entry_code")

        $data =  $raw_query->first();

        return response()->json($data);
    }
    function entryCode()
    {
        $latest = GeneralLedger::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $entryNo = $today . '-' . 'GL' . sprintf('%04d', $string);
        $isUnique = GeneralLedger::where('entry_code', $entryNo)->first();
        if ($isUnique) {
            $entryNo = $today . '-' . 'GL' . ($latest->id + 1);
        }
        return  $entryNo;
    }

    function completeLedger(Request $request)
    {
        GeneralLedger::where(['entry_code' => $request->entry_code])->update(['status' => 'completed']);
        return true;
    }

    function fetchDetailsEntries()
    {
        $res = GeneralLedger::where("entry_code", request('entry_code'))->get();
        return  response()->json($res);
    }
    function generateLedgerCode(){
        $gl=   GeneralLedger::latest()->first();
        $code=1;
        if($gl){
           $code=($gl->id)+1;
        }
   
   
        $random_id = rand(1000, 10000);
   
        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $rand = $today . '-' . sprintf('%04d', $string);
        return $rand.$code;
       }
    function saveBalances(Request $request)
    {
        $code=$this->generateLedgerCode();
        $this->validate($request, [

            'desc' => 'required',
            'debit_amount' => 'numeric|required',
            'credit_amount' => 'numeric|required',
            'account_id' => "required",
            'entry_date'=>'required'
        ]);
        GeneralLedger::create([
            'other_account_name' => $request->desc,
            'account_id' => $request->account_id,
            'entry_code' => 'Balances',
            'account_type' => $request->account,
            'debit_amount'=>$request->debit_amount,
            'credit_amount'=>$request->credit_amount,
            'details'=>$request->desc,
            'ref'=>'B',
            'entry_date'=>$request->entry_date
            

        ]+parent::user_branch_id());
        // $ledger_acc = LedgerAccount::firstOrCreate(
        //     ['account' => ' Balances Equity'],
        //     [
        //         'account_type' => 'Equity',
        //         'category' => 'equity'
        //     ]+Parent::user_branch_id()
        // );
        
           
            // GeneralLedger::create([
            //     'other_account_name' => $request->account,
            //     'account_id' => $ledger_acc->id,
            //     'entry_code' => $code,
            //     'account_type' => $request->account,
            //     'debit_amount'=>$request->credit_amount,
            //     'credit_amount'=>$request->debit_amount,
            //     'details'=>$request->desc,
            //     'ref'=>'D_B',
            //     'entry_date'=>$request->entry_date
                
    
            // ]+parent::user_branch_id());
        return true;
    }

    function fetchVatReport(Request $request){
    $from = $request->input('from');
    $to = $request->input('to');
    $branchId = $request->input('branch_id');

    $query = GeneralLedger::select(
        'ledger_accounts.account_type',
        DB::raw('ABS(SUM(COALESCE(general_ledgers.debit_amount, 0) - COALESCE(general_ledgers.credit_amount, 0))) as net_vat')
    )
    ->join('ledger_accounts', 'ledger_accounts.id', '=', 'general_ledgers.account_id')
    ->whereIn('ledger_accounts.account_type', ['input VAT', 'output VAT']);

    // Apply date filter
    if ($from && $to) {
        $query->whereBetween('general_ledgers.entry_date', [$from, $to]);
    }

    // Apply branch filter
    if ($branchId) {
        $query->where('general_ledgers.branch_id', $branchId);
    }

    $vatSummary = $query
        ->groupBy('ledger_accounts.account_type')
        ->get()
        ->pluck('net_vat', 'account_type');

    return response()->json([
        "vat_input" => abs($vatSummary['input VAT'] ?? 0),
        "vat_output" => abs($vatSummary['output VAT'] ?? 0)
    ]);
}
}
