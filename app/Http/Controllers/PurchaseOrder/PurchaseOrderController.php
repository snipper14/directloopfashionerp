<?php

namespace App\Http\Controllers\PurchaseOrder;

use Carbon\Carbon;
use App\Scopes\BranchScope;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\PurchaseOrder\PurchaseOrder;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\SupplierInvoice\SupplierInvoice;
use App\Http\Requests\PurchaseOrder\PurchaseOrderRequest;

class PurchaseOrderController extends BaseController
{
    public function create(PurchaseOrderRequest $request)
    {


        PurchaseOrder::updateOrCreate(
            [
                'order_no' => $request->order_no,
                'supplier_id' => $request->supplier_id,
                'stock_id' => $request->stock_id
            ],
            $request->validated() + [


                'approver_id' => Auth::user()->id
            ] + Parent::user_branch_id()
        );

        $res = PurchaseOrder::with(['stock'])->where(['order_no' => $request->order_no, "branch_id" => Auth::user()->branch_id])->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }

    function containsItem($request)
    {
        $res = PurchaseOrder::where([
            'order_no' => $request->order_no,
            'supplier_id' => $request->supplier_id,
            'stock_id' => $request->stock_id,
            "branch_id" => Auth::user()->branch_id
        ])->get();
        return  $res->isEmpty() ? false : true;
    }
    function completeOrder(Request $request)
    {
        $this->validate($request, [
            'order_no' => 'required',
            'supplier_id' => 'required'

        ]);
        // DB::transaction(function () use ($request) {


        // PurchaseOrder::where(['progress' => "progress", "order_no" => $request->order_no, "branch_id" => Auth::user()->branch_id])
        //     ->update(["progress" => "completed"]);

        //  }, 5);
        $order_no = $this->generateFinalPO();
        PurchaseOrder::where(['order_no' => $request->order_no, 'supplier_id' => $request->supplier_id, "progress" => "progress"])->update([
            'order_no' => $order_no,
            "progress" => "completed"
        ]);
        return true;
    }
    function generateFinalPO()
    {


        $latest = PurchaseOrder::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'PO' . sprintf('%04d', $string);
        $isUnique = PurchaseOrder::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'PO' . ($latest->id + 1);
        }
        return  $orderNo;
    }
    function generateFinalPODELETE()
    {
        $order_no =  1; //
        $res = PurchaseOrder::where('progress', 'completed')
            ->whereRaw('order_no REGEXP "^[0-9]+$"') // Only numeric order_no

            ->orderByRaw('CAST(order_no AS UNSIGNED) DESC')->latest()
            ->first(); //PurchaseOrder::where(["progress" => "completed"])->latest()->first();

        if ($res) {
            $prev_order_no = $res->order_no;


            // if (strpos($prev_order_no, 'PO') !== false) {

            //     $order_no = 1;
            // } else {
            $order_no = $prev_order_no + 1;

            // }
        }

        return  $order_no;
    }

    function deleteOrder(Request $request)
    {
        $id = $request->route('id');
        PurchaseOrder::withoutGlobalScope(BranchScope::class)->find($id)->delete();
        return true;
    }
    public function fetchOrderNo()
    {
        return  response()->json($this->orderNumber());
    }
    function orderNumber()
    {


        $latest = PurchaseOrder::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderNo = $today . '-' . 'PO' . sprintf('%04d', $string);
        $isUnique = PurchaseOrder::where('order_no', $orderNo)->first();
        if ($isUnique) {
            $orderNo = $today . '-' . 'PO' . ($latest->id + 1);
        }
        return  $orderNo;
    }

    function mainQuery()
    {
        $filled = array_filter(request()->only([
            'supplier_id',
            'order_no',

        ]));
        // if (Parent::showBranchPermission()) {

        //     $raw_query = PurchaseOrder::withoutGlobalScope(BranchScope::class)->with(['branch', 'stock', 'supplier', 'user']);
        // } else {
        $raw_query =  PurchaseOrder::with(['branch', 'stock', 'supplier', 'user']);
        //  }

        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {
                if ($column == 'supplier_id') {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        });
        $raw_query->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) {
                $from = date(request('from'));
                $to = date(request('to'));
                $query->whereBetween('receipt_date', [$from, $to]);
            }
        );

        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('vendor_reference', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('order_deadline', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('receipt_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('progress', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('internal_confirmation', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        });
        if (!Parent::checkForPermission()) {
            $raw_query->where('user_id', Parent::user_id());
        }
        if (Parent::showBranchPermission()) {
            $raw_query->withoutGlobalScope(BranchScope::class);
        }

        return $raw_query;
    }
    function fetch()
{
    $raw_query = $this->mainQuery();

    // Explicit join to suppliers table
    $raw_query->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id');
$raw_query->leftJoin('users', 'users.id', '=', 'purchase_orders.user_id');
    $raw_query->groupBy(['purchase_orders.order_no', 'purchase_orders.supplier_id']);

    $raw_query->selectRaw('
        purchase_orders.*,
        suppliers.company_name,
           users.name AS user_name,
        SUM(sub_total) AS sum_sub_total,
        SUM(qty) AS total_qty,
        SUM(qty_delivered) AS total_qty_delivered
    ');

    $raw_query->when(request('sort_supplier'), function ($query) {
        $query->orderBy('suppliers.company_name', request('sort_supplier')); // ASC or DESC
    });
  $raw_query->when(request('sort_receipt_date'), function ($query) {
        $query->orderBy('receipt_date', request('sort_receipt_date'));
    });

    $raw_query->when(request('sort_user'), function ($query) {
        $query->orderBy('users.name', request('sort_user'));
    });
      $raw_query->when(request('sort_sum_sub_total'), function ($query) {
        $query->orderBy('sum_sub_total', request('sort_sum_sub_total'));
    });

    $raw_query->orderBy('purchase_orders.id', 'DESC');

    $res = $raw_query->paginate(50);

    return response()->json($res);
}

    function fetchTotals()
    {
        $raw_query = $this->mainQuery();;
        $raw_query->selectRaw('SUM(sub_total) AS sum_sub_total,SUM(qty) AS total_qty,
      SUM(qty_delivered) AS total_qty_delivered')->where('progress', 'completed',);
        $res = $raw_query->first();
        return response()->json($res);
    }


    function fetchUnconfirmed()
    {
        $raw_query = $this->mainQuery();
        $raw_query->where([
            'branch_id' => Auth::user()->branch_id,
            'progress' => 'completed',
            'internal_confirmation' => 'pending'
        ])

            ->groupBy(['order_no', 'supplier_id'])->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(sub_total) AS sum_sub_total,SUM(qty) AS total_qty,
      SUM(qty_delivered) AS total_qty_delivered');
        $res = $raw_query->paginate(20);
        return response()->json($res);
    }


    function fetchAll()
    {
        $raw_query =  PurchaseOrder::with(['branch', 'stock', 'supplier', 'user']);
        $raw_query->where(['branch_id' => Auth::user()->branch_id])->groupBy('order_no')->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(sub_total) AS sum_sub_total,SUM(qty) AS total_qty');
        $res = $raw_query->get();
        return response()->json($res);
    }

    function fetchByOrder()
    {
        $raw_query = PurchaseOrder::with(['stock', 'branch', 'user', 'supplier'])->where([
            'order_no' => request('order_no'),
            'supplier_id' => request('supplier_id'),

        ]);
        if (!Parent::checkForPermission()) {
            $raw_query->where('user_id', Parent::user_id());
        }
        if (Parent::showBranchPermission()) {
            $raw_query->withoutGlobalScope(BranchScope::class);
        }
        $res = $raw_query->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }

    function fetchPendingOrder()
    {
        $res = PurchaseOrder::with(['stock'])->where([
            'order_no' => request('order_no'),
            'supplier_id' => request('supplier_id'),
            "branch_id" => Auth::user()->branch_id
        ])
            ->whereRaw('qty > qty_delivered')
            ->orderBy('id', 'DESC')->get();
        return response()->json($res);
    }


    function approveOrder()
    {
        DB::transaction(function () {
            PurchaseOrder::where([
                'order_no' => request('order_no'),
                'supplier_id' => request('supplier_id'),
                "branch_id" => Auth::user()->branch_id
            ])
                ->update(['internal_confirmation' => 'confirmed', "progress" => "completed", 'approver_id' => Auth::user()->id]);
            $order_details = PurchaseOrder::where([
                'order_no' => request('order_no'),
                'supplier_id' => request('supplier_id')
            ])->get();
        }, 5);
        return true;
    }


    function destroy()
    {
        PurchaseOrder::where([
            'order_no' => request('order_no'),
            'supplier_id' => request('supplier_id'),
            "branch_id" => Auth::user()->branch_id
        ])
            ->delete();
        return true;
    }

    function vendorApproval()
    {
        PurchaseOrder::where([
            'order_no' => request('order_no'),
            'supplier_id' => request('supplier_id'),
            "branch_id" => Auth::user()->branch_id
        ])
            ->update(['internal_confirmation' => 'confirmed', "progress" => "completed", 'supplier_confirmation' => 'confirmed']);
        return true;
    }

    function downloadPO(Request $request)
    {
        ini_set('max_execution_time', 2400);
        $supplier_id = $request->route('supplier_id');
        $order_no = $request->route('order_no');
        $res =  PurchaseOrder::with(['stock', 'user.department', 'branch', 'supplier', 'approver'])->where([
            'order_no' => $order_no,
            'supplier_id' => $supplier_id,
            "branch_id" => Auth::user()->branch_id
        ])->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $order_no . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res];
        $pdf = PDF::loadView('pdf.po', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }
}
