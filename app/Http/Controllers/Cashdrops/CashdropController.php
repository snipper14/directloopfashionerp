<?php

namespace App\Http\Controllers\Cashdrops;

use Carbon\Carbon;
use App\CashDrops\CashDrop;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateRegisterActiveSales;


class CashdropController extends BaseController
{
    //
    function create(Request $request)
    {
        $validator = Validator::validate($request->all(), [
            'cashdrawer_account_id' => 'required',
            'account_id' =>[
                'required',
                new ValidateRegisterActiveSales($request)
            ],
            'cash_amount' => 'required|numeric|gt:0',
           
        ]);
        $res = null;
        DB::transaction(function () use ($request, &$res) {
            $cashdrawer_account =   LedgerAccount::find($request->cashdrawer_account_id);
            $debit_account =   LedgerAccount::find($request->account_id);
            $ref_no = $this->refNumber();
            //credit entries   
            $insert_data = array(
                'other_account_name' => $debit_account->account,
                'account_id' =>  $request->cashdrawer_account_id,
                'account_type' => 'Cash Drops',
                'entry_code' => $ref_no,
                'ref' => $ref_no,
                'details' => 'Cash Drops',
                'credit_amount' => $request->cash_amount,
                'description' => 'Cash Drops',
                'entry_date' => Parent::currentDate(),

            );
            GeneralLedger::create($insert_data + Parent::user_branch_id());

            //debit entries
            $insert_data = array(
                'other_account_name' => $cashdrawer_account->account,
                'account_id' =>  $request->account_id,
                'account_type' => 'Cash Drops Deposit',
                'entry_code' => $ref_no,
                'ref' => $ref_no,
                'details' => 'Cash Drops Deposit',
                'debit_amount' => $request->cash_amount,
                'description' => 'Cash Drops Deposit',
                'entry_date' => Parent::currentDate(),

            );
            GeneralLedger::create($insert_data + Parent::user_branch_id());

            $insert_array = [
                'cashdrawer_account_id' => $request->cashdrawer_account_id,
                'account_id' => $request->account_id,
                'note' => $request->note,
                'cash_amount' => $request->cash_amount,
                'ref_no' => $ref_no,

            ];
            $res =  Cashdrop::create($insert_array + Parent::user_branch_id());
        }, 5);
        return response()->json($res);
    }

    function refNumber()
    {
        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $refno = $today . '-' . 'REF' . sprintf('%04d', $string);

        return  $refno;
    }

    function fetch()
    {
        $data = CashDrop::fetchCashdrops();
        return response()->json($data);
    }

    function getAccountById()
    {
        return LedgerAccount::getAccountById();
    }

    function destroy($ref_no){
        GeneralLedger::bulkDelete(['ref'=>$ref_no]);
        Cashdrop::where(['ref_no'=>$ref_no])->delete();
        return  true;
    }
}
