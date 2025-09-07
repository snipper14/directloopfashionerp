<?php

namespace App\Http\Controllers\PurchaseReturn;

use PDFS;
use Carbon\Carbon;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Stock\StockMovt;
use App\Suppliers\Supplier;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\PurchaseReturns\PurchaseReturn;
use App\Http\Controllers\BaseController;
use phpDocumentor\Reflection\Types\Parent_;
use App\Http\Requests\PurchaseReturn\PurchaseReturnRequest;
use App\Inventory\Inventory;

class PurchaseReturnController extends BaseController
{

    public function create(PurchaseReturnRequest $request)
    {
        DB::transaction(function () use ($request) {
            if ($this->containsItem($request)) { 
                PurchaseReturn::where([
                    'supplier_id' => $request->supplier_id,
                    'stock_id' => $request->stock_id,
                    'return_code' => $request->return_code
                ])
                    ->update(array('qty' => DB::raw('qty + ' . $request->qty)));
            } else {
                $prev_store_qty = Inventory::where('stock_id', $request->stock_id)->first()->main_store_qty;
                PurchaseReturn::create(['opening_stock' => $prev_store_qty] + Parent::user_branch_id() + $request->validated());
            }
        }, 5);

        $res = PurchaseReturn::with(['stock'])->where([
            'return_code' => $request->return_code,
            "branch_id" => Parent::branch_id()
        ])->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }



    function containsItem($request)
    {
        $res = PurchaseReturn::where(
            [
                'return_code' => $request->return_code,
                'stock_id' => $request->stock_id
            ]
        )->get();
        return  $res->isEmpty() ? false : true;
    }
    function fetchCode()
    {
        return response()->json($this->returnCode());
    }
    function returnCode()
    {


        $latest = PurchaseReturn::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $return_code = $today . '-' . 'RC' . sprintf('%04d', $string);
        $isUnique = PurchaseReturn::where('return_code', $return_code)->first();
        if ($isUnique) {
            $return_code = $today . '-' . 'RC' . ($latest->id + 1);
        }
        return  $return_code . Parent::user_id();
    }

    function fetchPendingReturns()
    {
        $supplier_id = request('supplier_id');
        $res = PurchaseReturn::with(['stock'])->where(['supplier_id' => $supplier_id, 'status' => "progress"])->get();
        if ($res->isEmpty()) {
            return response()->json(['results' => "return_code", "data" => $this->returnCode()]);
        } else {
            return response()->json(['results' => "data", "data" => $res]);
        }
    }
    function destroy(Request $request)
    {
        PurchaseReturn::find($request->route('id'))->delete();
        return true;
    }
    function fetch()
    {
        $filled = array_filter(request()->only([
            'supplier_id',
            'return_code',

        ]));

        $raw_query =  PurchaseReturn::with(['stock', 'supplier', 'user']);
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {
                if ($column == 'supplier_id') {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('return_code', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('return_date', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    )
                    ->orWhereHas(
                        'stock',
                        function ($query) {
                            $query->where('product_name', 'LIKE', '%' . request('search') . '%')
                                ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->where(['branch_id' => Parent::branch_id(), 'status' => "complete"])

            ->orderBy('id', 'DESC');

        if (request('page') > 0) {
            $res = $raw_query->paginate(10);
        } else {
            $res = $raw_query->get();
        }
        return response()->json($res);
    }
    function downloadReturns(Request $request)
    {
        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {
            $update_data = PurchaseReturn::where(
                ['return_code' => $request->return_code, 'status' => 'progress']
            )
                ->get();
            PurchaseReturn::where([
                'return_code' => $request->return_code,
                'supplier_id' => $request->supplier_id,

            ])->update(['status' => 'complete']);

            foreach ($update_data as $value) {
                if ($value['deduct_stock']) {
                    Stock::where(['id' => $value['stock_id']])->decrement('main_store_qty', $value['qty']);
                 
                }
            }
        }, 5);
        $res = PurchaseReturn::with(['stock', 'supplier', 'user'])->where([
            'supplier_id' => $request->supplier_id,
            'return_code' => $request->return_code,
            'status' => "complete"
        ])->get();


        $branch = Branch::where('id', Parent::branch_id())->first();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $res[0]->return_code . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }

        $data = ["data" => $res, 'branch' => $branch];

        $pdf = PDF::loadView('pdf.goods_return_note', $data); //compact('data') 
        $pdf->save($path);
        return response()->download($path);
    }
}
