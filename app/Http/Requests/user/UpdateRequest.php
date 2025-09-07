<?php

namespace App\Http\Requests\user;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'id' => 'required',
            'email' => 'nullable',
            'username' => 'required|edit_unique_custom:users,username,branch_id,' . Auth::user()->branch_id . ',id,' . $this->id,
            'department_id' => 'required',
            'branch_id' => 'required',
            'role_id' => 'required',
            'name' => 'string|required',
            'phone' => 'required',
            'login_status' => 'nullable'


        ];
    }

    public function messages()
    {
        return [

            'username.unique_custom' => 'The username  exists.',
            'email.unique_custom' => 'The email exists',

        ];
    }
}
