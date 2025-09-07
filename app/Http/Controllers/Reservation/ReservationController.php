<?php

namespace App\Http\Controllers\Reservation;

use Carbon\Carbon;
use Dompdf\Dompdf;
use App\Rooms\Room;
use Dompdf\Options;
use App\Guest\Guest;
use App\Rooms\RoomRate;
use Dompdf\FontMetrics;
use Illuminate\Http\Request;
use App\Rules\GuestPaidChecker;
use App\Transaction\Transaction;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Reservation\RoomReservation;
use Illuminate\Support\Facades\Auth;
use App\Rules\ValidateIfRoomAvailable;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Reservation\ReservationRequest;
use App\Reservation\RoomSale;
use App\Sale\AllSalesTotalReport;
use App\Scopes\BranchScope;
use Symfony\Component\VarDumper\Cloner\Data;

class ReservationController extends BaseController
{
    public function create(ReservationRequest $request)
    {
        $this->validate($request, ['room_id' => new ValidateIfRoomAvailable()]);

        DB::transaction(function () use ($request) {

            $insert_data = RoomReservation::create($request->validated() + Parent::user_branch_id());
            Room::where('id', $request->room_id)->update(['occupation' => "accupied"]);

            RoomSale::create([
                'room_reservation_id' => $insert_data->id,
                'amount_paid' => $request->amount_paid,
                'cash_paid' => $request->cash_paid,
                'mobile_money_paid' => $request->mobile_money_paid,
                'online_paid' => $request->online_paid,
                'card_paid' => $request->card_paid,
                'cheque_paid' => $request->cheque_paid,
                'bank_transfer_paid' => $request->bank_transfer_paid,
                'details' => 'new_entry'

            ] + Parent::user_branch_id());
            $guest_name = Guest::where('id', $request->guest_id)->first()->name;
            if ($request->paid) {

                $insert_ledger_data = array(


                    'ref' => $request->ref_no,
                    'entry_date' => date('Y-m-d'),
                    'description' => 'Accomodation',
                    'credit' => "Room",
                    'debit' => $guest_name,
                    'amount' => $request->total,
                );
                Transaction::create($insert_ledger_data + Parent::user_branch_id());
            } else {
                $insert_ledger_data = array(


                    'ref' => $request->ref_no,
                    'entry_date' => date('Y-m-d'),
                    'description' => 'Accomodation',
                    'credit' => $guest_name,
                    'debit' => "Room",
                    'amount' => $request->total,
                );
                Transaction::create($insert_ledger_data + Parent::user_branch_id());
            }
        }, 5);
        return true;
    }




    public function update(ReservationRequest $request)
    {
        DB::transaction(function () use ($request) {

            RoomReservation::where('id', $request->id)

                ->update([
                    'total' => DB::raw('total + ' . $request->total),
                    'tax_amount' => DB::raw('tax_amount + ' . $request->tax_amount),
                    'amount_paid' => DB::raw('amount_paid + ' . $request->amount_paid),
                    'cash_paid' => DB::raw('cash_paid + ' . $request->cash_paid),
                    'mobile_money_paid' => DB::raw('mobile_money_paid + ' . $request->mobile_money_paid),
                    'online_paid' => DB::raw('online_paid + ' . $request->online_paid),
                    'cheque_paid' => DB::raw('cheque_paid + ' . $request->cheque_paid),
                    'bank_transfer_paid' => DB::raw('bank_transfer_paid + ' . $request->bank_transfer_paid),
                    'card_paid' => DB::raw('card_paid + ' . $request->card_paid),

                ]
                    + $request->validated() + ['updated_by' => Parent::user_id()]);

            RoomSale::create([
                'room_reservation_id' => $request->id,
                'amount_paid' => $request->amount_paid,
                'cash_paid' => $request->cash_paid,
                'mobile_money_paid' => $request->mobile_money_paid,
                'online_paid' => $request->online_paid,
                'card_paid' => $request->card_paid,
                'cheque_paid' => $request->cheque_paid,
                'bank_transfer_paid' => $request->bank_transfer_paid,
                'details' => 'update_entry'

            ] + Parent::user_branch_id());
        }, 5);
        return true;
    }



    private function reportQuery()
    {
        $filled = array_filter(request()->only([
            'room_id',
            'guest_id',
            'clear_status',
            'guest_company_id'
        ]));
        $checkin_from = Carbon::parse(request('checkin_from'))->startOfDay();
        $checkin_to = Carbon::parse(request('checkin_to'))->endOfDay();
        $checkout_from = Carbon::parse(request('checkout_from'))->startOfDay();
        $checkout_to = Carbon::parse(request('checkout_to'))->endOfDay();
        $raw_query = RoomReservation::with([
            'house_keeper', 'waiter', 'guest_company',
            'guest', 'room.room_standard', 'user', 'room_package'
        ]);
        $from = date(request('from'));
        $to = date(request('to'));

        $raw_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $raw_query->when(request('checkin_to', '') != '' && request('checkin_from', '') != '', function ($query) use ($checkin_from, $checkin_to) {

            $query->whereBetween('from', [$checkin_from, $checkin_to]);
        });
        $raw_query->when(request('checkout_to', '') != '' && request('checkout_from', '') != '', function ($query) use ($checkout_from, $checkout_to) {

            $query->whereBetween('to', [$checkout_from, $checkout_to]);
        });
        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {
            foreach ($filled as $column => $value) {
                $query->where($column, 'LIKE', '%' . $value . '%');
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('house_keeper', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room', function ($query) {
                        $query->where('door_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room_package', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('guest', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'waiter',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    )
                    ->orWhereHas(
                        'user',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->orderBy('id', "DESC");
        if (request('status') == 'occupied') {
            $raw_query->where('clear_status', 'occupied');
        }
        return $raw_query;
    }
    public function fetch()
    {


        $raw_query = $this->reportQuery();


        $res =   $raw_query->where(Parent::branch_array())->get();
        if (request('page') > 0) {
            $res = $raw_query->where(Parent::branch_array())->paginate(50);
        }
        return response()->json($res);
    }

    public function fetchDailyAccomodation()
    {

        $raw_query = $this->dailyAccomadationQuery();
        $res =   $raw_query->where(Parent::branch_array())->get();

        if (request('page') > 0) {
            $res = $raw_query->where(Parent::branch_array())->paginate(50);
        }

        return response()->json($res);
    }
    function dailyAccomadationQuery()
    {
        $filled = array_filter(request()->only([
            'room_id',
            'guest_id',
            'clear_status'
        ]));
        $dt = request('curr_date');
        if (empty(request('curr_date'))) {
            $dt = Parent::currentDateTime(); //= Carbon::now();
        }

        $raw_query = RoomReservation::with([
            'house_keeper', 'waiter', 'guest', 'guest_company',
            'room.room_standard', 'room_package', 'branch', 'user'
        ]);
        $raw_query->whereRaw('"' . $dt . '" between `from` and `to`');

        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {
            foreach ($filled as $column => $value) {
                $query->where($column, 'LIKE', '%' . $value . '%');
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('house_keeper', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room', function ($query) {
                        $query->where('door_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room_package', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('guest', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'waiter',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->orderBy('id', "DESC");

        //   $raw_query->where('clear_status', 'occupied');
        return $raw_query;
    }
    public function fetchDailyAccomodationTotals()
    {
        $filled = array_filter(request()->only([
            'room_id',
            'guest_id',
            'clear_status'
        ]));
        $dt = request('curr_date');
        if (empty(request('curr_date'))) {
            $dt = Parent::currentDateTime();
        }

        $raw_query = RoomReservation::with(['house_keeper', 'waiter', 'guest', 'room.room_standard', 'room_package']);
        $raw_query->whereRaw('"' . $dt . '" between `from` and `to`');

        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {
            foreach ($filled as $column => $value) {
                $query->where($column, 'LIKE', '%' . $value . '%');
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('house_keeper', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room', function ($query) {
                        $query->where('door_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room_package', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('guest', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'waiter',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        })->orderBy('id', "DESC");

        // $raw_query->where('clear_status', 'occupied');
        $raw_query->selectRaw('SUM(price) AS sum_total,SUM(tax_amount) AS sum_tax_amount');
        $res =   $raw_query->where(Parent::branch_array())->first();

        return response()->json($res);
    }
    public function fetchTotals()
    {
        $raw_query = $this->reportQuery();
        $raw_query->selectRaw('SUM(total) AS sum_total,SUM(tax_amount) AS sum_tax_amount,SUM(credit_note_total) AS sum_credit_note_total');
        $res =   $raw_query->where(Parent::branch_array())->first();

        return response()->json($res);
    }
    public function fetchPendingCheckouts()
    {
        $filled = array_filter(request()->only([
            'room_id',
            'guest_id',
            'clear_status'
        ]));

        $raw_query = RoomReservation::with(['house_keeper', 'waiter', 'guest', 'room.room_standard', 'room_package']);



        $raw_query->when(count($filled) > 0, function ($query) use ($filled) {
            foreach ($filled as $column => $value) {
                $query->where($column, 'LIKE', '%' . $value . '%');
            }
        });
        $raw_query->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')

                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('house_keeper', function ($query) {
                        $query->where('first_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('last_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room', function ($query) {
                        $query->where('door_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('room_package', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('guest', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas(
                        'waiter',
                        function ($query) {
                            $query->where('name', 'LIKE', '%' . request('search') . '%');
                        }
                    );
            });
        });

        $raw_query->where('clear_status', 'occupied');

        $raw_query->where('to', '<=', Parent::currentDateTime());
        $res =   $raw_query->where(Parent::branch_array() + ['clear_status' => 'occupied'])->get();
        if (request('page') > 0) {
            $res = $raw_query->where(Parent::branch_array())->paginate(50);
        }
        return response()->json($res);
    }
    function destroy(Request $request)
    {
        $id = $request->route('id');
        $checkEmpty = RoomReservation::where(['id' => $id])->first();

        if ($checkEmpty) {

            Room::where('id', $checkEmpty->room_id)->update(['occupation' => "empty", 'usage_status' => "not_ready"]);
        }
        RoomReservation::where('id', $id)->delete();
        RoomSale::where(['room_reservation_id' => $id])->delete();
        return true;
    }

    function checkout(Request $request)
    {
        $this->validate($request, ['id' => new GuestPaidChecker()]);
        RoomReservation::where('id', $request->id)->update([
            'clear_status' => "cleared",
            'house_keeping_status' => "pending",
            'checkout_date' =>  Parent::currentDateTime(),
        ]);
        $checkEmpty = RoomReservation::where(['room_id' => $request->room_id, 'clear_status' => "occupied"])->get();
        if ($checkEmpty->isEmpty()) {
            Room::where('id', $request->room_id)->update(['occupation' => "empty", 'usage_status' => "not_ready"]);
        }
        return true;
    }
    function kitchenReport()
    {

        $res_array = $this->queryKitchenGuestReport();
        //  
        return response()->json($res_array);
    }
    function queryKitchenGuestReport()
    {
        $dt = request('curr_date');
        if (empty(request('curr_date'))) {
            $dt = Parent::currentDateTime(); //= Carbon::now();
        }
        $raw_query  = RoomReservation::with([
            'house_keeper', 'waiter', 'guest', 'branch', 'user',
            'room.room_standard', 'room_package'
        ]);

        $raw_query->whereRaw('"' . $dt . '" between `from` and `to`');
        $raw_query->selectRaw('*,SUM(total) AS sum_total,SUM(no_guest) AS sum_no_guest');
        $raw_query->groupBy('room_package_id');
        $res =  $raw_query->orderBy('id', "DESC")->get();


        $res_array = [];
        foreach ($res as  $value) {

            $raw_query2 = RoomReservation::with([
                'house_keeper', 'waiter', 'guest',
                'room.room_standard', 'room_package', 'room_rate'
            ]);

            $raw_query2->whereRaw('"' . $dt . '" between `from` and `to`');
            $raw_query2->where('room_package_id', $value['room_package_id']);

            $res2 =  $raw_query2->orderBy('id', "DESC")->get();
            $res_array[] = array(
                'sum_total' => $value['sum_total'],
                'sum_no_guest' => $value['sum_no_guest'],
                'room_package' => $value['room_package']->name,
                'branch' => $value['branch'],
                'user' => $value['user'],
                'data' => $res2,
            );
        }
        return $res_array;
    }


    function fetchGroupedInhouseSummary()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = DB::table('room_reservations')


            ->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

                $query->whereBetween('room_reservations.created_at', [$from, $to]);
            })
            ->join('rooms', 'rooms.id', '=', 'room_reservations.room_id')

            ->join('room_packages', 'room_packages.id', '=', 'room_reservations.room_package_id')
            ->join('room_standards', 'room_standards.id', '=', 'rooms.room_standard_id')
            ->selectRaw('room_standards.name,rooms.room_standard_id,SUM(room_reservations.total) AS sum_total,
        SUM(room_reservations.no_guest) AS sum_no_guest,
        SUM(room_reservations.price) AS sum_rate')
            ->groupBy('rooms.room_standard_id');

        $raw_res = $raw_query->get();
        $room_type_array = array();
        foreach ($raw_res as  $value) {

            $room_standard_id = $value->room_standard_id;

            $room_type_array[] = array(
                'room_standard_id' => $room_standard_id,
                'name' => $value->name,
                'sum_total' => $value->sum_total,
                'sum_rate' => $value->sum_rate,
                'no_guest' => $value->sum_no_guest,
                'details' => $this->fetchGroupInner($room_standard_id)
            );
        }
        return response()->json($room_type_array);
    }

    function fetchGroupInner($room_standard_id)
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = DB::table('room_reservations')


            ->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

                $query->whereBetween('room_reservations.created_at', [$from, $to]);
            })
            ->join('rooms', 'rooms.id', '=', 'room_reservations.room_id')

            ->join('room_packages', 'room_packages.id', '=', 'room_reservations.room_package_id')

            ->join('guests', 'guests.id', '=', 'room_reservations.guest_id')
            ->join('guest_companies', 'guest_companies.id', '=', 'room_reservations.guest_company_id')

            ->selectRaw('rooms.room_standard_id,rooms.door_name,room_reservations.no_guest,room_reservations.qty,guests.name AS guest,room_packages.name AS room_package,
        room_reservations.total,room_reservations.price,room_reservations.created_at,guest_companies.name AS guest_company ')
            ->where('rooms.room_standard_id', $room_standard_id);

        $raw_res = $raw_query->latest('created_at')->get();
        return $raw_res;
    }
    function downLoadDailyPdf()
    {
        ini_set('max_execution_time', 600);
        $dt = request('curr_date');
        if (empty(request('curr_date'))) {
            $dt = Carbon::now();
        }
        $raw_query = $this->dailyAccomadationQuery();
        $res = $raw_query->get();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "daily_room_reservation" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ['data' => $res, 'user' => Auth::user()];
        //return response()->json($data);
        if (request('house_keeping_report')) {
            $pdf = PDF::loadView('pdf.daily_room_reservation_house_keeping_report', $data + ["curr_date" => $dt]); //compact('data')
        } else {
            $pdf = PDF::loadView('pdf.daily_room_reservation', $data + ["curr_date" => $dt]); //compact('data')
        }
        Parent::createWatermark($pdf);
        $pdf->save($path);


        return response()->download($path);
    }


    function downLoadAccommodationReport()
    {


        $raw_query = $this->reportQuery();
        $res =   $raw_query->where(Parent::branch_array())->get();

        $from = date(request('from'));
        $to = date(request('to'));
        ini_set('max_execution_time', 600);
        $dt = request('curr_date');
        if (empty(request('curr_date'))) {
            $dt = Carbon::now();
        }

        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "reservation_report" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ['data' => $res, 'user' => Auth::user(), 'from' => $from, 'to' => $to, 'guest_company_id' => request('guest_company_id')];
        //return response()->json($data);

        $pdf = PDF::loadView('pdf.reservation_report', $data + ["curr_date" => $dt]); //compact('data')

        Parent::createWatermark($pdf);

        $pdf->save($path);
        return response()->download($path);
    }

    function downLoadAccommodationPaymentsReport()
    {


        $raw_query = $this->reportQuery();
        $res =   $raw_query->where(Parent::branch_array())->get();

        $from = date(request('from'));
        $to = date(request('to'));
        ini_set('max_execution_time', 600);
        $dt = request('curr_date');
        if (empty(request('curr_date'))) {
            $dt = Carbon::now();
        }

        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "reservation_payment_report" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ['data' => $res, 'user' => Auth::user(), 'from' => $from, 'to' => $to, 'guest_company_id' => request('guest_company_id')];
        //return response()->json($data);

        $pdf = PDF::loadView('pdf.reservation_payment_report', $data + ["curr_date" => $dt]); //compact('data')

        Parent::createWatermark($pdf);

        $pdf->save($path);
        return response()->download($path);
    }


    function downLoadAccommodationDailyIncomeReport()
    {

      
        ini_set('max_execution_time', 600);
        $dt = request('curr_date');
        if (empty(request('curr_date'))) {
            $dt = Carbon::now();
        }
        $raw_query = $this->dailyAccomadationQuery();      
        if (empty(request('curr_date'))) {
            $dt = Carbon::now();
        }
       
        $res = $raw_query->get();
      
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "daily_reservation_income" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ['data' => $res, 'user' => Auth::user()];
        //return response()->json($data);
       
            $pdf = PDF::loadView('pdf.daily_reservation_income', $data + ["curr_date" => $dt]); //compact('data')
      
        Parent::createWatermark($pdf);
        $pdf->save($path);


        return response()->download($path);
    }



    function downLoadInhouseGuestReport()
    {


        $res_array = $this->queryKitchenGuestReport();
        ini_set('max_execution_time', 600);
        $dt =   Parent::currentDateTime();
        if (empty(request('curr_date'))) {
            $dt = Carbon::now();
        }

        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "in_house_guest" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ['data' => $res_array, 'user' => Auth::user()];
        //return response()->json($data);

        $pdf = PDF::loadView('pdf.in_house_guest', $data + ["curr_date" => $dt]); //compact('data')

        Parent::createWatermark($pdf);

        $pdf->save($path);




        return response()->download($path);
    }
}
