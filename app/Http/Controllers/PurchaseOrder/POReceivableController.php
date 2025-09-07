<?php

namespace App\Http\Controllers\PurchaseOrder;

use Carbon\Carbon;
use App\Stock\Stock;
use App\Branch\Branch;
use App\Stock\StockMovt;
use App\Scopes\BranchScope;
use App\Suppliers\Supplier;
use App\Inventory\Inventory;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\PurchaseOrder\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\SupplierInvoice\SupplierInvoice;
use Illuminate\Support\Facades\Validator;
use App\PurchaseOrder\PurchaseOrderReceivable;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;
use App\Http\Requests\PurchaseOrder\POReceivableRequest;

class POReceivableController extends BaseController
{
    function create(POReceivableRequest $request)
    {
        DB::transaction(function () use ($request) {

            $prev_store_qty = Stock::where('id', $request->stock_id)->first()->main_store_qty;

            PurchaseOrderReceivable::create(['opening_stock' => $prev_store_qty] + $request->validated() + [
                'user_id' => Auth::user()->id,
                'branch_id' => Auth::user()->branch_id,

            ]);

            PurchaseOrderReceivableWithBatch::updateOrCreate(
                ['stock_id' => $request->stock_id, 'batch_no' => $request->batch_no],
                [
                    'qty_delivered' => DB::raw('qty_delivered +' . $request->qty_delivered),
                    'user_id' => Auth::user()->id,
                    'branch_id' => Auth::user()->branch_id
                ] + $request->validated()
            );
        }, 5);
        $res = PurchaseOrderReceivable::with(['stock'])->where([
            'order_no' => $request->order_no,
            'branch_id' => Auth::user()->branch_id,
            'status' => 'pending'
        ])->get();
        return response()->json($res);
    }
    function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            // PurchaseOrder::where('id', $request->purchase_order_id)->decrement('qty_delivered', $request->qty_delivered);
            $res = PurchaseOrderReceivable::where('id', $request->id)->first();

            PurchaseOrderReceivableWithBatch::where(
                ['stock_id' => $res->stock_id, 'batch_no' => $res->batch_no]
            )->update([
                'qty_delivered' => DB::raw('qty_delivered -' . $res->qty_delivered)
            ]);

            PurchaseOrderReceivable::find($request->id)->delete();
        }, 5);
        return true;
    }
    function fetchPendingDelivery(Request $request)
    {
        $res = PurchaseOrderReceivable::with(['stock'])->where([
            'order_no' => request('order_no'),

            'supplier_id' => request('supplier_id'),
            'branch_id' => Auth::user()->branch_id,
            'status' => 'pending'
        ])->get();
        return response()->json($res);
    }
    function fetchItemizedGrn()
    {
        $filled = array_filter(request()->only([
            'supplier_id',
            'order_no',
            'delivery_no',
            'batch_no'
        ]));
        $raw_query = PurchaseOrderReceivable::with(['branch','purchase_order', 'purchase_order.branch', 'stock', 'supplier', 'user']);
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {
                if ($column == 'supplier_id') {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        });
        if (request('from') != "" && request('to') != "") {

            $from = request('from');
            $to = request('to');
            $raw_query->whereBetween('received_date', [$from, $to]);
        }
        if (request('from_expiry') != "" && request('to_expiry') != "") {

            $from_expiry = request('from_expiry');
            $to_expiry = request('to_expiry');
            $raw_query->whereBetween('expiry_date', [$from_expiry, $to_expiry]);
        }
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('delivery_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('stock', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });

        $raw_query->where(['status' => 'completed'])->orderBy('id', 'DESC');
        if (Parent::showBranchPermission()) {
          //  $raw_query->withoutGlobalScope(BranchScope::class);
        }
        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(40);
        }

        return response()->json($res);
    }
    function fetchItemizedGrnBatch()
    {
        $filled = array_filter(request()->only([
            'supplier_id',
            'order_no',
            'delivery_no',
            'batch_no'
        ]));
        $raw_query = PurchaseOrderReceivableWithBatch::with(['branch','purchase_order', 'purchase_order.branch', 'stock', 'supplier', 'user']);
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {
                if ($column == 'supplier_id') {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        });
        if (request('from') != "" && request('to') != "") {

            $from = request('from');
            $to = request('to');
            $raw_query->whereBetween('received_date', [$from, $to]);
        }
        if (request('from_expiry') != "" && request('to_expiry') != "") {

            $from_expiry = request('from_expiry');
            $to_expiry = request('to_expiry');
            $raw_query->whereBetween('expiry_date', [$from_expiry, $to_expiry]);
        }
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('delivery_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('stock', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });
$raw_query->when(request('sort_expiry_date'), function ($query) {
    $query->orderBy('expiry_date', request('sort_expiry_date'));
});
        $raw_query->whereNotNull('batch_no')->orderBy('id', 'DESC');
        if (Parent::showBranchPermission()) {
           // $raw_query->withoutGlobalScope(BranchScope::class);
        }
        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(40);
        }

        return response()->json($res);
    }
    function deliveryNoteQuery()
    {
        $filled = array_filter(request()->only([
            'supplier_id',
            'order_no',
            'delivery_no',
            'batch_no'
        ]));
        $raw_query = PurchaseOrderReceivable::with(['branch','purchase_order', 'purchase_order.branch', 'stock', 'supplier', 'user']);
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {
                if ($column == 'supplier_id') {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }
            }
        });
        if (request('from') != "" && request('to') != "") {

            $from = request('from');
            $to = request('to');
            $raw_query->whereBetween('received_date', [$from, $to]);
        }
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('delivery_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    });
                // ->orWhereHas(
                //     'user',
                //     function ($query) {
                //         $query->where('name', 'LIKE', '%' . request('search') . '%');
                //     }
                // );
            });
        })->when(request('sort_sum_sub_total'),function($q){
            $q->orderBy('sum_sub_total',request('sort_sum_sub_total'));
        })
        ->when(request('sort_sum_qty_ordered'),function($q){
            $q->orderBy('sum_qty_ordered',request('sort_sum_qty_ordered'));
        })
        ->when(request('sort_sum_qty_delivered'),function($q){
            $q->orderBy('sum_qty_delivered',request('sort_sum_qty_delivered'));
        })
         ->when(request('sort_discount_amount'),function($q){
            $q->orderBy('sum_discount_amount',request('sort_discount_amount'));
        })
        ->when(request('sort_received_date'),function($q){
            $q->orderBy('received_date',request('sort_received_date'));
        });
      
        $raw_query->when(request('sort_user'), function ($query) {
           
      $query->leftJoin('users', 'users.id', '=', 'purchase_order_receivables.user_id')
     
          ->orderBy('users.name', request('sort_user'));
});
        
        $raw_query->where(['status' => 'completed'])->orderBy('id', 'DESC');
        if (Parent::showBranchPermission()) {
           // $raw_query->withoutGlobalScope(BranchScope::class);
        }
        return $raw_query;
    }
    function fetchDeliveryTotals()
    {
        $raw_query = $this->deliveryNoteQuery();
        $raw_query->selectRaw('SUM(tax_amount) AS sum_tax_amount,SUM(qty_delivered) AS sum_qty_delivered,SUM(qty_ordered) AS sum_qty_ordered,SUM(sub_total) AS sum_sub_total');
        $res = $raw_query->first();
        return response()->json($res);
    }
    function fetchDeliveryNotes()
    {

        $raw_query = $this->deliveryNoteQuery();
        $raw_query->selectRaw('purchase_order_receivables.*,SUM(tax_amount) AS sum_tax_amount,SUM(qty_delivered) AS sum_qty_delivered,SUM(qty_ordered) AS sum_qty_ordered,SUM(sub_total) AS sum_sub_total,SUM(discount_amount) AS sum_discount_amount')->groupBy('delivery_no');
        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(40);
        }

        return response()->json($res);
    }
    function fetchDeliveryDetails()
    {
        $res =  PurchaseOrderReceivable::with(['stock.unit', 'department', 'user', 'purchase_order', 'purchase_order.branch', 'supplier','branch'])->where([

            'delivery_no' => request('delivery_no'),

        ])->get();
        return response()->json($res);
    }
    function completeRecevable(Request $request)
    {
        PurchaseOrderReceivable::where([
            'order_no' => $request->order_no,
            'supplier_id' => $request->supplier_id,
        ])->update(['status' => 'completed']);
    }
    function updateVat()
    {

        $res = PurchaseOrderReceivable::where('tax_amount', '=', 0)->get();
        foreach ($res as  $value) {
            $vat_rate = 16;
            $tax_amount = Parent::calculateTax($vat_rate, $value['sub_total']);

            PurchaseOrderReceivable::where(['id' => $value['id']])->update(['tax_rate' => $vat_rate, 'tax_amount' => $tax_amount]);
        }
    }
    function deliveryNo()
    {


        $latest = PurchaseOrderReceivable::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $delivery_no = $today . '-' . 'DN' . sprintf('%04d', $string);
        $isUnique = PurchaseOrderReceivable::where('delivery_no', $delivery_no)->first();
        if ($isUnique) {
            $delivery_no = $today . '-' . 'DN' . ($latest->id + 1);
        }
        return  $delivery_no;
    }

    function fetchDeliveryNo()
    {
        return response()->json($this->deliveryNo());
    }
    function fetchOrders()
    {

        $filled = array_filter(request()->only([
            'supplier_id',
            'order_no',

        ]));
        $raw_query =  PurchaseOrder::with(['branch', 'stock', 'supplier', 'user']);
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
        })->where(['branch_id' => Auth::user()->branch_id, 'internal_confirmation' => "confirmed"])
    ->groupBy(['order_no', 'supplier_id'])
    ->havingRaw('SUM(qty) > SUM(qty_delivered)')
    ->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(sub_total) AS sum_sub_total,SUM(qty) AS total_qty,
SUM(qty_delivered) AS total_qty_delivered');
        $res = $raw_query->paginate(20);
        return response()->json($res);
    }

    function completeDelivery(Request $request)
    {
        $validated = $request->validate([

            'department_id' => 'required',
        ]);
        ini_set('max_execution_time', 2400);

        $supplier_id = $request->supplier_id;
        $order_no = $request->order_no;

        DB::transaction(function () use ($request) {

            $update_data = PurchaseOrderReceivable::where(
                ['delivery_no' => $request->delivery_no]
            )
                ->get();
            $sum_sub_total = 0;
            $total_vat=0;
            print_r(json_encode($update_data));
            foreach ($update_data as $value) {

                Stock::where(['id' => $value['stock_id']])->update(['purchase_price' => $value['unit_price']]);
                PurchaseOrder::where('id', $value['purchase_order_id'])->increment('qty_delivered', $value['qty_delivered']);
                PurchaseOrder::where('id', $value['purchase_order_id'])->update(['supplier_confirmation' => 'confirmed']);

                Inventory::updateOrCreate(
                    ['stock_id' => $value['stock_id'], 'department_id' => $request->department_id, 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty +' . $value['qty_delivered'])]
                );
                $sum_sub_total += $value['sub_total'];
                $total_vat+=$value['tax_amount'];
            }

            PurchaseOrderReceivable::where([
                'order_no' =>  $request->order_no,
                'supplier_id' => $request->supplier_id,

            ])->update([
                'status' => 'completed',
                'department_id' => $request->department_id,
                'delivery_no' => $request->delivery_no,
                'cu_invoice_no' => $request->cu_invoice_no
            ]);

            PurchaseOrderReceivableWithBatch::where([
                'order_no' =>  $request->order_no,
                'supplier_id' => $request->supplier_id,

            ])->update([
                'status' => 'completed',
                'department_id' => $request->department_id,
                'delivery_no' => $request->delivery_no,
                'cu_invoice_no' => $request->cu_invoice_no
            ]);

            //Accounts
            $data = array(
                'supplier_id' => $request->supplier_id,
                'invoice_no' => $request->delivery_no,
                'po_no' => $request->order_no,
                'invoice_total' => $sum_sub_total,
                'invoice_date' => $update_data[0]['received_date'],
                'description' => "purchase delivery"
            );
            SupplierInvoice::create($data +
                ["unpaid_amount" => $sum_sub_total]
                + Parent::user_branch_id());
                $this->ledgerWHT($total_vat, $sum_sub_total, $request);
                $this->creditSupplierAccount($sum_sub_total, $request);
                $this->debitInputVATAccount($total_vat, $request);
                $this->debitInventoryAccount($total_vat, $sum_sub_total, $request);
            

        }, 5);

        $branch = Branch::where('id', Auth::user()->branch_id)->first();




        return true;
    }
    function receiveAllDeliveries(Request $request)
    {
        //receive bulk
        $validated = $request->validate([
            'delivery_no' => 'required',
            'department_id' => 'required',
            'received_date' => 'required',
        ]);
        DB::transaction(function () use ($request) {
            $res_data = PurchaseOrder::where(
                ['order_no' => $request->order_no]
            )
                ->get();
            $sum_sub_total = 0;
            $supplier_id = "";
            $order_no = "";
            $tax_total=0;
            foreach ($res_data as $value) {
                $supplier_id = $value['supplier_id'];
                $order_no = $value['order_no'];
                $qty_received = ($value['qty'] - $value['qty_delivered']);
                Stock::where(['id' => $value['stock_id']])->update(['purchase_price' => $value['unit_price']]);
                PurchaseOrder::where('id', $value['id'])->increment('qty_delivered', $qty_received);
                PurchaseOrder::where('id', $value['id'])->update(['supplier_confirmation' => 'confirmed']);

                Inventory::updateOrCreate(
                    ['stock_id' => $value['stock_id'], 'department_id' => $request->department_id, 'branch_id' => Parent::branch_id()],
                    ['qty' => DB::raw('qty +' . $qty_received)]
                );
                $sub_total = ($value['unit_price'] * $qty_received);
                $tax_amount = Parent::calculateTax($value['tax_rate'], $sub_total);
                $tax_total+=$tax_amount;
                $receivable_array = array(
                    'purchase_order_id' => $value['id'],
                    'supplier_id' => $value['supplier_id'],
                    'stock_id' => $value['stock_id'],
                    'qty_delivered' => $qty_received,
                    'qty_ordered' => $value['qty'],
                    'order_no' => $value['order_no'],
                    'delivery_no' => $request->delivery_no,
                    'received_date' => $request->received_date,
                    'sub_total' => $sub_total,
                    'unit_price' => $value['unit_price'],
                    'status' => 'completed',
                    'department_id' => $request->department_id,
                    'cu_invoice_no' => $request->cu_invoice_no,
                    'tax_rate' => $value['tax_rate'],
                    'tax_amount' => $tax_amount

                );
                PurchaseOrderReceivable::create($receivable_array  + [
                    'user_id' => Auth::user()->id,
                    'branch_id' => Auth::user()->branch_id,

                ]);
                $sum_sub_total += $sub_total;
            }
            PurchaseOrderReceivable::where([
                'order_no' =>  $request->order_no,
                'supplier_id' => $request->supplier_id,

            ])->update([
                'status' => 'completed',
                'department_id' => $request->department_id,
                'delivery_no' => $request->delivery_no,
                'cu_invoice_no' => $request->cu_invoice_no
            ]);
            //Accounts
            $data = array(
                'supplier_id' => $supplier_id,
                'invoice_no' => $request->delivery_no,
                'po_no' => $order_no,
                'invoice_total' => $sum_sub_total,
                'invoice_date' => $request->received_date,
                'description' => "purchase delivery"
            );
            SupplierInvoice::create($data +
                ["unpaid_amount" => $sum_sub_total]
                + Parent::user_branch_id());

        //ledger
        $this->ledgerWHT($tax_total, $sum_sub_total, $request);
        $this->creditSupplierAccount($sum_sub_total, $request);
        $this->debitInputVATAccount($tax_total, $request);
        $this->debitInventoryAccount($tax_total, $sum_sub_total, $request);
        //end ledger
        }, 5);
        return true;
    }

    function insertInputVATAccount($total_vat,$request){
        $accounts_data =  LedgerAccount::where(['account_type' => "input VAT"])->first();
        if($total_vat==0){
            return;
        }
        if(!$accounts_data){
            return ;
        }
        $supplier=Supplier::where('id',$request->supplier_id)->first();
    
        $insert_data = array(
            'other_account_name' => $supplier->company_name,
            'account_id' => $accounts_data->id,
            'account_type' => 'PURCHASES VAT',
            'entry_code' =>$request->order_no ,
            'ref' =>$request->order_no ,
            'details' => "Input VAT Purchase ( GRN)",
            'debit_amount' => $total_vat,
            'description' => " VAT",
            'entry_date' =>$request->received_date,
    
        );
       
        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function insertInputVATAccountComplete($total_vat,$request,$insert_date){
        $accounts_data =  LedgerAccount::where(['account_type' => "input VAT"])->first();
        if($total_vat==0){
            return;
        }
        if(!$accounts_data){
            return ;
        }
        $supplier=Supplier::where('id',$request->supplier_id)->first();
    
        $insert_data = array(
            'other_account_name' => $supplier->company_name,
            'account_id' => $accounts_data->id,
            'account_type' => 'PURCHASES VAT',
            'entry_code' =>$request->order_no ,
            'ref' =>$request->order_no ,
            'details' => "Input VAT Purchase ( GRN)",
            'debit_amount' => $total_vat,
            'description' => " VAT",
            'entry_date' =>$insert_date,
    
        );
       
        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function downloadDN(Request $request)
    {
        $validated = $request->validate([

            'department_id' => 'required',
        ]);
        ini_set('max_execution_time', 2400);

        $supplier_id = $request->supplier_id;
        $order_no = $request->order_no;


        $branch = Branch::where('id', Auth::user()->branch_id)->first();
        $res =  PurchaseOrderReceivable::with(['stock', 'department', 'user.department', 'purchase_order', 'purchase_order.branch', 'supplier'])->where([
            'order_no' => $order_no,
            'supplier_id' => $supplier_id,
            'delivery_no' => $request->delivery_no,
            "branch_id" => Auth::user()->branch_id
        ])->get();



        // return response()->json($data);
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "supplier_delivery_note" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res, 'branch' => $branch];
        $pdf = PDF::loadView('pdf.delivery', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }


    function downloadDN2(Request $request)
    {
        ini_set('max_execution_time', 2400);

        $supplier_id = $request->supplier_id;
        $order_no = $request->order_no;

        DB::transaction(function () use ($request) {

            $update_data = PurchaseOrderReceivable::where(
                ['status' => "pending", 'delivery_no' => $request->delivery_no]
            )
                ->get();

            foreach ($update_data as $value) {
                Stock::where(['id' => $value['stock_id']])->increment('qty', $value['qty_delivered']);
                PurchaseOrder::where('id', $value['purchase_order_id'])->increment('qty_delivered', $value['qty_delivered']);
                PurchaseOrder::where('id', $value['purchase_order_id'])->update(['supplier_confirmation' => 'confirmed']);
            }

            PurchaseOrderReceivable::where([
                'order_no' =>  $request->order_no,
                'supplier_id' => $request->supplier_id,
                'delivery_no' => $request->delivery_no
            ])->update(['status' => 'completed',]);
        }, 5);
        $branch = Branch::where('id', Auth::user()->branch_id)->first();
        $res =  PurchaseOrderReceivable::with(['stock', 'user.department', 'purchase_order', 'purchase_order.branch', 'supplier'])->where([
            'order_no' => $order_no,
            'supplier_id' => $supplier_id,
            'delivery_no' => $request->delivery_no,
            "branch_id" => Auth::user()->branch_id
        ])->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $res[0]->delinery_no . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res, 'branch' => $branch];
        $pdf = PDF::loadView('pdf.delivery', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }


    function deleteDeliveryItem(Request $request)
    {
        DB::transaction(function () use ($request) {
            $res = PurchaseOrderReceivable::where('id', $request->id)->first();

            PurchaseOrderReceivableWithBatch::where(
                ['stock_id' => $res->stock_id, 'batch_no' => $res->batch_no]
            )->update([
                'qty_delivered' => DB::raw('qty_delivered -' . $res->qty_delivered)
            ]);

            PurchaseOrderReceivable::where(['id' => $request->id])->delete();
            Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->decrement('qty', $request->qty_delivered);
       
            $accounts_data =  LedgerAccount::where(['account_type' => "input VAT"])->first();
            if($res->tax_amount>0){
                if($accounts_data){

                    $supplier=Supplier::where('id',$res->supplier_id)->first();
                    $insert_data = array(
                        'other_account_name' => $supplier->company_name,
                        'account_id' => $accounts_data->id,
                        'account_type' => 'PURCHASES VAT DELETED',
                        'entry_code' =>$request->delivery_no ,
                        'ref' =>$request->delivery_no ,
                        'details' => "GRN deleted ( GRN)",
                        'credit_amount' => $res->tax_amount,
                        'description' => " VAT REVERSAL",
                        'entry_date' =>Parent::currentDate(),
                
                    );
                   
                    GeneralLedger::create($insert_data + Parent::user_branch_id());
                }
               
            }
          
               
            

       
       
        }, 5);
        return true;
    }

    function submitUpdateBatchQty(Request $request)
    {
        Validator::validate($request->all(), [
            'qty' => 'required|numeric|gte:0',


        ]);
        PurchaseOrderReceivableWithBatch::where('id', $request->id)->update(['qty_delivered' => $request->qty]);
        return true;
    }
    function submitUpdateBatchSoldQty(Request $request)
    {
        Validator::validate($request->all(), [
            'sold_qty' => 'required|numeric|gte:0',
            'id' => 'required'

        ]);
        PurchaseOrderReceivableWithBatch::where('id', $request->id)->update(['qty_sold' => $request->sold_qty]);
        return true;
    }

    function addBatchItems(Request $request)
    {
        Validator::validate($request->all(), [
            'qty_delivered' => 'required|numeric|gte:0',
            'expiry_date' => 'required',
            'id' => 'required',
            'batch_no' => "required",
            'supplier_id'=>"required",
            'department_id'=>"required"
        ]);
        // PurchaseOrderReceivableWithBatch::where(['stock_id' => $request->id, 'batch_no' => $request->batch_no])->delete();
        // PurchaseOrderReceivableWithBatch::create([
        //     'stock_id' => $request->id,
        //     'batch_no' => $request->batch_no,
        //     'qty_delivered' => $request->qty_delivered,
        //     'expiry_date' => $request->expiry_date,
        //     'received_date'=>Parent::currentDate(),
        //     'purchase_order_id'=>1,
        //     'department_id'=>$request->department_id,
        //     'supplier_id'=>$request->supplier_id,
        //     ''
        // ]+Parent::user_branch_id());

        PurchaseOrderReceivableWithBatch::updateOrCreate(['stock_id' => $request->id, 'batch_no' => $request->batch_no],[
            'stock_id' => $request->id,
            'batch_no' => $request->batch_no,
            'qty_delivered' => $request->qty_delivered,
            'expiry_date' => $request->expiry_date,
            'received_date'=>Parent::currentDate(),
            'purchase_order_id'=>1,
            'department_id'=>$request->department_id,
            'supplier_id'=>$request->supplier_id,
            ''
        ]+Parent::user_branch_id());
        return true;
    }

    function getAveragePurchasePrice(){
        $stock_id=request('id');
     $res=   PurchaseOrderReceivable::where('stock_id',$stock_id)->avg('unit_price');
     return response()->json(["res"=>$res]);
    }
    function ledgerWHT($tax_total, $sum_sub_total, $request)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "WHT"])->first();
        $supplier = Supplier::where('id', $request->supplier_id)->first();
        $wht_amount = $tax_total * 0.02;
        if ($wht_amount > 0) {
            if ($supplier->deduct_wht == 'deduct') {

                if ($accounts_data) {



                    $insert_data = array(
                        'other_account_name' => $supplier->company_name,
                        'account_id' => $accounts_data->id,
                        'account_type' => "WHT",
                        'entry_code' => $request->order_no,
                        'ref' => $request->order_no,
                        'details' => "Withholding Tax",
                        'credit_amount' => $wht_amount,
                        'description' => "Withholding Tax",
                        'entry_date' => Parent::currentDate(),

                    );

                    GeneralLedger::create($insert_data + Parent::user_branch_id());

                    $credit_ledger = array(

                        'supplier_id' => $request->supplier_id,
                        'ref' => $request->order_no,
                        'type' => 'WHT',
                        'entry_date' => Parent::currentDate(),
                        'description' => "Withholding Tax",
                        'debit' => $wht_amount,
                        'amount_due' => 0,
                        'balance' => 0
                    );

                    CreditLedger::create($credit_ledger + Parent::user_branch_id());
                }
            }
        }
    }
    function creditSupplierAccount($sum_sub_total, $request)
    {
        $data2 = array(

            'supplier_id' => $request->supplier_id,
            'ref' => $request->order_no,
            'type' => 'IN',
            'entry_date' => Parent::currentDate(),
            'description' => "purchase delivery",
            'credit' => $sum_sub_total,
            'amount_due' => 0,
            'balance' => 0
        );
        CreditLedger::create($data2 + Parent::user_branch_id());
    }
    function debitInputVATAccount($total_vat, $request)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "input VAT"])->first();
        if ($total_vat == 0) {
            return;
        }
        if (!$accounts_data) {
            return;
        }
        $supplier = Supplier::where('id', $request->supplier_id)->first();

        $insert_data = array(
            'other_account_name' => $supplier->company_name,
            'account_id' => $accounts_data->id,
            'account_type' => 'VAT Input',
            'entry_code' => $request->order_no,
            'ref' => $request->order_no,
            'details' => "Input VAT Purchase ( GRN)",
            'debit_amount' => $total_vat,
            'description' => " VAT",
            'entry_date' => Parent::currentDate(),

        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function debitInventoryAccount($total_vat, $total, $request)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "Inventory"])->first();

        if (!$accounts_data) {
            return;
        }
        $value = $total - $total_vat;
        $supplier = Supplier::where('id', $request->supplier_id)->first();

        $insert_data = array(
            'other_account_name' => $supplier->company_name,
            'account_id' => $accounts_data->id,
            'account_type' => 'GRN. Inventory',
            'entry_code' => $request->order_no,
            'ref' => $request->order_no,
            'details' => " Purchase ( GRN)",
            'debit_amount' => $value,
            'description' => "Purchase Stock Inventory ",
            'entry_date' => Parent::currentDate(),

        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }


    
    public function purchaseAging(Request $request)
    {
        $perPage = (int) $request->input('per_page', 25);
        $today   = Carbon::today()->toDateString();

        // Optional filters
        $from = $request->input('from');
        $to   = $request->input('to');
        $stockId = $request->input('stock_id');
        $supplierId = $request->input('supplier_id');

        $q = PurchaseOrderReceivable::query()
            ->with(['stock:id,product_name']) // eager load stock name
            ->when($from && $to, fn($qq) => $qq->whereBetween('received_date', [$from, $to]))
            ->when($stockId, fn($qq) => $qq->where('stock_id', $stockId))
            ->when($supplierId, fn($qq) => $qq->where('supplier_id', $supplierId));

        $todaySql = DB::getPdo()->quote($today);

        $report = $q->select([
                'stock_id',
                DB::raw("MAX(received_date) AS last_received_date"),
                DB::raw("SUM(sub_total)      AS total_amount"),
                DB::raw("SUM(CASE 
                            WHEN DATEDIFF($todaySql, received_date) BETWEEN 0  AND 29 THEN sub_total 
                            ELSE 0 END) AS bucket_current"),
                DB::raw("SUM(CASE 
                            WHEN DATEDIFF($todaySql, received_date) BETWEEN 30 AND 59 THEN sub_total 
                            ELSE 0 END) AS bucket_30"),
                DB::raw("SUM(CASE 
                            WHEN DATEDIFF($todaySql, received_date) BETWEEN 60 AND 89 THEN sub_total 
                            ELSE 0 END) AS bucket_60"),
                               DB::raw("SUM(CASE 
                            WHEN DATEDIFF($todaySql, received_date) BETWEEN 90 AND 119 THEN sub_total 
                            ELSE 0 END) AS bucket_90"),
                DB::raw("SUM(CASE 
                            WHEN DATEDIFF($todaySql, received_date) >= 120 THEN sub_total 
                            ELSE 0 END) AS bucket_120_plus"),
            ])
            ->groupBy('stock_id')
            ->orderByDesc(DB::raw('MAX(received_date)'))
            ->paginate($perPage);

        // Add stock name to each result
        $report->getCollection()->transform(function ($item) {
            $item->stock_name = optional($item->stock)->product_name;
            return $item;
        });

        return response()->json($report);
    }
}
