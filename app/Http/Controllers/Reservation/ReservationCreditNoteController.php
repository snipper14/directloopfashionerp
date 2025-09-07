<?php

namespace App\Http\Controllers\Reservation;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Reservation\RoomSale;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Reservation\RoomReservation;
use App\Http\Controllers\BaseController;
use App\Reservation\ReservationCreditNote;
use App\Http\Requests\Reservation\ReservationCreditNoteRequest;

class ReservationCreditNoteController extends BaseController
{
    //
    public function create(ReservationCreditNoteRequest $request)
    {
        DB::transaction(function () use ($request) {
            ReservationCreditNote::create($request->validated() + Parent::user_branch_id());
            RoomReservation::where(["id" => $request->room_reservation_id])
                ->increment("credit_note_total", $request->total);

            RoomSale::create([
                'room_reservation_id' => $request->room_reservation_id,
                'amount_paid' => '-' . $request->total,
                'cash_paid' => 0,
                'mobile_money_paid' => 0,
                'card_paid' => 0,
                'cheque_paid' => 0,
                'bank_transfer_paid' => 0,
                'details' => 'credit'

            ] + Parent::user_branch_id());
        }, 5);
        return response()->json($this->queryByCreditNote($request->credit_no));
    }

    function queryByCreditNote($credit_no)
    {
        $main_query = ReservationCreditNote::with(['room_reservation', 'user']);

        $res = $main_query->where(["credit_no" => $credit_no])->first();
        return $res;
    }
    function fetchCreditNoteNumber()
    {
        return response()->json(["credit_no" => $this->creditNoteNumber()]);
    }


    function fetch()
    {
        $main_query = ReservationCreditNote::with(['room_reservation', "user"]);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) {
            $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();

            $query->whereBetween('updated_at', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('credit_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere("details", "LIKE", '%' . request('search') . '%')
                    ->orWhereHas('room_reservation', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        });

        $main_query->orderBy('id', "DESC");
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(50);
        }
        return response()->json($res);
    }


    function creditNoteNumber()
    {


        $latest = ReservationCreditNote::latest()->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $creditNoteNum = $today . '-' . 'CR' . sprintf('%04d', $string);
        $isUnique = ReservationCreditNote::where('credit_no', $creditNoteNum)->first();
        if ($isUnique) {
            $creditNoteNum = $today . '-' . 'CR' . ($latest->id + 1);
        }
        return  $creditNoteNum . Parent::user_id();
    }
}
