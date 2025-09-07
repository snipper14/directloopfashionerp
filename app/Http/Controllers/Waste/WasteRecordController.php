<?php

namespace App\Http\Controllers\Waste;

use Carbon\Carbon;
use App\Stock\StockWaste;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Stock\RecordWasteRequest;

class WasteRecordController extends BaseController
{
    public function create(RecordWasteRequest $request)
    {


        StockWaste::updateOrCreate(
            ['waste_no' => $request->waste_no, 'stock_id' => $request->stock_id],
            $request->validated() + Parent::user_branch_id()
        );

        return response()->json($this->fetchByWasteNo($request->waste_no));
    }
    function fetchByWasteNo($waste_no)
    {

        $rs = StockWaste::with(['stock', 'department', 'waste_reason', 'unit'])
            ->where(['waste_no' => $waste_no])->get();
        return $rs;
    }
    function fetchWasteNo()
    {
        DB::enableQueryLog();
        $rs = StockWaste::with(['stock', 'department', 'waste_reason', 'unit'])
            ->where(['status' => "progress"] + Parent::user_branch_id())->get();
        //   print_r(DB::getQueryLog());
        if ($rs->isEmpty()) {
            return response()->json(["waste_no" => $this->wasteNum(), "waste_data" => []]);
        } else {
            return response()->json(["waste_no" => '', "waste_data" => $rs]);
        }
        return response()->json($this->wasteNum());
    }
    function wasteNum()
    {

        $latest = StockWaste::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $req_no = $today . '-' . 'WST' . sprintf('%04d', $string);
        $isUnique = StockWaste::where('waste_no', $req_no)->first();
        if ($isUnique) {
            $req_no = $today . '-' . 'WST' . ($latest->id + 1);
        }
        return  $req_no;
    }

    function destroy(Request $request)
    {
        StockWaste::where('id', $request->route('id'))->delete();
        return true;
    }

    function complete(Request $request)
    {
        DB::transaction(function () use ($request) {
            $res = $this->fetchByWasteNo($request->waste_no);
            foreach ($res as $value) {
              
                Inventory::updateOrCreate(
                    [
                        'stock_id' => $value['stock_id'],
                        'department_id' => $value['department_id'],
                        'branch_id' => Parent::branch_id()
                    ],
                    ['qty' => DB::raw('qty -' . $value['qty'])]
                );
            }
            StockWaste::where('waste_no', $request->waste_no)->update(['status' => 'completed']);
        }, 5);
    }

    function fetch()
    {
        
            $main_query = StockWaste::with(['stock', 'department', 'waste_reason', 'unit','staff','user'])
                ->when(request('search', '') != '', function ($query) {
                    $query->where(function ($q) {
                        $q->where('waste_no', 'LIKE', '%' . request('search') . '%')
                            ->orWhereHas('waste_reason', function ($q) {
                                $q->where('reasons', 'LIKE', '%' . request('search') . '%');
                            })
                            ->orWhereHas('stock', function ($q) {
                                $q->where('product_name', 'LIKE', '%' . request('search') . '%');
                            })
                            ->orWhereHas('department', function ($q) {
                                $q->where('department', 'LIKE', '%' . request('search') . '%');
                            })
                            ->orWhereHas('unit', function ($q) {
                                $q->where('name', 'LIKE', '%' . request('search') . '%');
                            });
                    });
                });
                $main_query->when(request('from', '') != '' && request('to', '') != '',
                function ($query) {
                   $from = date(request('from'));
                   $to = date(request('to'));
                   $query->whereBetween('date_wasted', [$from, $to]);
               });
            $res = $main_query->latest()->get();
            if (request('page') > 0) {
                $res = $main_query->latest()->paginate(20);
            }
            return response()->json($res);
        
    }
function fetchWasteItem(Request $request)
    {
        $stock_id = $request->input('stock_id');
        $waste_no = $request->input('ref_no');
        $res = StockWaste::with(['staff','user','waste_reason','stock'])->where('stock_id', $stock_id)
            ->where('waste_no', $waste_no)
            ->get();
        return response()->json($res);
    }
    function fetchTotals()
    {
        $raw_query = StockWaste::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('waste_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('waste_reason', function ($q) {
                        $q->where('reasons', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('stock', function ($q) {
                        $q->where('product_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('department', function ($q) {
                        $q->where('department', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('unit', function ($q) {
                        $q->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });
        $raw_query->when(request('from', '') != '' && request('to', '') != '',
         function ($query) {
            $from = date(request('from'));
            $to = date(request('to'));
            $query->whereBetween('date_wasted', [$from, $to]);
        });
        $raw_query->selectRaw('SUM(total) AS sum_sub_total,SUM(qty) AS total_qty
     ')->where( 'status' , 'completed',);
        $res = $raw_query->first();
        return response()->json($res);
    }

}
