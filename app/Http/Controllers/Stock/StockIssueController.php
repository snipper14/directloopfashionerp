<?php

namespace App\Http\Controllers\Stock;

use Carbon\Carbon;
use App\Stock\Stock;
use App\Stock\StockMovt;
use App\Stock\IssueStock;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\ValidateIssueStoreQty;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateCompleteIssueStoreQty;
use App\Http\Requests\Stock\StockIssueRequest;
use App\Inventory\Inventory;
use App\Rules\ValidateCompleteIssueInternalStoreQty;
use App\Rules\ValidateItemIssueDepartment;

class StockIssueController extends BaseController
{
    public function create(StockIssueRequest $request)
    {
        $this->validate($request, ['qty_issued' => new ValidateIssueStoreQty($request->stock_id, $request->source_department_id)]);
        //  $this->validate($request, ['product_department_id' => new ValidateItemIssueDepartment($request->department_id)]);

        DB::transaction(function () use ($request) {
            $prev_store_qty = Stock::where('id', $request->stock_id)->first()->store_qty;
            IssueStock::updateOrCreate(['issue_no' => $request->issue_no, 'stock_id' => $request->stock_id], [
                'date_issued' => Parent::currentDate(),
                'opening_stock' => $prev_store_qty
            ] + $request->validated() + Parent::user_branch_id());
            Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->source_department_id])->decrement('qty', $request->qty_issued);
            Inventory::updateOrCreate(['stock_id' => $request->stock_id, 'department_id' => $request->department_id], [
                'qty' => DB::raw('qty +' . $request->qty_issued)
            ] + $request->validated() + Parent::user_branch_id());
        }, 5);
        return response()->json($this->fetchByIssueNo($request->issue_no));
    }
    function isUnique($request)
    {
        $res = IssueStock::where(['issue_no' => $request->issue_no, 'stock_id' => $request->stock_id])->get();
        if ($res->isEmpty()) {
            return true;
        } else {
            return false;
        }
    }
    function fetchByIssueNo($issue_no)
    {

        $rs = IssueStock::with(['stock', 'unit'])->where(['issue_no' => $issue_no])->get();
        return $rs;
    }
    function fetchIssueNo()
    {
        $rs = IssueStock::with(['stock', 'unit'])->where(['status' => "progress"] + Parent::user_branch_id())->get();
        if ($rs->isEmpty()) {
            return response()->json(["issue_no" => $this->issueNumber(), "issue_data" => []]);
        } else {
            return response()->json(["issue_no" => '', "issue_data" => $rs]);
        }
        return response()->json($this->issueNumber());
    }

    function issueNumber()
    {

        $latest = IssueStock::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $req_no = $today . '-' . 'ISN' . sprintf('%04d', $string);
        $isUnique = IssueStock::where('issue_no', $req_no)->first();
        if ($isUnique) {
            $req_no = $today . '-' . 'ISN' . ($latest->id + 1);
        }
        return  $req_no;
    }
    function destroy(Request $request)
    {
        DB::transaction(function () use ($request) {
            IssueStock::where('id', $request->id)->delete();
            Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->department_id])->decrement('qty', $request->qty_issued);
            Inventory::where(['stock_id' => $request->stock_id, 'department_id' => $request->source_department_id])->increment('qty', $request->qty_issued);
        }, 5);
        return true;
    }
public function updateIssue(){
    $res=IssueStock::get();
    foreach ($res as $value) {
       $row_total= $value['purchase_price'] * $value['qty_issued'];
       IssueStock::where('id',$value['id'])->update(['row_total'=>$row_total]);
    }  
}
    public function completeIssue(Request $request)
    {
        //print_r($request->issue_data);

        $this->validate($request, [
            'source_department_id' => "required",
            'department_id' => "required",
            'issued_staff_id' => 'required',
            'issue_no' => "required",


        ]);
        //   $this->validate($request, ['issue_data' => new ValidateCompleteIssueInternalStoreQty()]);

        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {
            IssueStock::where("issue_no", $request->issue_no)->forceDelete();
            foreach ($request->issue_data as $value) {

                $final_qty = $value['qty_issued'] * $value['mapping_value'];
                $prev_store_qty = Stock::where('id', $value['stock_id'])->first()->store_qty;
                $prev_opening_dept_stock = Stock::where('id', $value['stock_id'])->first()->qty;
                IssueStock::create(
                    [
                        'stock_id' => $value['stock_id'],
                        'branch_id' => $value['branch_id'],
                        'unit_id' => $value['unit_id'],
                        'department_id' => $request->department_id,
                        'source_department_id' => $request->source_department_id,
                        'issue_no' => $value['issue_no'],
                        'internal_issue_no' => $request->internal_issue_no,
                        'user_id' => $value['user_id'],
                        'qty_issued' => $value['qty_issued'],
                        'mapping_value' => $value['mapping_value'],
                        'final_qty' => $final_qty,
                        'unit' => $value['unit'],
                        'date_issued' => Parent::currentDate(),
                        'purchase_price' => $value['purchase_price'],
                        'row_total' => $value['purchase_price'] * $value['qty_issued'],
                        'opening_stock' => $prev_store_qty,
                        'status' => "complete",
                        'issued_staff_id' => $request->issued_staff_id
                    ]
                );
            }
        }, 5);



        $issue_no = $request->issue_no;
        $res =  IssueStock::with(['stock', 'user.department', 'department', 'branch', 'issued_staff'])->where([
            'issue_no' => $issue_no,

        ])->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $issue_no . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res];


        return response()->json($this->issueNumber());
    }
    public function kitchenBatchIssue(Request $request)
    {

        $this->validate($request, [
            'department_id' => "required",
            'issued_staff_id' => 'required',



        ]);


        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {
            $issue_no = $this->issueNumber();
            foreach ($request->issue_data as $value) {

                $final_qty = $value['qty'];
                $prev_store_qty = Stock::where('id', $value['stock_id'])->first()->store_qty;
                $stock_data = Stock::with(['unit'])->where('id', $value['stock_id'])->first();
                if ($final_qty > 0) {
                    IssueStock::create(
                        [
                            'stock_id' => $value['stock_id'],
                            'branch_id' => Parent::branch_id(),
                            'unit_id' => $stock_data->unit_id,
                            'department_id' => $request->department_id,
                            'issue_no' => $issue_no,
                            'user_id' => Parent::user_id(),
                            'qty_issued' => $final_qty,
                            'mapping_value' => 1,
                            'final_qty' => $final_qty,
                            'unit' => $stock_data->unit->name,
                            'date_issued' => Parent::currentDate(),
                            'purchase_price' => $stock_data->production_cost,
                            'row_total' => $stock_data->production_cost * $final_qty,
                            'opening_stock' => $prev_store_qty,
                            'status' => "complete",
                            'issued_staff_id' => $request->issued_staff_id
                        ]
                    );


                    Stock::where(['id' => $value['stock_id']])->increment('qty', $final_qty);
                    //PERFORM PRODUCT DEDUCTION FORMULA


                }
            }
        }, 5);

        return true;
    }
    function fetchIssueData()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = IssueStock::with(['stock', 'user', 'department', 'branch', 'issued_staff', 'source_department']);
        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('date_issued', [$from, $to]);
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('issue_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('internal_issue_no', 'LIKE', '%' . request('search') . '%')

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
                    )
                    ->orWhereHas(
                        'department',
                        function ($query) {
                            $query->where('department', 'LIKE', '%' . request('search') . '%');
                        }
                    )
                    ->orWhereHas(
                        'source_department',
                        function ($query) {
                            $query->where('department', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->where(Parent::branch_array());

        $raw_query->groupBy('issue_no')->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(qty_issued) AS sum_qty_issued,SUM(row_total) AS sum_row_total');

        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(20);
        }

        return response()->json($res);
    }
    function fetchIssueDetailed()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = IssueStock::with(['stock', 'user', 'department', 'source_department', 'branch', 'issued_staff']);
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
                    )
                    ->orWhereHas(
                        'stock',
                        function ($query) {
                            $query->where('product_name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        });

        $raw_query->orderBy('id', 'DESC');

        if (request('report_type') == 'group') {
            $raw_query->selectRaw("*,SUM(qty_issued) AS sum_qty_issued,SUM(final_qty) AS sum_final_qty")->groupBy('stock_id');
        }
        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(20);
        }

        return response()->json($res);
    }
    function fetchDispatchDetails()
    {
       
        $raw_query = IssueStock::with(['stock', 'user', 'department', 'source_department', 'branch', 'issued_staff']);
      
        

        $raw_query->where(['issue_no'=>request('issue_no')])->orderBy('id', 'DESC');

      
        $res = $raw_query->get();
      
                

        return response()->json($res);
    }
    function downloadIssueNote(Request $request)
    {

        $issue_no = $request->route('issue_no');
        $res =  IssueStock::with(['stock', 'user.department', 'department', 'branch', 'issued_staff'])->where([
            'issue_no' => $issue_no,

        ])->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $issue_no . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res];
        $pdf = PDF::loadView('pdf.internal_issuenote', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }

    function downloadDetailsPdf()
    {
        ini_set('max_execution_time', 600);

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = IssueStock::with(['stock', 'user', 'department', 'branch', 'issued_staff']);
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
                        'stock',
                        function ($query) {
                            $query->where('product_name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->where(Parent::branch_array());

        $res = $raw_query->orderBy('id', 'DESC')->get();

        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "issue_details" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ["data" => $res];
        $pdf = PDF::loadView('pdf.issue_detailed_report', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }
}
