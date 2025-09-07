<?php

namespace App\Http\Controllers\Stock;

use Carbon\Carbon;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Stock\StockMovt;
use Illuminate\Http\Request;
use App\Stock\StockMovtStatus;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Symfony\Component\VarDumper\Cloner\Data;

class StockMovementMaintStoreController extends BaseController
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
                    'id' => $this->stockMovtID($stock_id),
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

    function totalBalance($stock_id)
    {
        return ($this->getOpeningStock($stock_id) + $this->totalGrn($stock_id)) - $this->totalIssued($stock_id);
    }
    function getPhysicalStock($value)
    {
        $req_date = request('report_date');
        $stock_id = $value['id'];

        $rs1 = StockMovt::where([
            'date_recorded' => $req_date,
            'stock_id' => $stock_id, 'is_physical_update' => '1'
        ])->first();
        if ($rs1) {

            $query = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])->sum('physical_qty');

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
    function fetchStockMovement()
    {
        //whereIn('id', [15, 13])
        $data = $this->queryData();
        return response()->json($data);
    }



    function stockMovtID($stock_id)
    {
        $req_date = request('report_date');
        $query = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])->first();
        if ($query) {
            return $query->id;
        } else {
            return 0;
        }
    }

    function getOpeningStock($stock_id)
    {
        $req_date = request('yesterday_date');

        $query = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id, 'is_physical_update' => '1'])->first();

        if ($query) {
            $query_op = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])->sum('physical_qty');
            return $query_op;
        } else {

            $query2 = StockMovt::whereDate('date_recorded', '<', $req_date)->where(['stock_id' => $stock_id])->latest('date_recorded')->first();

            if ($query2) {
                return $query2->physical_qty;
            } else {
                return 0;
            }
        }
    }

    function totalIssued($stock_id)
    {
        $req_date = request('report_date');
        // DB::enableQueryLog();
        $query = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])
            ->get()->sum('issued_qty');
        //   print_r( DB::getQueryLog());
        if ($query) {

            return $query;
        } else {
            return 0;
        }
    }

    function totalGrn($stock_id)
    {
        $req_date = request('report_date');
        $query = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])
            ->get()->sum('grn_qty');
        if ($query) {

            return $query;
        } else {
            return 0;
        }
    }


    function systemStock($stock_id)
    {
        return $this->totalBalance($stock_id);
    }

    function fetchMovtStoreStatus()
    {
        $cur_date = request('report_date');  //'2022-01-25'; //Parent::currentDate();
        DB::enableQueryLog();
        $res = StockMovtStatus::whereDate('date_recorded', '=', $cur_date)
            ->where(['location' => 'store'])
            ->orderBy('id', 'DESC')->first();

        if ($res) {
            return response()->json(['status' => false]);
        } else {
            return response()->json(['status' => true]);
        }
    }
}
