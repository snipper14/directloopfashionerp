<?php

namespace App\Http\Controllers\Users;

use App\User;
use Exception;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\BaseController;
use App\Http\Requests\user\UpdateRequest;
use App\Http\Requests\user\StoreUserRequest;

class UserController extends BaseController
{
    public function create(StoreUserRequest $request, User $user)
    {

        $user = $user->create($request->only('name', 'phone', 'role_id', 'username', 'department_id', 'branch_id', 'login_status') + ['password' => Hash::make($request->password)]);
        return response()->json(['user' => $user]);
    }

    public function fetch()
    {
        $main_query = User::with(['role', 'branch'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('email', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('role', function ($q) {
                        $q->where('roleName', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('branch', function ($q) {
                        $q->where('branch', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });
        $main_query->when(request('sort_name'), function ($query) {
            $query->orderBy('name', request('sort_name'));
        });
          $main_query->when(request('sort_phone'), function ($query) {
            $query->orderBy('phone', request('sort_phone'));
        });
         $main_query->when(request('sort_username'), function ($query) {
            $query->orderBy('username', request('sort_username'));
        });
         if (request('sort_branch')) {
        $main_query->leftJoin('branches', 'branches.id', '=', 'users.branch_id')
                   ->orderBy('branches.branch', request('sort_branch'))
                   ->select('users.*'); // Important: prevent select conflict
    }
    if (request('sort_role')) {
        $main_query->leftJoin('roles', 'roles.id', '=', 'users.role_id')
                   ->orderBy('roles.roleName', request('sort_role'));
    }
        $user =   $main_query->orderBy('users.id', 'DESC')->paginate(50);
        //     $user=User::orderBy('id','DESC')->paginate(10);
        return response()->json(['results' => $user]);
    }
    public function fetchAll()
    {
        $user = User::with(['role', 'branch'])->when(
            request('department_id', '') != '',
            function ($query) {
                $query->where('department_id', request('department_id'));
            }
        )->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('role', 'LIKE', '%' . request('search') . '%');
            });
        })->orderBy('id', 'DESC')->get();
        //     $user=User::orderBy('id','DESC')->paginate(10);
        return response()->json($user);
    }
    public function update(UpdateRequest $request)
    {
        $user = User::where('id', $request->id)->update($request->validated());
        return response()->json(['results' => $user]);
    }

    public function delete(Request $request, User $user)
    {
        $id = $request->route('id');

        $user->where('id', $id)->delete();
        return response()->json(['message' => "record deleted"]);
    }

    public function export_csv()
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $users = User::orderBy('id', 'DESC')->get();
        $columns = array('Name', 'Email', 'Phone', 'Role');

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                fputcsv($file, array($user->name, $user->email, $user->phone, $user->role));
            }
            fclose($file);
        };
        return response()->streamDownload($callback, 'prefix-' . date('d-m-Y-H:i:s') . '.csv', $headers);
        // return  response()->stream($callback, 200, $headers);
        //    return Response::stream($callback, 200, $headers);
    }

    public function getUserInfo()
    {
        $users = User::with(['role'])->orderBy('id', 'DESC')->get();
        return response()->json(['results' => $users]);
    }

    public function multiDelete(Request $request)
    {
            // Validate input
            $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:users,id'
            ]);

        

            // Prevent deleting self
            $userIds = array_filter($request->user_ids, function ($id) {
                return $id !== Auth::id();
            });

            if (empty($userIds)) {
                return response()->json(['message' => 'No valid users selected'], 400);
            }

            // Perform soft delete
            $deletedCount = User::whereIn('id', $userIds)->delete();

            return response()->json([
                'message' => "$deletedCount user(s) deleted successfully",
                'deleted_count' => $deletedCount
            ], 200);

        return true;
    }
}
