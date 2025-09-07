<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{

    public function index(Request $request)
    {
        if (!Auth::check() && $request->path() != 'login_pos') {
            return view('pos');
            //   return redirect('/login_pos');
        }

        if (!Auth::check() && $request->path() == 'login_pos') {

            return view('pos');
        }

        // you are already logged in... so check for if you are an admin user..
        $user = Auth::user();

        if ($request->path() == 'login') {
            return view('pos');
            // return redirect('/poslogin');
        }
        return $this->checkForPermission($user, $request);
    }

    public function waiterLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'bail|required',
            'password' => 'bail|required',

        ]);
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

    public function checkForPermission($user, $request)
    {
        $permission = json_decode($user->role->permission);
        $hasPermission = false;
        if (!$permission) {
            return view('pos');
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
                return view('pos');
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
            return view('pos');
        }


        return view('notfound');
    }
    public function logout()
    {
        Auth::logout();
        return redirect('/login_pos');
    }
}
