<?php

namespace App\Http\Requests\user;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'username' => 'required|unique_custom:users,username,branch_id,' . Auth::user()->branch_id,
     
      'first_name' => 'required|min:3|max:200',
      'last_name' => 'required|min:3|max:200',


      'department_id' => 'required',
      'branch_id' => 'required',
      'role_id' => 'required',
      'name' => 'string',
      'phone' => 'required',
      //  'phone'=> 'required|regex:/(254)[0-9]{9}/',
      'password' =>'nullable',
      'login_status'=>'nullable'

    ];
  }

  public function messages()
  {
    return [

      'email.unique_custom' => 'The Email has been registered',
      'username.unique_custom' => 'The username has been registered',

    ];
  }
}
