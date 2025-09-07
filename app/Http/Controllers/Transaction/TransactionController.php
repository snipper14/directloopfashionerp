<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Transaction\Transaction;
use Illuminate\Http\Request;

class TransactionController extends BaseController
{
    public function fetch()
    {
        $row_query = Transaction::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref_no', 'LIKE', '%' . request('search') . '%')
                   
                    ->orWhere('description', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('credit', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('debit', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
                   
            });
        })->with([ 'user'])->where(Parent::branch_array())->orderBy('id', 'DESC');
        $data = $row_query->get();
        if (request('page') > 0) {
            $data = $row_query->paginate(200);
            return response()->json($data);
        }

        return response()->json($data);
    }
}
