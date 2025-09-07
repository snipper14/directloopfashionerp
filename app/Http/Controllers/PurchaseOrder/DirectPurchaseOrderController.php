<?php

namespace App\Http\Controllers\PurchaseOrder;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

use App\Http\Controllers\BaseController;
use App\PurchaseOrder\DirectPurchaseOrder;
use App\Http\Requests\PurchaseOrder\DirectPurchaseOrderRequest;
use Barryvdh\DomPDF\Facade as PDF;

class DirectPurchaseOrderController extends BaseController
{
    public function create(DirectPurchaseOrderRequest $request)
    {
        if ($this->containsItem($request)) {
            DirectPurchaseOrder::where([
                'order_no' => $request->order_no,

                'stock_id' => $request->stock_id
            ])
                ->update(array(
                    'qty' => DB::raw('qty + ' . $request->qty),
                    'sub_total' => DB::raw('sub_total + ' . $request->sub_total),
                    'taxes' => DB::raw('taxes + ' . $request->taxes)
                ));
        } else {
            DirectPurchaseOrder::create(
                $request->validated() +
                    Parent::user_branch_id() +
                    ['approver_id' => Parent::user_id()]
            );
        }
        $res = DirectPurchaseOrder::with(['stock'])->where(['order_no' => $request->order_no])->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }


    function containsItem($request)
    {
        $res = DirectPurchaseOrder::where([
            'order_no' => $request->order_no,
            'stock_id' => $request->stock_id
        ])->get();
        return  $res->isEmpty() ? false : true;
    }

    public function fetchOrderNo()
    {
        return  response()->json($this->orderNumber());
    }
    function orderNumber()
    {


        $latest = DirectPurchaseOrder::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'DPO' . sprintf('%04d', $string);
        $isUnique = DirectPurchaseOrder::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'PO' . ($latest->id + 1);
        }
        return  $orderNo;
    }

    function fetchByOrder()
    {
        $res = DirectPurchaseOrder::with(['stock'])->where([
            'order_no' => request('order_no'),

        ])
            ->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }


    function completeOrder(Request $request)
    {
        $this->validate($request, [
            'order_no' => 'required',

        ]);
        DirectPurchaseOrder::where(["order_no" => $request->order_no])
            ->update(["progress" => "completed"]);
        return true;
    }


    function fetch()
    {
        $filled = array_filter(request()->only([
            'user_id',
            'order_no',

        ]));

        $raw_query =  DirectPurchaseOrder::with(['branch', 'stock',  'user']);
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {
                if ($column == 'user_id') {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('order_deadline', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('order_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('progress', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('internal_confirmation', 'LIKE', '%' . request('search') . '%')


                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->where(Parent::branch_array());
        if (!Parent::checkForPermission()) {
            $raw_query->where('user_id', Parent::user_id());
        }
        $raw_query->groupBy('order_no')->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(sub_total) AS sum_sub_total,SUM(qty) AS total_qty,
      SUM(qty_delivered) AS total_qty_delivered');

        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(20);
        }

        return response()->json($res);
    }

    function deleteOrder(Request $request)
    {
        $id = $request->route('id');
        DirectPurchaseOrder::find($id)->delete();
        return true;
    }

    function destroy()
    {
        DirectPurchaseOrder::where([
            'order_no' => request('order_no'),

        ])
            ->delete();
        return true;
    }
    function fetchPendingOrder()
    {
        $res = DirectPurchaseOrder::with(['stock'])->where([
            'order_no' => request('order_no'),

        ])
            ->whereRaw('qty > qty_delivered')
            ->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }

    function approveOrder(Request $request)
    {
        DirectPurchaseOrder::where([
            'order_no' => $request->order_no,

        ])
            ->update(['internal_confirmation' => 'confirmed', "progress" => "completed", 'approver_id' => Parent::user_id()]);
        return true;
    }

    function disapproveOrder(Request $request)
    {
        DirectPurchaseOrder::where([
            'order_no' => $request->order_no,

        ])
            ->update(['internal_confirmation' => 'pending', "progress" => "progress", 'approver_id' => Parent::user_id()]);
        return true;
    }

    function downloadPO(Request $request)
    {

        $order_no = $request->route('order_no');
        $res =  DirectPurchaseOrder::with(['stock', 'user.department', 'branch',  'approver'])->where([
            'order_no' => $order_no,

        ])->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "direct_po" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res];
        $pdf = PDF::loadView('pdf.direct_po', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }
}
