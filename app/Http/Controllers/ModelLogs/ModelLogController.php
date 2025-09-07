<?php

namespace App\Http\Controllers\ModelLogs;

use App\Log\ModelLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;


class ModelLogController extends BaseController
{
    public function fetch(Request $request)
    {
        $search = $request->query('search');
        $userId = $request->query('user_id');
        $from = $request->query('from');
        $to = $request->query('to');

        $logs = ModelLog::with(['user', 'branch'])->when($search, function ($query, $search) {
            return $query->where('action', 'like', "%{$search}%")
                ->orWhere('model_type', 'like', "%{$search}%")
                ->orWhere('data', 'like', "%{$search}%")
                ->orWhereHas('user', function ($query) {
                    $query->where('name', 'LIKE', '%' . request('search') . '%');
                })->orWhereHas('branch', function ($query) {
                    $query->where('branch', 'LIKE', '%' . request('search') . '%');
                });
        })
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->latest()
            ->paginate(30);

        return response()->json($logs);
    }
}
