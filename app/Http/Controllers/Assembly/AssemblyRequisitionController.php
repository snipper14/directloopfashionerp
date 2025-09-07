<?php

namespace App\Http\Controllers\Assembly;

use App\Branch\Branch;
use Illuminate\Http\Request;
use App\Ingredient\Ingredient;
use App\Requistion\Requistion;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use phpDocumentor\Reflection\Types\Parent_;
use App\Assembly\AssemblyProductRequisition;
use App\Http\Requests\Requisition\RequistionRequest;
use App\Http\Requests\Assembly\AssemblyRequisitionRequest;
use Barryvdh\DomPDF\Facade as PDF;

class AssemblyRequisitionController extends BaseController
{
    public function create(AssemblyRequisitionRequest $request)
    {
        DB::transaction(function () use ($request) {
            if ($this->containsItem($request)) {

                $this->updateRawMaterialForAssembly($request);
            } else {
                $this->createRawMaterialForAssembly($request);
            }
        }, 5);
        $res = Ingredient::where('stock_id', $request->stock_id)->get();
        if (!$res->isEmpty()) {
            $res = AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where([

                'approved' => '0',
                'status' => "progress"
            ] + Parent::user_branch_id())->get();
            return response()->json($res);
        } else {
            return response()->json([]);
        }
    }
    function containsItem($request)
    {
        $res = AssemblyProductRequisition::where([
            'approved' => '0',

            'stock_id' => $request->stock_id,
            'status' => "progress"
        ] + Parent::user_branch_id())->get();
        return  $res->isEmpty() ? false : true;
    }
    function createRawMaterialForAssembly($request)
    {
        $res = Ingredient::where('stock_id', $request->stock_id)->get();
        if (!$res->isEmpty()) {

            foreach ($res as $value) {
                # code...

                $insert_data = [

                    'ingredient_id' => $value['ingredient_id'],
                    'ingredient_price' => $value['ingredient']['cost_price'],
                    'ingredient_qty' => ($request->qty * $value['qty']),
                    'sub_total' => ($request->qty * $value['qty'] * $value['ingredient']['cost_price']),


                ] + Parent::user_branch_id();
                AssemblyProductRequisition::create($insert_data + $request->validated());
            }
        }
    }

    function updateRawMaterialForAssembly($request)
    {
        $res = Ingredient::where('stock_id', $request->stock_id)->get();
        if (!$res->isEmpty()) {

            foreach ($res as $value) {
                # code...

                $update_data = [
                    'qty' => DB::raw('qty + ' . $request->qty),
                    'ingredient_qty' => DB::raw('ingredient_qty + ' . ($request->qty * $value['qty'])),
                    'sub_total' => DB::raw('sub_total +' . ($request->qty * $value['qty'] * $value['ingredient']['cost_price'])),
                ];


                AssemblyProductRequisition::where([
                    'req_id' => $request->req_id,
                    'stock_id' => $request->stock_id,
                    'ingredient_id' => $value['ingredient_id'],
                ] + Parent::user_branch_id())
                    ->update($update_data);
            }
        }
    }
    function fetchPendngReq()
    {
        $res = AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where([
            'approved' => '0',

            'status' => "progress"
        ] + Parent::user_branch_id())->get();
        return response()->json($res);
    }

    function deleteItem(Request $request)
    {

        $id = $request->route('id');
        AssemblyProductRequisition::find($id)->delete();

        return true;
    }

    function fetch()
    {

        $raw_query = AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where([
            'branch_id' => Parent::branch_id(),

            'status' => "complete",

        ]);
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('req_id', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('date_due', 'LIKE', '%' . request('search') . '%')
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
            $raw_query->where('user_id', Parent::user_id());
        }
        $raw_query->groupBy('req_id')->orderBy('id', 'DESC');
        $raw_query->selectRaw('*,SUM(ingredient_qty) as sum_ingredient_qty,SUM(sub_total) as sum_sub_total');
        $res = $raw_query->paginate(20);
        return response()->json($res);
    }
    function fetchByReqId()
    {
        $res = AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where([
            'branch_id' => Parent::branch_id(),
            'req_id' => request('req_id'),


        ])->get();
        return response()->json($res);
    }
    function delete(Request $request)
    {
        $req_id = $request->route('req_id');

        AssemblyProductRequisition::where('req_id', $req_id)->delete();
        return true;
    }
    function approve(Request $request)
    {
        $req_id = $request->req_id;

        AssemblyProductRequisition::where('req_id', $req_id)->update(["approved" => '1']);
        return true;
    }

    function fetchUserRequests()
    {
        $user_id = request('user_id');
        $filled = array_filter(request()->only([
            'req_id',
            'product_name',
            'ingredient_name'
        ]));
        $raw_query =   AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where(
            [
                'approved' => '1',
                'user_id' => $user_id,
                'branch_id' => Parent::branch_id(),
                'is_stock_updated' => 0
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
                } else if ($column == 'ingredient_name') {

                    $query->whereHas('ingredient', function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', '%' . $value . '%');
                    });
                }
            }
        });
        $res = $raw_query->whereRaw('ingredient_qty > ingredient_qty_issued')->get();
        return response()->json($res);
    }
    function fetchGroupedUserRequests()
    {
        $user_id = request('user_id');
        $filled = array_filter(request()->only([
            'req_id',
            'product_name',
            'ingredient_name'
        ]));
        $raw_query =   AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where(
            [
                'approved' => '1',
                'user_id' => $user_id,
                'branch_id' => Parent::branch_id(),
                'is_stock_updated' => 0
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
                } else if ($column == 'ingredient_name') {

                    $query->whereHas('ingredient', function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', '%' . $value . '%');
                    });
                }
            }
        });

        $res = $raw_query->selectRaw('*,SUM(ingredient_qty_issued) AS total_ingredient_qty_issued,
        SUM(ingredient_qty-ingredient_qty_issued) AS total_ingredient_pending')->groupBy('req_id', 'stock_id')->get();
        return response()->json($res);
    }

    function downloadReq(Request $request)
    {
        ini_set('max_execution_time', 2400);
        AssemblyProductRequisition::where([
            'req_id' => $request->req_id,
            'user_id' => Parent::user_id(),

        ])->update(['status' => 'complete']);

        $res = AssemblyProductRequisition::with(['user', 'department', 'stock', 'ingredient'])->where([
            'branch_id' => Parent::branch_id(),

            'req_id' => $request->req_id,
            'status' => "complete",

        ])->get();
        $branch = Branch::where('id', Parent::branch_id())->first();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $res[0]->req_id . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }

        $data = ["data" => $res, 'branch' => $branch];

        $pdf = PDF::loadView('pdf.assembly_requisition', $data); //compact('data') 
        $pdf->save($path);
        return response()->download($path);
    }
}
