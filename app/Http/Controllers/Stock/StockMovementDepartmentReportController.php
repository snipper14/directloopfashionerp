<?php

namespace App\Http\Controllers\Stock;

use Carbon\Carbon;
use App\Stock\Stock;
use App\Stock\StockMovt;
use Illuminate\Http\Request;
use App\Stock\StockMovtStatus;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Invoices\Invoice;
use App\POS\ArchivedOrder;
use App\POS\POSOrder;
use App\POS\RoomOrder;
use App\Stock\IssueStock;

class StockMovementDepartmentReportController extends BaseController
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

                //   $total_stock =  ($this->getOpeningStock($stock_id) + $this->totalIssued($stock_id));

                $data[] = [
                    'stock_id' => $stock_id,
                    'id' => $this->stockMovtID($stock_id),
                    'code' => $value['code'],

                    'product_name' => $value['product_name'],
                    'selling_price' => $value['selling_price'],
                    "opening_dept_stock" => $this->getOpeningStock($stock_id),
                    'sum_dept_issued_qty' =>  $this->totalIssued($stock_id),
                    'total_stock' => $this->totalBalance($stock_id),
                    'sum_dept_issued_qty' =>  $this->totalIssued($stock_id),

                    'total_sold' => $this->soldStock($stock_id),
                    'dept_system_qty' => $this->systemStock($stock_id),
                    'physical_dept_qty' => $this->getPhysicalStock($value),
                    // 'deficit' => $value['qty'] - $this->systemStock($stock_id),

                ];
            }
        }
        return $data;
    }
    function totalBalance($stock_id)
    {
        return ($this->getOpeningStock($stock_id) + $this->totalIssued($stock_id));
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

            $query = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])->sum('physical_dept_qty');

            if ($query) {
                return $query;
            } else {
                return 0;
            }
        } else {


            return $this->systemStock($stock_id);
        }
    }
    function fetchDeptStockMovt()
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
            $query_res = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])->sum('physical_dept_qty');
            return $query_res;
        } else {

            $query2 = StockMovt::whereDate('date_recorded', '<', $req_date)
            ->where(['stock_id' => $stock_id])->latest('date_recorded')->first();

            if ($query2) {
                return $query2->physical_dept_qty;
            } else {
                return 0;
            }
        }
    }
function totalIssued($stock_id){
    $req_date = request('report_date');
    $query = IssueStock::where(['date_issued' => $req_date, 'stock_id' => $stock_id,'status'=>'complete'])
        ->first();
    if ($query) {
        $query_dept = IssueStock::where(['date_issued' => $req_date, 'stock_id' => $stock_id,'status'=>'complete'])
        ->sum('final_qty');
        return $query_dept;
    } else {
        return 0;
    }
}
    // function totalIssued($stock_id)
    // {
    //     $req_date = request('report_date');
    //     $query = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])
    //         ->first();
    //     if ($query) {
    //         $query_dept = StockMovt::where(['date_recorded' => $req_date, 'stock_id' => $stock_id])
    //         ->sum('dept_issued_qty');
    //         return $query_dept;
    //     } else {
    //         return 0;
    //     }
    // }


   
    function soldStock($stock_id)
    {
        $total_order = 0;
        $from = request('opening_stock_timestamp');
        $to = request('closing_stock_timestamp');
        $query_pos = POSOrder::withTrashed()->whereBetween('created_at', [$from, $to])
            ->where(['stock_id' => $stock_id])->get()->sum('qty');
        $query_rs = RoomOrder::withTrashed()->whereBetween('created_at', [$from, $to])
            ->where(['stock_id' => $stock_id])->get()->sum('qty');
            $query_invoice = Invoice::withTrashed()->whereBetween('created_at', [$from, $to])
            ->where(['stock_id' => $stock_id])->get()->sum('qty');

        if ($query_pos) {

            $total_order += $query_pos;
        }
        if ($query_rs) {

            $total_order += $query_rs;
        }
        if ($query_invoice) {

            $total_order += $query_invoice;
        }
        return ($total_order + $this->soldArchiveStock($stock_id));
    }
    function soldArchiveStock($stock_id)
    {
        $total_order = 0;
        $from = request('opening_stock_timestamp');
        $to = request('closing_stock_timestamp');
        $query_pos = ArchivedOrder::whereBetween('created_at', [$from, $to])
            ->where(['stock_id' => $stock_id, ])->get()->sum('qty');
       
        if ($query_pos) {

            $total_order += $query_pos;
        }
        return $total_order;
    }

    function systemStock($stock_id)
    {
        return ($this->totalBalance($stock_id) - $this->soldStock($stock_id));
    }

    function fetchBlankStockSheet()
    {
        $raw_query = Stock::orderBy('id', 'ASC');
        $raw_query->when(request('search') != "", function ($q) {

            $q->where("product_name", 'LIKE', '%' . request('search') . '%')

                ->orWhere("code", 'LIKE', '%' . request('search') . '%');
        });
        $raw_query->when(request('department_id') != "", function ($q) {

            $q->where("department_id", request('department_id'));
        });

        $dept_res = $raw_query->groupBy('product_category_id')->get();
        $data = array(); {

            foreach ($dept_res as $value) {
                $dept_id = $value['product_category_id'];
                $query = Stock::where('product_category_id', $dept_id);
                $query->when(request('search') != "", function ($q) {

                    $q->where("product_name", 'LIKE', '%' . request('search') . '%')

                        ->orWhere("code", 'LIKE', '%' . request('search') . '%');
                });
                $query->when(request('department_id') != "", function ($q) {

                    $q->where("department_id", request('department_id'));
                });
                $res = $query->get();
                $data[] = array(
                    'category' => $value['product_category']->name,
                    'stock' => $res,

                );
            }
        }
        return response()->json($data);
    }

    function fetchMovtStatus()
    {
        $cur_date = request('report_date'); //'2022-01-25'; //Parent::currentDate();

        $res = StockMovtStatus::whereDate('date_recorded', '=', $cur_date)
            ->where(['location' => 'department'])
            ->orderBy('id', 'DESC')->first();
        if ($res) {
            return response()->json(['status' => false]);
        } else {
            return response()->json(['status' => true]);
        }
    }
}
