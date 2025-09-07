<?php

namespace App\Http\Controllers\Requisition;

use Carbon\Carbon;
use App\Branch\Branch;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Requistion\Requistion;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Requisition\RequistionRequest;
use Barryvdh\DomPDF\Facade as PDF;

class RequisitionController extends BaseController
{

    function create(RequistionRequest $request)
    {

        if ($this->containsItem($request)) {
            Requistion::where([
                'req_id' => $request->req_id,
                'user_id' => Auth::user()->id,
                "branch_id" => Auth::user()->branch_id, 'stock_id' => $request->stock_id
            ])
                ->update(array('qty' => DB::raw('qty + ' . $request->qty)));
        } else {
            Requistion::create($request->validated() + [
                'branch_id' => Auth::user()->branch_id,
                'user_id' => Auth::user()->id,
            ]);
        }
        $res = Requistion::with(['user', 'department', 'stock',])->where([
            'branch_id' => Auth::user()->branch_id,
            'approved' => '0',
            'status' => "progress",

            'user_id' => Auth::user()->id,
        ])->get();
        return response()->json($res);
    }

    function containsItem($request)
    {
        $res = Requistion::where([
            'approved' => '0',
            'user_id' => Auth::user()->id,
            'stock_id' => $request->stock_id,
            "branch_id" => Auth::user()->branch_id,
            'status' => "progress",

        ])->get();
        return  $res->isEmpty() ? false : true;
    }

    function fetch()
    {

        $raw_query = Requistion::with(['user', 'department', 'stock',])->where([
            'branch_id' => Parent::branch_id(),

            'status' => "complete",

        ]);
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('req_id', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_due', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('priority', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('product_type', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('details', 'LIKE', '%' . request('search') . '%')


                    ->orWhereHas('department', function ($query) {
                        $query->where('department', 'LIKE', '%' . request('search') . '%');
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
            $raw_query->where('user_id', Auth::user()->id);
        }
        $raw_query->groupBy('req_id')->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(qty) as sum_qty,SUM(qty*unit_price) as sum_sub_total');
        $res = $raw_query->paginate(20);
        return response()->json($res);
    }
    function fetchReqNo()
    {
        return response()->json($this->requisitionNo());
    }

    function deleteItem(Request $request)
    {
        $id = $request->route('id');
        Requistion::find($id)->delete();
        return true;
    }

    function delete(Request $request)
    {
        $req_id = $request->route('req_id');

        Requistion::where('req_id', $req_id)->delete();
        return true;
    }
    function approve(Request $request)
    {
        $req_id = $request->req_id;

        Requistion::where('req_id', $req_id)->update(["approved" => '1']);
        return true;
    }
    function requisitionNo()
    {
        $latest = Requistion::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $req_no = $today . '-' . 'RQ' . sprintf('%04d', $string);
        $isUnique = Requistion::where('req_id', $req_no)->first();
        if ($isUnique) {
            $req_no = $today . '-' . 'RQ' . ($latest->id + 1);
        }
        return  $req_no;
    }

    function fetchPendngReq()
    {
        $res = Requistion::with(['user', 'department', 'stock',])->where([
            'branch_id' => Auth::user()->branch_id,
            'approved' => '0',

            'status' => "progress",
            'user_id' => Auth::user()->id,
        ])->get();
        return response()->json($res);
    }

    function fetchByReqId()
    {
        $res = Requistion::with(['user', 'department', 'stock',])->where([
            'branch_id' => Auth::user()->branch_id,
            'req_id' => request('req_id'),


        ])->get();
        return response()->json($res);
    }


    function updateReqData(RequistionRequest $request)
    {
        Requistion::where([
            'id' => $request->id
        ])->update($request->validated());
        $res = Requistion::with(['user', 'department', 'stock',])->where([
            'branch_id' => Auth::user()->branch_id,
            'req_id' => $request->req_id,

            'user_id' => Auth::user()->id,
        ])->get();
        return response()->json($res);
    }

    public function checkForPermission()
    {

        $user = Auth::user();

        $permission = json_decode($user->role->permission);

        $hasPermission = false;
        if (!$permission) {

            return  $hasPermission;
        }

        foreach ($permission as $p) {

            foreach ($p->children as $c) {

                if ($c->name == request('currentRoute')) {

                    if ($c->fetch) {

                        $hasPermission = true;
                    } else {
                    }
                }
            }
        }
        return $hasPermission;
    }

    function fetchUserRequests()
    {
        $user_id = request('user_id');
        $filled = array_filter(request()->only([
            'req_id',
            'product_name',

        ]));
        $raw_query =   Requistion::with(['user', 'department', 'stock'])->where(
            [
                'approved' => '1',
                'user_id' => $user_id,
                'branch_id' => Parent::branch_id()
            ]
        );
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {


            foreach ($filled as $column => $value) {


                if ($column == 'req_id') {
                    $query->where($column, $value);
                } else if ($column == 'product_name') {
                    $query->whereHas('stock', function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', '%' . $value . '%');
                    });
                }
            }
        });
        $res = $raw_query->whereRaw('qty > qty_issued')->get();
        return response()->json($res);
    }

    function downloadReq(Request $request)
    {
        ini_set('max_execution_time', 2400);
        Requistion::where([
            'req_id' => $request->req_id,
            'user_id' => Auth::user()->id,

        ])->update(['status' => 'complete']);

        $res = Requistion::with(['user', 'department', 'stock',])->where([
            'branch_id' => Auth::user()->branch_id,

            'req_id' => $request->req_id,
            'status' => "complete",

        ])->get();
        $branch = Branch::where('id', Auth::user()->branch_id)->first();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $res[0]->req_id . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }

        $data = ["data" => $res, 'branch' => $branch];

        $pdf = PDF::loadView('pdf.requisition', $data); //compact('data') 
        $pdf->save($path);
        return response()->download($path);
    }
}
