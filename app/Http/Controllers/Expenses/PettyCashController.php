<?php

namespace App\Http\Controllers\Expenses;

use App\Expenses\PettyCash;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expenses\PettyCashRequest;
use Illuminate\Http\Request;

class PettyCashController extends BaseController
{
    public function create(PettyCashRequest $request){
        PettyCash::create($request->validated()+['user_id'=>Parent::user_id()]);
        $res=PettyCash::latest('created_at')->get();
        return response()->json($res);
    }
    function fetch()
    {
        $from = request('from');
        $to = request('to');
        $raw_query = PettyCash::when($from != '' && $to != '', function ($query) use ($to, $from) {
                $query->whereBetween('date_recorded', [$from, $to]);
            })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q
                        ->whereHas('user', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })->with([  'user'])
            ->orderBy('id', 'DESC');

        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(15);
        }
        return response()->json(['results' => $data] + ['total' => $this->openingBalanceTotals()]);
    }
    function openingBalanceTotals()
    {
        $from = request('from');
        $to = request('to');
        $raw_query = PettyCash::when($from != '' && $to != '', function ($query) use ($to, $from) {
                $query->whereBetween('date_recorded', [$from, $to]);
            })
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q
                        ->whereHas('user', function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        });
                });
            })->selectRaw('SUM(opening_balance) AS total_amount')
            ->orderBy('id', 'DESC');

        $data =  $raw_query->first();
        return $data;
    }

    function destroy(Request $request)
    {

        $id = $request->route('id');
        PettyCash::where('id', $id)->delete();
        return true;
    }
}
