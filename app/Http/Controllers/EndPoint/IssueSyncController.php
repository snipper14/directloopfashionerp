<?php

namespace App\Http\Controllers\EndPoint;

use App\Scopes\BranchScope;
use Illuminate\Http\Request;
use App\Stock\InitialStoreIssue;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;

class IssueSyncController extends BaseController
{
    function fetchIssuedStock()
    {
        $res = InitialStoreIssue::withoutGlobalScope(BranchScope::class)->where(['isSync' => '0', 'status' => 'complete'])->get();
        return response()->json($res);
    }

    function updateIssuedStockLocally(Request $request)
    {

        $items = array();
        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {
            $data = $request->issue_data;
            foreach ($request->issue_data as  $value) {
                //  if ($this->issueIfExist($value['issue_no'], $value['stock_id'])) {
                InitialStoreIssue::create(
                    [
                        'issue_no' => $value['issue_no'],
                        'stock_id' => $value['stock_id'],
                        'branch_id' => $value['branch_id'],
                        'unit_id' => $value['unit_id'],
                        'user_id' => $value['user_id'],
                        'status' => $value['status'],
                        'qty_issued' => $value['qty_issued'],
                        'opening_stock' => $value['opening_stock'],
                        'qty_received' => $value['qty_received'],
                        'row_total' => $value['row_total'],
                        'unit' => $value['unit'],
                        'date_issued' => $value['date_issued'],
                        'date_received' => $value['date_received'],
                        'purchase_price' => $value['purchase_price'],
                        'isReceived' => $value['isReceived'],
                    ]
                );
            }
            //   }
        }, 5);

        return response()->json(["status" => 'success']);
    }
    function issueIfExist($issue_no, $stock_id)
    {
        $res = InitialStoreIssue::where(['issue_no' => $issue_no, 'stock_id' => $stock_id])->get();
        return $res->isEmpty() ? true : false;
    }
    function updateIssueCompletedStatus()
    {
        InitialStoreIssue::withoutGlobalScope(BranchScope::class)->where(['isSync' => '0'])->update(['isSync' => '1']);
        return response()->json(["status" => 'success']);
    }
}
