<?php

namespace App\Http\Controllers\Stock;

use App\Stock\Stock;
use App\Stock\IssueStock;
use Illuminate\Http\Request;
use App\Stock\MixedStockMovt;
use App\Stock\InitialStoreIssue;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Rules\HasStoreStockMovtUpdated;

class MixedStockMovtController extends BaseController
{
    function queryData()
    {
        $raw_query = Stock::orderBy('id', 'ASC');
        $raw_query->when(request('search') != "", function ($q) {

            $q->where("product_name", 'LIKE', '%' . request('search') . '%')

                ->orWhere("code", 'LIKE', '%' . request('search') . '%');
        });
        $raw_query->when(request('department_id') != "", function ($q) {

            $q->where("department_id", request('department_id'));
        });
        $res = $raw_query->get();
        $data = array();
        if (!$res->isEmpty()) {

            foreach ($res as  $value) {
                $stock_id = $value['id'];
                // echo $value['product_name'];

                $total_stock =  ($this->getOpeningStock($stock_id) + $this->totalGrn($stock_id));

                $data[] = [
                    'stock_id' => $stock_id,

                    'code' => $value['code'],

                    'product_name' => $value['product_name'],
                    'purchase_price' => $value['purchase_price'],
                    "opening_stock" => $this->getOpeningStock($stock_id),
                    'sum_grn_qty' => $this->totalGrn($stock_id),
                    'total_stock' => $total_stock,
                    'sum_issued_qty' =>  $this->totalIssued($stock_id),
                    'total_balance' => $this->totalBalance($stock_id),

                    'system_stock' => $this->systemStock($stock_id),
                    'physical_qty' => $this->getPhysicalStock($value),
                    'deficit' => $this->getPhysicalStock($value) - $this->systemStock($stock_id),

                ];
            }
        }
        return $data;
    }

    function fetchStockMovement()
    {
        //whereIn('id', [15, 13])
        $data = $this->queryData();
        return response()->json($data);
    }

    function getOpeningStock($stock_id)
    {
        $req_date = request('yesterday_date');

        $query = MixedStockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])->first();

        if ($query) {
            $query_op = MixedStockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])->first();
            return $query_op->physical_qty;
        } else {

            return 0;
        }
    }

    function totalGrn($stock_id)
    {
        $req_date = request('report_date');
        $query = InitialStoreIssue::where(['date_received' => $req_date, 'stock_id' => $stock_id])
            ->get()->sum('qty_received');
        if ($query) {

            return $query;
        } else {
            return 0;
        }
    }

    function totalIssued($stock_id)
    {
        $req_date = request('report_date');

        $query = IssueStock::where(['date_issued' => $req_date, 'stock_id' => $stock_id])
            ->get()->sum('qty_issued');

        if ($query) {

            return $query;
        } else {
            return 0;
        }
    }

    function totalBalance($stock_id)
    {
        return ($this->getOpeningStock($stock_id) + $this->totalGrn($stock_id)) - $this->totalIssued($stock_id);
    }
    function systemStock($stock_id)
    {
        return $this->totalBalance($stock_id);
    }

    function getPhysicalStock($value)
    {
        $req_date = request('report_date');
        $stock_id = $value['id'];

        $rs1 = MixedStockMovt::where([
            'date_recorded' => $req_date,
            'stock_id' => $stock_id,
        ])->first();
        if ($rs1) {

            $query = MixedStockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])->sum('physical_qty');

            if ($query) {
                return $query;
            } else {
                return 0;
            }
        } else {
            return $this->systemStock($stock_id);
            //return $value['store_qty'];
        }
    }

    function fetchMovtStoreStatus()
    {
        $report_date = request('report_date');
        $query = MixedStockMovt::where(['date_recorded' => $report_date])->get();
        if (!$query->isEmpty()) {
            return response()->json(['status' => false]);
        } else {
            return response()->json(['status' => true]);
        }
    }

    function updatePhysicalStock(Request $request)
    {
        $this->validate($request, ['report_date' => new HasStoreStockMovtUpdated()]);
        DB::transaction(function () use ($request) {
            $data = $request->update_data;
            foreach ($data as $value) {
                Stock::where('id', $value['stock_id'])->update(['store_qty' => $value['physical_qty']]);
                MixedStockMovt::create(
                    [
                        'stock_id' => $value['stock_id'],
                        'date_recorded' => $request->report_date,
                        'physical_qty' => $value['physical_qty'],
                        'opening_stock' => $value['opening_stock'],
                        'grn_qty' => $value['sum_grn_qty'],
                      
                        'issued_qty'=>$value['sum_issued_qty'],
                       
                       
                    ] + Parent::user_branch_id()
                );
            }
        }, 5);
        return true;
    }

    function fetchAll()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = MixedStockMovt::with(["user", "stock"])->orderBy('id', 'ASC');
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('date_recorded', [$from, $to]);
        });
        $raw_query->when(request('search') != "", function ($q) {
            $q->whereHas("stock", function ($q) {
                $q->where("product_name", 'LIKE', '%' . request('search') . '%')

                    ->orWhere("code", 'LIKE', '%' . request('search') . '%');
            });
        });
        $res = $raw_query->paginate(250);

        if (!request('page')) {
            $res = $raw_query->get();
        }
        return response()->json($res);
    }
}
