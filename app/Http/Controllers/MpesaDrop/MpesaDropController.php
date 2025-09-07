<?php

namespace App\Http\Controllers\MpesaDrop;

use Carbon\Carbon;
use App\Drops\Mpesadrop;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateRegisterActiveSales;

class MpesaDropController extends BaseController
{
    function create(Request $request)
    {
        $validator = Validator::validate($request->all(), [
            'mpesa_account_id' => 'required',
            'account_id' =>[
                'required',
                new ValidateRegisterActiveSales($request)
            ],
            'mpesa_amount' => 'required|numeric|gt:0',
           
        ]);
        $res = null;
        DB::transaction(function () use ($request, &$res) {
            $mpesadrawer_account =   LedgerAccount::find($request->mpesa_account_id);
            $debit_account =   LedgerAccount::find($request->account_id);
            $ref_no = $this->refNumber();
            //credit entries   
            $insert_data = array(
                'other_account_name' => $debit_account->account,
                'account_id' =>  $request->mpesa_account_id,
                'account_type' => 'Mpesa Drops',
                'entry_code' => $ref_no,
                'ref' => $ref_no,
                'details' => 'Mpesa Drops',
                'credit_amount' => $request->mpesa_amount,
                'description' => 'Mpesa Drops',
                'entry_date' => Parent::currentDate(),

            );
            GeneralLedger::create($insert_data + Parent::user_branch_id());

            //debit entries
            $insert_data = array(
                'other_account_name' => $mpesadrawer_account->account,
                'account_id' =>  $request->account_id,
                'account_type' => 'Mpesa Drops Deposit',
                'entry_code' => $ref_no,
                'ref' => $ref_no,
                'details' => 'Mpesa Drops Deposit',
                'debit_amount' => $request->mpesa_amount,
                'description' => 'Mpesa Drops Deposit',
                'entry_date' => Parent::currentDate(),

            );
            GeneralLedger::create($insert_data + Parent::user_branch_id());

            $insert_array = [
                'mpesa_account_id' => $request->mpesa_account_id,
                'account_id' => $request->account_id,
                'note' => $request->note,
                'mpesa_amount' => $request->mpesa_amount,
                'ref_no' => $ref_no,

            ];
            $res =  Mpesadrop::create($insert_array + Parent::user_branch_id());
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
        $data = Mpesadrop::fetchMpesadrops();
        return response()->json($data);
    }

    function getAccountById()
    {
        return LedgerAccount::getMpesaAccountById();
    }
}
