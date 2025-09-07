<?php

namespace App\Http\Controllers\QBReconciliation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\QBReconciliation\BankStatementRequest;
use App\Ledgers\GeneralLedger;
use App\QBReconciliation\QBBankingReconciliation;
use App\QBReconciliation\ReconBankStatement;
use Carbon\Carbon;

class QBBankReconciliationController extends BaseController
{
      function fetchPreviousReconsClosing()
    {
        $res =  ReconBankStatement::where(['status' => 'reconciled', 'account_id' => request('account_id')])->latest()->first();
        $opening_balance = 0;
        if ($res) {
            $opening_balance = $res->closing_balance;
        }
        $pending =  ReconBankStatement::where(['status' => 'pending', 'account_id' => request('account_id')])->latest()->first();

        $closing_balance = 0;
        if ($pending) {
            $closing_balance =    $pending->closing_balance;
        }
        return response()->json(['opening_balance' => $opening_balance, 'closing_balance' => $closing_balance, 'ref_no' => $this->refNumber()]);
    }
    function fetchPendingStatements()
    {

        $data = QBBankingReconciliation::where(['status' => 'pending', 'account_id' => request('account_id')])->latest()->get();
        return response()->json($data);
    }

    function fetchUnreconciledStatements()
    {
        $res = GeneralLedger::with(['account'])->where(['recons_status' => 'pending_reconciliation', 'account_id' => request('account_id')])->get();
        return response()->json($res);
    }
    function refNumber()
    {


        $latest = ReconBankStatement::where(['status' => 'pending', 'account_id' => request('account_id')])->latest()->first();
        if ($latest) {
            return $latest->ref;
        }
        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $ref_no = $today . '-' . '' . sprintf('%04d', $string);
        

        return  $ref_no . Parent::user_id();
    }
    function create(Request $request)
    {

        DB::transaction(function () use ($request) {
            $data = $request->selectedItems;
            QBBankingReconciliation::where(['status' => 'pending', 'account_id' => $request->account_id])->forceDelete();
            foreach ($data as  $value) {
                $res = GeneralLedger::find($value);
                QBBankingReconciliation::create([
                    'general_ledger_id' => $res->id,
                    'account_id' => $res->account_id,
                    'entry_date' => $request->entry_date,
                    'dr_amount' => $res->debit_amount,
                    'cr_amount' => $res->credit_amount,
                    'opening_balance' => $request->opening_balance,
                    'closing_balance' => $request->closing_balance,
                    'ref' => $request->ref
                ] + Parent::user_branch_id());
            }
        }, 5);
        $result = QBBankingReconciliation::where(['ref' => $request->ref, 'account_id' => $request->account_id])->get();
        return response()->json($result);
    }

    function completeRecons(Request $request)
    {


        DB::transaction(function () use ($request) {
            $data =   QBBankingReconciliation::where(['status' => 'pending', 'ref' => $request->ref, 'account_id' => $request->account_id])->get();
            foreach ($data as  $value) {

                GeneralLedger::where(['id' => $value['general_ledger_id']])->update([
                    'recons_status' => 'reconciled',
                ]);
            }

            QBBankingReconciliation::where(['status' => 'pending', 'ref' => $request->ref, 'account_id' => $request->account_id])->update(['status' => 'completed']);
            $unreconciled = GeneralLedger::with(['account'])->where(['recons_status' => 'pending_reconciliation', 'account_id' => request('account_id')])->get();
            foreach ($unreconciled as  $value) {

                QBBankingReconciliation::create(
                    [
                        'general_ledger_id' => $value['id'],
                        'account_id' => $value['account_id'],
                        'entry_date' => $value['entry_date'],
                        'dr_amount' => $value['debit_amount'],
                        'cr_amount' => $value['credit_amount'],
                        'opening_balance' => $request->opening_balance,
                        'closing_balance' => $request->closing_balance,
                        'ref' => $request->ref,
                        'status' => 'uncleared'
                    ] + Parent::user_branch_id()
                );
            }
        }, 5);
        return true;
    }

    // function fetchReconByRef()
    // {
    //     $raw_query =  QBBankingReconciliation::with(['user', 'branch', 'generalLedger', 'account'])->where('status', 'completed');
    //     $raw_query->when(request('account_id') != '', function ($query) {
    //         $query->where('account_id', request('account_id'));
    //     });
    //     $raw_query->selectRaw('*,SUM(dr_amount) AS sum_dr_amount,SUM(cr_amount) AS sum_cr_amount')->groupBy("ref")->latest();
    //     $data = $raw_query->get();
    //     if (request('page') > 0) {
    //         $data = $raw_query->paginate(30);
    //     }
    //     return response()->json($data);
    // }

    function fetchReconsDetailedReport()
    {
        $raw_query =  QBBankingReconciliation::with(['user', 'branch', 'generalLedger', 'account'])->where('status', 'completed');;

        $raw_query->where(['account_id' => request('account_id'), "ref" => request('ref')]);

        $raw_query->latest();
        $data = $raw_query->get();

        return response()->json($data);
    }
    function fetchUnclearedReconsDetailedReport()
    {
        $raw_query =  QBBankingReconciliation::with(['user', 'branch', 'generalLedger', 'account'])->where('status', 'uncleared');;

        $raw_query->where(['account_id' => request('account_id'), "ref" => request('ref')]);

        $raw_query->latest();
        $data = $raw_query->get();

        return response()->json($data);
    }
    function saveBankStatement(BankStatementRequest $request)
    {
        ReconBankStatement::create($request->validated() + Parent::user_branch_id());
        $res = ReconBankStatement::where(['account_id' => $request->account_id, 'status' => 'pending'])->latest()->get();
        return   $res;
    }
    function fetchBankStatements()
    {
        $res = ReconBankStatement::where(['account_id' => request('account_id'), 'status' => 'pending'])->latest()->get();
        return   $res;
    }
    function fetchMatchingTransaction()
    {
        $main_query = GeneralLedger::with(['account']);
        if (request('matching_records') == 'true') {
            if (request('debit_amount') > 0) {
                $main_query->whereBetween('credit_amount', [request('debit_amount') - 1, request('debit_amount') + 1]);
            }
            if (request('credit_amount') > 0) {
                $main_query->whereBetween('debit_amount', [request('credit_amount') - 1, request('credit_amount') + 1]);
            }
        }
        $res = $main_query->where(['account_id' => request('account_id'), 'recons_status' => 'pending_reconciliation'])->get();
        return $res;
    }
    function reconcileMatch(Request $request)
    {
      
        ReconBankStatement::where([
            'id' => $request->recon_bank_statement_id

        ])->update([
            'status' => 'reconciled',
            'general_ledger_id' => $request->general_ledger_id,
            'opening_balance' => $request->opening_balance,
            'closing_balance' => $request->closing_balance,

        ]);
        GeneralLedger::where('id', $request->general_ledger_id)->update(['recons_status' => 'reconciled']);
        return;
    }

    function importBankStatement(Request $request)
    {
        $data = json_decode($request['data'], true);
        $folderPath = public_path('uploads');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $file = $request->file('import_file');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();


        //  return response()->json(['message' => 'uploaded successfully'], 200);

        $valid_extension = array("csv", 'xls');

        // 2MB in Bytes
        $maxFileSize = 2097152;
        // Check file extension
        if (in_array(strtolower($extension), $valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {

                // File upload location
                $location = 'uploads';

                // Upload file
                $file->move($folderPath, $filename);

                // Import CSV to Database
                $filepath = public_path($location . "/" . $filename);

                // Reading file
                $file = fopen($filepath, "r");

                $importData_arr = array();
                $i = 0;

                while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                    $num = count($filedata);

                    // Skip first row (Remove below comment if you want to skip the first row)
                    if ($i == 0) {
                        $i++;
                        continue;
                    }
                    for ($c = 0; $c < $num; $c++) {
                        $importData_arr[$i][] = $filedata[$c];
                    }
                    $i++;
                }
                fclose($file);

                // Insert to MySQL database

                foreach ($importData_arr as $importData) {

                    $insert_array = [
                        'account_id' => $data['account_id'],
                        'transaction_date' => $importData[0],
                        'credit_amount' => $importData[2],
                        'debit_amount' => $importData[3],
                        'ref_no' => $data['ref'],
                        'details' => $importData[1],

                    ] + Parent::user_branch_id();
                    ReconBankStatement::create($insert_array);
                }
                $res = ReconBankStatement::where(['account_id' => $data['account_id'], 'status' => 'pending'])->latest()->get();

                return response()->json(["status" => "success", "message" => "successfully uploaded", 'results' => $res], 200);
            } else {
                return response()->json(["status" => "error", "message" => "File too large. File must be less than 2MB."], 200);
            }
        } else {
            return response()->json(["status" => "error", "message" => "Invalid File Extension."], 200);
        }
    }
    function deleteStatement(Request $request)
    {
        $id = $request->route('id');
        ReconBankStatement::find($id)->delete();
        return true;
    }
    function fetchReconByRef()
    {
        $raw_query = ReconBankStatement::with(['user', 'branch', 'generalLedger', 'account']);
        $raw_query->when(request('account_id') != '', function ($query) {
            $query->where('account_id', request('account_id'));
        });
        $raw_query->selectRaw('*,SUM(debit_amount) AS sum_dr_amount,SUM(credit_amount) AS sum_cr_amount')
        ->groupBy("account_id")->where(['status' => 'reconciled'])->latest();
        $data = $raw_query->get();
        if (request('page') > 0) {
            $data = $raw_query->paginate(30);
        }
        return response()->json($data);
        return $data;
    }
    function fetchByAccountID()
{
    $raw_query = ReconBankStatement::with(['user', 'branch', 'generalLedger', 'account']);

    // Filter by account_id
    $raw_query->when(request('account_id') != '', function ($query) {
        $query->where('account_id', request('account_id'));
    });

    // Filter by transaction_date (Between `from` and `to` dates)
    if (request('from') && request('to')) {
        $raw_query->whereBetween('transaction_date', [request('from'), request('to')]);
    }

    // Optional search in related data
    if (request('search')) {
        $searchTerm = request('search');
        $raw_query->where(function ($query) use ($searchTerm) {
            $query->where('status', 'reconciled')
                ->where('account_id', request('account_id'))
                ->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->where('ref_no', 'LIKE', "%$searchTerm%")
                        ->orWhereHas('user', function ($q) use ($searchTerm) {
                            $q->where('name', 'LIKE', "%$searchTerm%");
                        })
                        ->orWhereHas('branch', function ($q) use ($searchTerm) {
                            $q->where('branch', 'LIKE', "%$searchTerm%");
                        })
                        ->orWhereHas('generalLedger', function ($q) use ($searchTerm) {
                            $q->where('other_account_name', 'LIKE', "%$searchTerm%");
                        })
                        ->orWhereHas('account', function ($q) use ($searchTerm) {
                            $q->where('account', 'LIKE', "%$searchTerm%");
                        });
                });
        });
    }

    // Order by latest
    $raw_query->latest();

    // Apply pagination if requested
    if (request('page') > 0) {
        $data = $raw_query->paginate(30);
    } else {
        $data = $raw_query->get();
    }

    return response()->json($data);
}

    //
}
