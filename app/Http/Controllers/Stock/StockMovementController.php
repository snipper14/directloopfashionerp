<?php

namespace App\Http\Controllers\Stock;

use App\Stock\Stock;
use App\Sale\SoldItem;
use App\Stock\StockMovt;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\PurchaseOrder\DirectPOReceivable;
use App\Sale\RoomServiceSale;
use App\Sale\RoomServiceSoldItem;
use App\Sale\TakeAwaySale;
use App\Sale\TakeAwaySoldItem;
use App\Stock\StockMovtStatus;

class StockMovementController extends BaseController
{
    function fetch()
    {

        return response()->json($this->queryStockMovt());
    }
    function queryStockMovt()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = StockMovt::with(["user", "stock", 'branch'])->orderBy('id', 'ASC')->groupBy('stock_id');
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('date_recorded', [$from, $to]);
        });

        $raw_query->when(request('search') != "", function ($q) {
            $q->whereHas("stock", function ($q) {
                $q->where("product_name", 'LIKE', '%' . request('search') . '%')

                    ->orWhere("code", 'LIKE', '%' . request('search') . '%');
            });
        });
        $raw_query->selectRaw("*,
  SUM(grn_qty) AS sum_grn_qty,
  SUM(stock_returns) AS sum_stock_returns,
SUM(issued_qty) AS sum_issued_qty

  ");
        $res = $raw_query->get();

        if (!$res->isEmpty()) {
            $result_array = array();
            foreach ($res as $value) {
                $system_stock = StockMovt::where('stock_id', $value['stock_id'])->orderBy('id', 'desc')
                    ->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

                        $query->whereBetween('date_recorded', [$from, $to]);
                    })
                    ->first()->system_qty;

                $total_stock = $value['opening_stock'] + $value['sum_grn_qty'];
                $total_balance = $total_stock - $value['sum_issued_qty'];
                $deficit = $total_balance - $system_stock;
                //  $deficit = $value['physical_qty'] - $total_balance;
                $result_array[] = array(

                    'id' => $value['id'],
                    'stock_id' => $value['stock_id'],
                    'opening_stock' => $value['opening_stock'],
                    'branch' => $value['branch'],
                    'user' => $value['user'],
                    'sum_grn_qty' => $value['sum_grn_qty'],
                    'sum_stock_returns' => $value['sum_stock_returns'],
                    'total_stock' => $total_stock,
                    'sum_issued_qty' => $value['sum_issued_qty'],
                    'total_balance' => $total_balance,
                    'system_stock' => $system_stock,
                    'physical_qty' => $value['physical_qty'],
                    'deficit' => $deficit,
                    'stock' => $value['stock'],
                );
            }
            return $result_array;
        }

        return $res;
    }

    function fetchAll()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = StockMovt::with(["user", "stock"])->orderBy('id', 'ASC');
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
    function fetchMovtDeaprtmentLogs()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = StockMovt::with(["user", "stock"])->orderBy('id', 'ASC')->where('is_internal_stock', 1);
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
    function fetchDeptStockMovt()
    {
        return response()->json($this->departmentMovtQuery());
    }
    function departmentMovtQuery()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = StockMovt::with(["user", "stock", 'branch'])
            ->where('is_internal_stock', 1)
            ->orderBy('id', 'ASC')->groupBy('stock_id');
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('date_recorded', [$from, $to]);
        });

        $raw_query->when(request('search') != "", function ($q) {
            $q->whereHas("stock", function ($q) {
                $q->where("product_name", 'LIKE', '%' . request('search') . '%')

                    ->orWhere("code", 'LIKE', '%' . request('search') . '%');
            });
        });
        $raw_query->selectRaw("*,

SUM(dept_issued_qty) AS sum_dept_issued_qty

");
        $res = $raw_query->get();

        if (!$res->isEmpty()) {
            $result_array = array();
            foreach ($res as $value) {
                $dept_system_qty = StockMovt::where('stock_id', $value['stock_id'])->orderBy('id', 'desc')
                    ->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

                        $query->whereBetween('date_recorded', [$from, $to]);
                    })
                    ->first()->dept_system_qty;


                //////////    total items sold on POS RESTAURANT 
                $total_stock_pos = 0;
                $total_sold_pos = SoldItem::where('stock_id', $value['stock_id'])
                    ->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

                        $query->whereBetween('order_date', [$from, $to]);
                    })
                    ->selectRaw('SUM(qty) AS sum_sold_items')->first();
                if ($total_sold_pos) {
                    $total_stock_pos = $total_sold_pos->sum_sold_items;
                }
                /////////ens total sale pos res 

                ///////  total items sold on take away
                $total_stock_takeaway = 0;
                $total_sold_takeaway = TakeAwaySoldItem::where('stock_id', $value['stock_id'])
                    ->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

                        $query->whereBetween('order_date', [$from, $to]);
                    })
                    ->selectRaw('SUM(qty) AS sum_sold_items')->first();
                if ($total_sold_takeaway) {
                    $total_stock_takeaway = $total_sold_takeaway->sum_sold_items;
                }
                ////end takeaway sales


                ////// total items sold on roomservice
                $total_stock_roomservice = 0;
                $total_sold_roomservice = RoomServiceSoldItem::where('stock_id', $value['stock_id'])
                    ->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

                        $query->whereBetween('order_date', [$from, $to]);
                    })
                    ->selectRaw('SUM(qty) AS sum_sold_items')->first();
                if ($total_sold_roomservice) {
                    $total_stock_roomservice = $total_sold_roomservice->sum_sold_items;
                }
                /////end roomservice sales


                $total_stock = $value['opening_dept_stock'] + $value['sum_dept_issued_qty'];

                //  $deficit = $total_stock - $dept_system_qty;
                $deficit = $value['physical_dept_qty'] - $total_stock;

                $total_sold = $total_stock_pos + $total_stock_takeaway + $total_stock_roomservice;
                $result_array[] = array(

                    'id' => $value['id'],
                    'stock_id' => $value['stock_id'],
                    'opening_dept_stock' => $value['opening_dept_stock'],

                    'sum_dept_issued_qty' => $value['sum_dept_issued_qty'],

                    'total_stock' => $total_stock,

                    'dept_system_qty' => $dept_system_qty,
                    'physical_dept_qty' => $value['physical_dept_qty'],
                    'deficit' => $deficit,
                    'total_sold' => $total_sold,
                    'stock' => $value['stock'],
                    'branch' => $value['branch'],
                    'user' => $value['user'],
                );
            }
            return $result_array;
        }

        return $res;
    }







    function downloadPdf()
    {
        ini_set('max_execution_time', 600);


        $res = $this->queryStockMovt();
        // return response()->json($this->queryStockMovt());
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "stock_movement" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ['data' => $this->queryStockMovt()] + ["from" => request('from'), 'to' => request('to')];
        //return response()->json($data);
        $pdf = PDF::loadView('pdf.stock_movement', $data); //compact('data')
        $pdf->save($path);


        return response()->download($path);
    }

    function downloadDeptPdfLogs()
    {
        ini_set('max_execution_time', 600);

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = StockMovt::with(["user", 'branch', "stock"])->where('is_internal_stock', 1)->orderBy('id', 'ASC');
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('date_recorded', [$from, $to]);
        });
        $raw_query->when(request('search') != "", function ($q) {
            $q->whereHas("stock", function ($q) {
                $q->where("product_name", 'LIKE', '%' . request('search') . '%')

                    ->orWhere("code", 'LIKE', '%' . request('search') . '%');
            });
        });

        $res = $raw_query->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "stock_movement" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res, "from" => request('from'), 'to' => request('to')];

        $pdf = PDF::loadView('pdf.department_stock_movt_log', $data); //compact('data')
        $pdf->save($path);


        return response()->download($path);
    }


    function downloadPdfLogs()
    {
        ini_set('max_execution_time', 600);

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = StockMovt::with(["user", 'branch', "stock"])->orderBy('id', 'ASC');
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('date_recorded', [$from, $to]);
        });
        $raw_query->when(request('search') != "", function ($q) {
            $q->whereHas("stock", function ($q) {
                $q->where("product_name", 'LIKE', '%' . request('search') . '%')

                    ->orWhere("code", 'LIKE', '%' . request('search') . '%');
            });
        });

        $res = $raw_query->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "stock_movement" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res, "from" => request('from'), 'to' => request('to')];

        $pdf = PDF::loadView('pdf.stock_movement_logs', $data); //compact('data')
        $pdf->save($path);


        return response()->download($path);
    }

    function downloadPOSStockMovtPdf()
    {
        ini_set('max_execution_time', 600);


        $res = $this->departmentMovtQuery();
        // return response()->json($this->queryStockMovt());
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "department_stock_movt" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ['data' => $res] + ["from" => request('from'), 'to' => request('to')];
        //return response()->json($data);
        $pdf = PDF::loadView('pdf.department_stock_movt', $data); //compact('data')
        $pdf->save($path);


        return response()->download($path);
    }


    function updatePhysicalStock(Request $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->update_data;
            foreach ($data as $value) {
                  Stock::where('id', $value['stock_id'])->update(['store_qty' => $value['physical_qty']]);
                StockMovt::create(
                    [
                        'stock_id' => $value['stock_id'],
                        'date_recorded' => $request->report_date,
                        'physical_qty' => $value['physical_qty'],
                        'is_physical_update' => '1',

                    ] + Parent::user_branch_id()
                );
            }
            StockMovtStatus::create(['updated' => '1', 'location' => 'store', 'date_recorded' => $request->report_date] + Parent::user_branch_id());
        }, 5);
        return response()->json($this->queryStockMovt());
    }

    function updateDeptPhysicalStock(Request $request)
    {
        DB::transaction(function () use ($request) {
            $data = $request->update_data;
            foreach ($data as $value) {
                 Stock::where('id', $value['stock_id'])->update(['qty' => $value['physical_dept_qty']]);

                StockMovt::create(
                    [
                        'stock_id' => $value['stock_id'],
                        'date_recorded' => $request->report_date,
                        'physical_dept_qty' => $value['physical_dept_qty'],
                        'is_physical_update' => '1',

                    ] + Parent::user_branch_id()
                );
            }
            StockMovtStatus::create([
                'updated' => '1', 'location' => 'department', 'date_recorded' => $request->report_date
            ] + Parent::user_branch_id());
        }, 5);
        return response()->json($this->queryStockMovt());
    }
}
