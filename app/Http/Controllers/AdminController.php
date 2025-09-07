<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Http\Requests\LicenceRequest;
use App\UserLogin;

class AdminController extends BaseController
{
    function home(Request $request){
      
        if (!Auth::check() ) {
        
           return view('welcome');
        }else{
            return view('welcome');
        }
    }
    public function index(Request $request)
    {

        // first check if you are loggedin and admin user ...
        //return Auth::check();

        if (!Auth::check() && $request->path() != 'login') {
            return redirect('/login');
        }

        if (!Auth::check() && $request->path() == 'login') {

            return view('welcome');
        }

        // you are already logged in... so check for if you are an admin user..
        $user = Auth::user();

        if ($request->path() == 'login') {
            return redirect('/');
        }
        //  return view('welcome');
        return $this->checkForUserPermission($user, $request);
    }
    public function checkForUserPermission($user, $request)
    {
        $permission = json_decode($user->role->permission);
        $hasPermission = false;
        if (!$permission) {
            return view('welcome');
        }
        //allow all routes not included in db
        if ($permission) {
            $is_found = false;
            foreach ($permission as $p) {

                foreach ($p->children as $c) {

                    if ($c->name == $request->path()) {
                        $is_found = true;
                    }
                }
            }
            if (!$is_found) {
                return view('welcome');
            }
        }
        //check permission for included routes
        foreach ($permission as $p) {
            foreach ($p->children as $c) {
                if ($c->name == $request->path()) {
                    if ($c->read) {
                        $hasPermission = true;
                    }
                }
            }
        }
        if ($hasPermission) {
            return view('welcome');
        }


        return view('notfound');
    }
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
    public function logoutPOS()
    {
        Auth::logout();
        return true;
    }

    public function adminLogin(LicenceRequest $request)
    {
        // validate request
        // $this->validate($request, [

        //     'email' => 'bail|required_without:username',
        //     'username' => 'bail|required_without:email',
        //     'password' => 'bail|required',

        // ]);
        $ip = $request->ip();
        if ($request->email) {
            if (
                Auth::attempt(['email' => $request->email, 'password' => $request->password])
                ||
                Auth::attempt(['username' => $request->email, 'password' => $request->password])
            ) {
                $user = Auth::user();
                if($user->login_status!=='Admin'){
                    return response()->json([
                        'msg' => 'You are not an administrator',
                    ], 403);
                }
                $permission = Auth::user()->role->permission;
                $department =  Auth::user()->department;
                $branch = Auth::user()->branch;
                $role = Auth::user()->role;


                UserLogin::create(["user_id" => $user->id, "branch_id" => $user->branch_id, "module" => "backoffice", "location_ip" => $ip]);
                return response()->json([
                    'msg' => 'You are logged in',
                    'user' => $user,
                    'permission' => $permission,
                    'department' => $department,
                    'branch' => $branch,
                    'role' => $role
                ]);
            } else {
                return response()->json([
                    'msg' => 'Incorrect login details',
                ], 403);
            }
        } else if ($request->username) {
            if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                $user = Auth::user();
                $permission = Auth::user()->role->permission;
                $department =  Auth::user()->department;
                $branch = Auth::user()->branch;
                $role = Auth::user()->role;

                UserLogin::create(["user_id" => $user->id, "branch_id" => $user->branch_id, "module" => "backoffice", "location_ip" => $ip]);


                $data =
                    [
                        'msg' => 'You are logged in',
                        'user' => $user,
                        'permission' => json_decode($permission),
                        'department' => $department,
                        'branch' => $branch,
                        'role' => $role
                    ];


                return response()->json(
                    $data,
                    200,
                    [],
                    JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                );

                // 200,
                // [],
                // JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            } else {
                return response()->json([
                    'msg' => 'Incorrect login details',
                ], 403);
            }
        }
    }

    public function userLogin(LicenceRequest $request)
    {
        // validate request
        // $this->validate($request, [

        //     'email' => 'bail|required_without:username',
        //     'username' => 'bail|required_without:email',
        //     'password' => 'bail|required',

        // ]);
        $ip = $request->ip();
        if ($request->email) {
            if (
                Auth::attempt(['email' => $request->email,'branch_id' => $request->branch_id, 'password' => $request->password])
                ||
                Auth::attempt(['username' => $request->email, 'branch_id' => $request->branch_id,'password' => $request->password])
            ) {
                $user = Auth::user();
                $permission = Auth::user()->role->permission;
                $department =  Auth::user()->department;
                $branch = Auth::user()->branch;
                $role = Auth::user()->role;


                UserLogin::create(["user_id" => $user->id, "branch_id" => $user->branch_id, "module" => "backoffice", "location_ip" => $ip]);
                return response()->json([
                    'msg' => 'You are logged in',
                    'user' => $user,
                    'permission' => $permission,
                    'department' => $department,
                    'branch' => $branch,
                    'role' => $role
                ]);
            } else {
                return response()->json([
                    'msg' => 'Incorrect login details',
                ], 403);
            }
        } else if ($request->username) {
            if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                $user = Auth::user();
                $permission = Auth::user()->role->permission;
                $department =  Auth::user()->department;
                $branch = Auth::user()->branch;
                $role = Auth::user()->role;

                UserLogin::create(["user_id" => $user->id, "branch_id" => $user->branch_id, "module" => "backoffice", "location_ip" => $ip]);


                $data =
                    [
                        'msg' => 'You are logged in',
                        'user' => $user,
                        'permission' => json_decode($permission),
                        'department' => $department,
                        'branch' => $branch,
                        'role' => $role
                    ];


                return response()->json(
                    $data,
                    200,
                    [],
                    JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                );

                // 200,
                // [],
                // JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            } else {
                return response()->json([
                    'msg' => 'Incorrect login details',
                ], 403);
            }
        }
    }

    function getUserName()
    {

        $res = User::orderBy('id', 'ASC')->where(["branch_id" => 1])->where('role_id', '=', 11)
            ->orWhere('role_id', '=', 12)->orWhere('role_id', '=', 13)
            ->get();
            $res=User::orderBy('id', 'ASC')->get();
        return response()->json($res);
    }

    public function externalLoginID(Request $request)
    {
        $user = User::where(['email' => $request->email, 'id' => $request->id])->first();
        if ($user) {
           
            Auth::login($user);
            return response()->json([
                'msg' => 'You are logged in',
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'msg' => 'Incorrect login details',
            ], 403);
        }
    }
    public function externalLogin(Request $request)
    {

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            return response()->json([
                'msg' => 'You are logged in',
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'msg' => 'Incorrect login details',
            ], 403);
        }
    }

    function sessionMonitor(){
        return true;
    }
}
