<?php

namespace App\Http\Controllers\Setting;

use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function createRole(Request $request)
    {
        $this->validate($request, [

            'roleName' => 'required|unique:roles,roleName,NULL,id,deleted_at,NULL',

        ]);
        Role::create([
            'roleName' => $request->roleName,
        ]);
        $data = Role::orderBy('id', 'DESC')->get();
        return response()->json($data);
    }
    public function updateRole(Request $request)
    {
        $this->validate($request, [
            'roleName' => "nullable|unique:roles,roleName,{$request->id},id,deleted_at,NULL",
            'id' => 'required'
        ]);
        Role::where('id', $request->id)->update([
            'roleName' => $request->roleName,
        ]);
        $data = Role::orderBy('id', 'DESC')->get();
        return response()->json($data);
    }
    public function fetchRoles()
    {
        $data = Role::orderBy('id', 'DESC')->get();
        return response()->json($data);
    }

    public function destroyRole(Request $request)
    {
        $id = $request->route('id');
        Role::find($id)->delete();
        return true;
    }

    public function assign_roles(Request $request)
    {
        $this->validate($request, [
            'permission' => 'required',
            'id' => 'required',
        ]);
        Role::where('id', $request->id)->update([
            'permission' => $request->permission,
        ]);
        return true;
    }

    function getRoles()
    {

        return Role::all();
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'confirm_password' => 'required|min:1|max:50',
            'new_password' => 'required|min:4|max:50|required_with:confirm_password|same:confirm_password',
        ]);
        $current_password = Auth::User()->password;
        if (Hash::check($request->old_password, $current_password)) {
            $user_id = Auth::User()->id;
            $obj_user = User::find($user_id);
            $obj_user->password = Hash::make($request->new_password);
            $obj_user->save();
            return response()->json(["status" => "success", "msg" => "password changed successfully login with new password"]);
        } else {
            return response()->json(["status" => "error", "msg" => "Current pasword is incorrect"], 400);
        }
    }








    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'password_confirmation' => 'required|min:4|max:50',
            'password' => 'required|min:4|max:50|required_with:password_confirmation|same:password_confirmation',
        ]);
        User::where('id', $request->id)->update(
            ['password' => Hash::make($request->password)]
        );

        return true;
    }

    public function deleteUser(Request $request)
    {
        $id = $request->route('id');
        User::find($id)->delete();
        return true;
    }
}
