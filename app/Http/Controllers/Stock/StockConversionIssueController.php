<?php

namespace App\Http\Controllers\Stock;

use Carbon\Carbon;
use App\Stock\IssueStock;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Stock\IssueStockConversion;
use App\Http\Controllers\Controller;
use App\Rules\ValidateIssueStoreQty;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Stock\StockIssueConversionRequest;
use App\Stock\Stock;

class StockConversionIssueController extends BaseController
{
    public function create(StockIssueConversionRequest $request)
    {


        $this->validate($request, ['qty_remaining' => new ValidateIssueStoreQty($request->parent_stock_id, $request->department_id)]);
        DB::transaction(function () use ($request) {
            IssueStockConversion::updateOrCreate([
                'issue_no' => $request->issue_no,
                'parent_stock_id' => $request->parent_stock_id, 'stock_id' => $request->stock_id
            ], $request->validated() + Parent::user_branch_id());
            Inventory::updateOrCreate(
                [
                    'stock_id' =>  $request->stock_id,
                    'department_id' => $request->department_to_id,
                    'branch_id' => Parent::branch_id()
                ],
                ['qty' => DB::raw('qty +' . $request->qty_issued)]
            );

            Inventory::updateOrCreate(
                [
                    'stock_id' => $request->parent_stock_id,
                    'department_id' => $request->department_id,
                    'branch_id' => Parent::branch_id()
                ],
                ['qty' => DB::raw('qty -' . $request->parent_issue_qty)]
            );
        }, 5);
        return response()->json($this->fetchByIssueNo($request->issue_no));
    }
    function fetchByIssueNo($issue_no)
    {

        $rs = IssueStockConversion::with(['stock.unit', 'parent_stock'])->where(['issue_no' => $issue_no])->get();
        return $rs;
    }
    function fetchIssueNo()
    {
        $rs = IssueStockConversion::with(['stock', 'parent_stock'])->where(['status' => "progress"] + Parent::user_branch_id())->get();
        if ($rs->isEmpty()) {
            return response()->json(["issue_no" => $this->issueNumber(), "issue_data" => []]);
        } else {
            return response()->json(["issue_no" => '', "issue_data" => $rs]);
        }
        return response()->json($this->issueNumber());
    }
    function issueNumber()
    {

        $latest = IssueStockConversion::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $req_no = 'ISNC' . '-' . $today   . sprintf('%04d', $string);
        $isUnique = IssueStockConversion::where('issue_no', $req_no)->first();
        if ($isUnique) {
            $req_no =  'ISNC' . '-' . $today  . ($latest->id + 1);
        }
        return  $req_no;
    }

    function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            IssueStockConversion::where(['id' => $request->id])->delete();
            Inventory::updateOrCreate(
                [
                    'stock_id' =>  $request->stock_id,
                    'department_id' => $request->department_to_id,
                    'branch_id' => Parent::branch_id()
                ],
                ['qty' => DB::raw('qty -' . $request->qty_issued)]
            );

            Inventory::updateOrCreate(
                [
                    'stock_id' => $request->parent_stock_id,
                    'department_id' => $request->department_id,
                    'branch_id' => Parent::branch_id()
                ],
                ['qty' => DB::raw('qty +' . $request->parent_issue_qty)]
            );
        }, 5);
    }
    function completeIssue(Request $request)
    {
        DB::transaction(function () use ($request) {
        IssueStockConversion::where(['issue_no' => $request->issue_no])->update(['status' => 'complete']);
        $res = IssueStockConversion::where(['issue_no' => $request->issue_no])->get();
        foreach ($res as  $value) {
            $stock = Stock::with('unit')->where('id', $value['stock_id'])->first();
            $insert = [
                'stock_id' => $value['stock_id'],
                'issued_staff_id' => $value['issued_staff_id'],
                'unit_id' => $stock->unit_id,
                'source_department_id' => $value['department_id'],
                'department_id' => $value['department_to_id'],
                'issue_no' => $value['issue_no'],
                'qty_issued' => $value['qty_issued'],
                'final_qty' => $value['qty_issued'],
                'final_qty' => $value['qty_issued'],
                'unit' => $stock->unit->name,
                'date_issued' => $value['date_issued'],
                'mapping_value' => 1,
                'purchase_price' => $stock->purchase_price,
                'row_total' => ($stock->purchase_price * $value['qty_issued']),
                'status' => 'complete'
            ]+Parent::user_branch_id();
            IssueStock::create($insert);
        }
    },5);
        return true;
    }

    function fetch()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = IssueStockConversion::with(['stock', 'parent_stock', 'user', 'department', 'department_to', 'branch', 'issued_staff']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('date_issued', [$from, $to]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('issue_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    )
                    ->orWhereHas(
                        'issued_staff',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->where(Parent::branch_array());

        $raw_query->orderBy('id', 'DESC');
        //$raw_query->selectRaw('*,SUM(qty_issued) AS sum_qty_issued,SUM(parent_issue_qty) AS sum_parent_issue_qty');

        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(20);
        }

        return response()->json($res);
    }
}
