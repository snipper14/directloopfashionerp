<?php

namespace App\Http\Controllers\Card;

use Illuminate\Http\Request;
use App\Card\CardTerminalAccount;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;

class CardTerminalController extends BaseController
{
    function create(Request $request)
    {
        $rules = [
            'account_id' => 'required|unique_custom:card_terminal_accounts,account_id,branch_id,' . Parent::branch_id()
        ];
        $customMessages = [
            'account_id.unique_custom' => 'Account already created',

        ];
        $this->validate($request, $rules, $customMessages);
        CardTerminalAccount::create(['account_id' => $request->account_id] + Parent::user_branch_id());
        return true;
    }
    function fetch()
    {
        $data = CardTerminalAccount::with(['account'])
            ->when(request('search', '') != '', function ($query) {
                $query->where(function ($q) {
                    $q->where('account', 'LIKE', '%' . request('search') . '%');
                });
            })->orderBy('id', 'DESC')->get();

        return response()->json($data);
    }

    function destroy(Request $request)
    {
        $id = $request->route('id');
        CardTerminalAccount::where('id', $id)->delete();
        return true;
    }
}
