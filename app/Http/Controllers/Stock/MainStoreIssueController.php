<?php

namespace App\Http\Controllers\Stock;

use Carbon\Carbon;
use App\Stock\Stock;
use Illuminate\Http\Request;
use App\Stock\InitialStoreIssue;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\ValidateIssueStoreQty;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateItemIssueDepartment;
use App\Rules\ValidateCompleteIssueStoreQty;
use App\Http\Requests\Stock\MainStoreIssueRequest;
use App\Rules\CheckMainStoreStockQtyAvailability;
use phpDocumentor\Reflection\Types\Parent_;

class MainStoreIssueController extends BaseController
{
    public function create(MainStoreIssueRequest $request)
        {
            $this->validate($request, ['stock_id' => new CheckMainStoreStockQtyAvailability($request->qty_issued)]);
            $prev_store_qty = Stock::where('id', $request->stock_id)->first()->main_store_qty;
                InitialStoreIssue::updateOrCreate(
                    ['issue_no' => $request->issue_no, 'stock_id' => $request->stock_id],
                [
                    'date_issued' => Parent::currentDate(),
                    'opening_stock' => $prev_store_qty,  
                ] + $request->validated() + Parent::user_branch_id());
          
            return response()->json($this->fetchByIssueNo($request->issue_no));
        
    }

    function fetchByIssueNo($issue_no)
    {

        $rs = InitialStoreIssue::with(['stock', 'unit'])->where(['issue_no' => $issue_no])->get();
        return $rs;
    }

    function fetchIssueNo()
    {
        $rs = InitialStoreIssue::with(['stock', 'unit'])->where(['status' => "progress"] + Parent::user_branch_id())->get();
        if ($rs->isEmpty()) {
            return response()->json(["issue_no" => $this->issueNumber(), "issue_data" => []]);
        } else {
            return response()->json(["issue_no" => '', "issue_data" => $rs]);
        }
        return response()->json($this->issueNumber());
    }

    function destroy(Request $request)
    {
        InitialStoreIssue::where('id', $request->route('id'))->delete();
        return true;
    }
    
    function issueNumber()
    {

        $latest = InitialStoreIssue::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $req_no = $today . '-' . 'MISN' . sprintf('%04d', $string);
        $isUnique = InitialStoreIssue::where('issue_no', $req_no)->first();
        if ($isUnique) {
            $req_no = $today . '-' . 'MISN' . ($latest->id + 1);
        }
        return  $req_no;
    }

    public function completeIssue(Request $request)
    {
        //print_r($request->issue_data);

        $this->validate($request, [
         
            'issue_no' => "required",
            'issue_data' => new ValidateCompleteIssueStoreQty()

        ]);
         // $this->validate($request, ['issue_data' => new ValidateCompleteIssueStoreQty()]);

        ini_set('max_execution_time', 2400);
        DB::transaction(function () use ($request) {
            InitialStoreIssue::where("issue_no", $request->issue_no)->forceDelete();
            foreach ($request->issue_data as $value) {
              
             
                  InitialStoreIssue::create(
                    [
                        'stock_id' => $value['stock_id'],
                        'branch_id' => $value['branch_id'],
                        'unit_id' => $value['unit_id'],
                        'department_id' => $request->department_id,
                        'issue_no' => $value['issue_no'],
                        'user_id' => $value['user_id'],
                        'qty_issued' => $value['qty_issued'],
                       
                       
                        'unit' => $value['unit'],
                        'date_issued' => Parent::currentDate(),
                        'purchase_price' => $value['purchase_price'],
                        'row_total' => $value['row_total'] * $value['qty_issued'],
                       
                        'status' => "complete"

                    ]
                );


                Stock::where(['id' => $value['stock_id']])->decrement('main_store_qty', $value['qty_issued']);

               
               
            }
        }, 5);



        $issue_no = $request->issue_no;
        $res =  InitialStoreIssue::with(['stock', 'user.department', 'branch',])->where([
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
        $pdf = PDF::loadView('pdf.main_store_issuenote', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);

        //return response()->json($this->issueNumber());
    }

    function fetchIssueData()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = InitialStoreIssue::with(['stock', 'user', 'branch',]);
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
                    );
            });
        })->where(Parent::branch_array());

        $raw_query->groupBy('issue_no')->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(qty_issued) AS sum_qty_issued,SUM(qty_received) AS sum_qty_received,SUM(row_total) AS sum_row_total');

        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(20);
        }

        return response()->json($res);
    }

    function downloadIssueNote(Request $request)
    {

        $issue_no = $request->route('issue_no');
        $res =  InitialStoreIssue::with(['stock', 'user.department',  'branch',])->where([
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
        $pdf = PDF::loadView('pdf.main_store_issuenote', $data); //compact('data') 
        $pdf->save($path);


        return response()->download($path);
    }
    function fetchIssueDetailed()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = InitialStoreIssue::with(['stock', 'user',  'branch',]);
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
        })->where(Parent::branch_array()+['status'=>'complete']);
   if(request('isReceived')=="not_received"){
       $raw_query->where('isReceived','0');
   }
        $raw_query->orderBy('id', 'DESC');


        $res = $raw_query->get();
        if (request('page') > 0) {
            $res = $raw_query->paginate(20);
        }

        return response()->json($res);
    }
    function downloadDetailsPdf()
    {
        ini_set('max_execution_time', 600);

        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = InitialStoreIssue::with(['stock', 'user',  'branch',]);
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

    function receiveStock(Request $request){
        DB::transaction(function () use ($request) {

            InitialStoreIssue::where(['id'=>$request->id
            ])->update(['isReceived'=>'1',
            'date_received'=>Parent::currentDate(),
            'qty_received' => DB::raw('qty_received + '.$request->qty_issued)]);
    
            Stock::where(['id'=>$request->stock_id])->increment('store_qty',$request->qty_issued);
        },5);
    }

    function receiveBulkStock(Request $request){
        DB::transaction(function () use ($request) {

        $issue_data=$request->issue_data;
       
        foreach ($issue_data as  $value) {
          
           InitialStoreIssue::where(['id'=>$value['id'],
           ])->update(['isReceived'=>'1',
           'date_received'=>Parent::currentDate(),
           'qty_received' => DB::raw('qty_received + '.$value['qty_issued'])]);
   
           Stock::where(['id'=>$value['stock_id']])->increment('store_qty',$value['qty_issued']);
        }
    },5);
    }
}
