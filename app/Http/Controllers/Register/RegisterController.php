<?php

namespace App\Http\Controllers\Register;

use Carbon\Carbon;
use App\Register\Register;
use App\CashDrops\CashDrop;
use Illuminate\Http\Request;
use App\CashDrops\CloseRegister;
use App\Expenses\ExpensePayment;
use App\Payments\CustomerPayment;
use App\Refund\RetailSalesRefund;
use App\Sale\AllSalesTotalReport;
use App\Sale\AllSaleDetailsReport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Rules\ValidateRegisterActive;
use App\Rules\ValidateIsCashierZOption;
use App\CashDrops\CloseRegisterCategory;
use App\Http\Controllers\BaseController;
use App\SupplierPayment\SupplierPayment;
use Illuminate\Support\Facades\Validator;
use App\CashDrops\CloseRegisterHourlySale;
use App\Drops\Mpesadrop;
use App\Rules\ValidateRegisterActiveSales;
use App\Rules\ValidateRegisterActiveBeforeClosingShift;

class RegisterController extends BaseController
{
    function create(Request $request)
    {
        $validator = Validator::validate($request->all(), [



            'opening_mpesa_balance' => "required|numeric",

            'opening_cash_balance' => [new ValidateRegisterActive($request)],
        ]);
        Register::create([
            'opening_mpesa_balance' => $request->opening_mpesa_balance,
            'opening_cash_balance' => $request->opening_cash_balance,
            'status' => 'opened'

        ] + Parent::user_branch_id());
        return true;
    }
    //

    function fetchRegisterOpeningBalances()
    {
        $res =  Register::with(['user'])->where(['status' => 'opened'])->latest()->first();
        return $res;
    }
    function closeRegister(Request $request)
    {

        $validator = Validator::validate($request->all(), [
            'cashier_left_cash' =>  [new ValidateIsCashierZOption($request), new ValidateRegisterActiveBeforeClosingShift($request)],
            'cashier_counted_cash' => "required|numeric",

            'counted_mpesa_amount' => "required|numeric|gte:0",
            'counted_card_balance' => "required|numeric|gte:0",


        ]);
        $register_id = null;
        DB::transaction(function () use ($request, &$register_id) {

            $registerOpeningTimestamp = Register::where(['status' => 'opened'])
                ->latest('created_at')
                ->value('created_at');

            if ($registerOpeningTimestamp) {
                // Use selectRaw to calculate totals in one query
                $salesTotals = AllSalesTotalReport::whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(receipt_total) as total_sales,SUM(total_vat) AS sum_total_vat, SUM(cash_pay+receipt_balance) as total_cash, SUM(mpesa_pay) as total_mpesa,
                SUM(card_pay) AS sum_card_pay,SUM(loyalty_points) AS sum_loyalty_points,SUM(credit_pay) AS sum_credit_payments')
                    ->first();
                $hourlySales = AllSaleDetailsReport::whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw("
        CONCAT(HOUR(created_at), ':00-', HOUR(created_at) + 1, ':00') as hour_range,
        SUM(row_total) as total_sales   
    ")
                    ->groupBy('hour_range')
                    ->orderByRaw('HOUR(created_at)')
                    ->get();
                $sales_details = AllSaleDetailsReport::with('productCategory')->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('*,SUM(row_total) as total_sales')
                    ->groupBy('product_category_id')->get();
                $return_cash_sales = RetailSalesRefund::where(['pay_method' => 'Cash'])->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(refund_amount) as cash_refund_amount')
                    ->first();
                $return_mpesa_sales = RetailSalesRefund::where(['pay_method' => 'M-pesa'])->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(refund_amount) as mpesa_refund_amount')
                    ->first();
                $cashdrop = CashDrop::whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(cash_amount) as total_cash_amount')
                    ->first();
                $mpesadrop = Mpesadrop::whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(mpesa_amount) as total_mpesa_amount')
                    ->first();
                $cash_customer_payments = CustomerPayment::where(['pay_method' => 'Cash'])->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(amount_paid) as cash_customer_payments')
                    ->first();
                $mpesa_customer_payments = CustomerPayment::where(['pay_method' => 'M-pesa'])->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(amount_paid) as mpesa_customer_payments')
                    ->first();
                $cash_supplier_payments = SupplierPayment::where(['pay_method' => 'Cash'])->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(amount_paid) as cash_supplier_payments')
                    ->first();
                $mpesa_supplier_payments = SupplierPayment::where(['pay_method' => 'M-pesa'])->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(amount_paid) as mpesa_supplier_payments')
                    ->first();
                $cash_expenses_payments = ExpensePayment::where(Parent::branch_array() + ['pay_method' => 'Cash'])->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(amount_paid) as cash_expenses_payments')
                    ->first();
                $mpesa_expenses_payments = ExpensePayment::where(['pay_method' => 'M-pesa'])->whereBetween('created_at', [$registerOpeningTimestamp, Carbon::now()])
                    ->selectRaw('SUM(amount_paid) as mpesa_expenses_payments')
                    ->first();


                // Results as an array or object
                $mpesa_customer_payments = $mpesa_customer_payments->mpesa_customer_payments ? $mpesa_customer_payments->mpesa_customer_payments : 0;
                $cash_customer_payments =    $cash_customer_payments->cash_customer_payments ? $cash_customer_payments->cash_customer_payments : 0;
                $mpesa_expenses_payments = $mpesa_expenses_payments->mpesa_expenses_payments ? $mpesa_expenses_payments->mpesa_expenses_payments : 0;
                $cash_expenses_payments = $cash_expenses_payments->cash_expenses_payments ? $cash_expenses_payments->cash_expenses_payments : 0;
                $mpesa_supplier_payments =  $mpesa_supplier_payments->mpesa_supplier_payments ? $mpesa_supplier_payments->mpesa_supplier_payments : 0;
                $cash_supplier_payments =   $cash_supplier_payments->cash_supplier_payments ? $cash_supplier_payments->cash_supplier_payments : 0;
                $total_cashdrop_amount = $cashdrop->total_cash_amount ? $cashdrop->total_cash_amount : 0;
                $total_mpesadrop_amount =   $mpesadrop->total_mpesa_amount ? $mpesadrop->total_mpesa_amount : 0;
                $sum_credit_payments =  $salesTotals->sum_credit_payments ? $salesTotals->sum_credit_payments : 0;
                $mpesa_payments = $salesTotals->total_mpesa ? $salesTotals->total_mpesa : 0;
                $return_cash_sales = $return_cash_sales->cash_refund_amount ? $return_cash_sales->cash_refund_amount : 0;
                $return_mpesa_sales = $return_mpesa_sales->mpesa_refund_amount ? $return_mpesa_sales->mpesa_refund_amount : 0;
                $cash_payments = $salesTotals->total_cash ? $salesTotals->total_cash : 0;
                $card_payments = $salesTotals->sum_card_pay ? $salesTotals->sum_card_pay : 0;

                $expected_cash = ($request->cashier_opening_cash + $cash_customer_payments + $cash_payments)
                    - ($cash_expenses_payments + $cash_supplier_payments + $total_cashdrop_amount + $return_cash_sales);

                $expected_mpesa = ($request->opening_mpesa_balance + $mpesa_customer_payments + $mpesa_payments) -
                    ($mpesa_supplier_payments + $mpesa_expenses_payments + $total_mpesadrop_amount + $return_mpesa_sales);
                $data = [
                    'opened_time' => $registerOpeningTimestamp,
                    'opening_mpesa_amount' => $request->opening_mpesa_balance,
                    'opening_cash_amount' => $request->cashier_opening_cash,
                    'cash_payments' =>  $cash_payments,
                    'mpesa_payments' => $mpesa_payments,
                    'card_payments' => $salesTotals->sum_card_pay ? $salesTotals->sum_card_pay : 0,
                    'loyalty_payments' => $salesTotals->sum_loyalty_points ? $salesTotals->sum_loyalty_points : 0,
                    'credit_payments' => $sum_credit_payments,
                    'return_cash_sales' => $return_cash_sales,
                    'return_mpesa_sales' => $return_mpesa_sales,
                    'cash_drops' => $total_cashdrop_amount,
                    'mpesa_drops' => $total_mpesadrop_amount,
                    'cash_supplier_payments' => $cash_supplier_payments,
                    'mpesa_supplier_payments' => $mpesa_supplier_payments,
                    'cash_expenses_payments' => $cash_expenses_payments,
                    'mpesa_expenses_payments' => $mpesa_expenses_payments,
                    'cash_customer_payments' => $cash_customer_payments,
                    'mpesa_customer_payments' =>  $mpesa_customer_payments,
                    'expected_cash' => $expected_cash,
                    'expected_mpesa' => $expected_mpesa,
                    'expected_card' => $card_payments,
                    'closing_cash' => $request->cashier_counted_cash,
                    'closing_mpesa' => $request->counted_mpesa_amount,
                    'closing_card' => $request->counted_card_balance,
                    'cash_shortover' => ($request->cashier_counted_cash - $expected_cash),
                    'mpesa_shortover' => ($request->counted_mpesa_amount - $expected_mpesa),
                    'card_shortover' => ($request->counted_card_balance - $card_payments),
                    'tax_collected' => $salesTotals->sum_total_vat ? $salesTotals->sum_total_vat : 0,
                    'cashier_left_cash'=>$request->cashier_left_cash
                ];
                $register = CloseRegister::create($data + Parent::user_branch_id());
                $register_id = $register->id;
                foreach ($sales_details as  $value) {
                    CloseRegisterCategory::create([
                        'product_category_id' => $value['product_category_id'],
                        'amount' => $value['total_sales'],
                        'close_register_id' => $register->id
                    ] + Parent::user_branch_id());
                }
                foreach ($hourlySales as  $value) {
                    CloseRegisterHourlySale::create([
                        'hours' => $value['hour_range'],
                        'amount' => $value['total_sales'],
                        'close_register_id' => $register->id
                    ] + Parent::user_branch_id());
                }
                Register::where(['status' => 'opened'])->update(['status' => 'closed']);
            } else {
                return 'No open register found.';
            }
        }, 5);

        $res = CloseRegister::with('closeRegisterCategories.productCategory', 'closeRegisterHourlySales', 'user')->where('id', $register_id)->first();
        return response()->json($res);
    }
    function  fetch()
    {
        $from = request('opening_time');
        $to = request('closing_time');
        $main_query = CloseRegister::with('closeRegisterCategories.productCategory', 'closeRegisterHourlySales', 'user')->latest();
        $main_query->when(request('opening_time', '') != '' && request('closing_time', '') != "", function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($q) {
            $q->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            });
        });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $data = $main_query->get();

        if (request('page') > 0) {
            $data = $main_query->paginate(40);
        }
        return $data;
    }
    function fetchCashierOnShift()
    {
        $res = Register::with(['user'])->latest()->first();
        return $res;
    }

    function fetchBalancesDetails(Request $request)
    {
        $opening_time = $request->opened_time ? $request->opened_time : '2000-01-01 00:00:00';
        $closing_time = $request->created_at;
        $cashier_id = $request->user_id;

        $main_query = AllSalesTotalReport::with(['user', 'cashier', 'waiter'])->where(Parent::branch_array());

        $main_query->whereBetween('created_at', [$opening_time, $closing_time]);

        //  $main_query->where(['cashier_id' => $cashier_id]);
        $data = $main_query->latest('created_at')->get();
        if (request('page') > 0) {
            $data = $main_query->latest('created_at')->paginated(30);
        }
        return response()->json($data);
    }
    function fetchBalancesDetailsItemized(Request $request)
    {

        $opening_time = $request->opened_time ? $request->opened_time : '2000-01-01 00:00:00';
        $closing_time = $request->created_at;
        $cashier_id = $request->user_id;

        $main_query = AllSaleDetailsReport::with(['user', 'stock'])->where(Parent::branch_array());

        $main_query->whereBetween('created_at', [$opening_time, $closing_time]);
        $main_query->selectRaw('*,SUM(qty) as sum_qty,SUM(row_total) AS sum_row_total,SUM(row_vat) AS sum_row_vat')
            ->groupBy('stock_id');

        //  $main_query->where(['cashier_id' => $cashier_id]);
        $data = $main_query->latest('created_at')->get();
        if (request('page') > 0) {
            $data = $main_query->latest('created_at')->paginated(30);
        }
        return response()->json($data);
    }

    function fetchClosingShift()
    {
        $res = CloseRegister::where(['branch_id' => Parent::branch_id()])->latest()->first();
        if ($res) {
            return $res;
        } else {
            return response()->json(["success" => "true"], 201);
        }
    }
}
