<?php

namespace App\Http\Controllers\Sale;

use DateTime;
use Exception;
use App\Sale\Sale;
use Carbon\Carbon;
use App\Sale\POSale;
use App\Stock\Stock;
use App\POS\POSOrder;
use App\Branch\Branch;
use App\POS\POSCredit;
use App\Sale\SoldItem;
use App\Cart\OrderCart;
use App\dept\Department;
use App\Invoices\Invoice;
use App\Customer\Customer;
use App\Employee\Employee;
use App\PreOrder\Preorder;
use Mike42\Escpos\Printer;
use App\Scopes\BranchScope;
use App\Inventory\Inventory;
use App\Stock\DeptStockMovt;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use App\Rules\ValidateBatchNo;
use App\Rules\ValidateBatchQty;
use App\Transaction\Transaction;
use App\Card\CardTerminalAccount;
use App\Refund\RetailSalesRefund;
use App\Sale\AllSalesTotalReport;
use App\Sale\AllSaleDetailsReport;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Jobs\UpdateCustomerDetails;
use App\POS\OrderEmployeeDeduction;
use App\Rules\ValidateExpiryOnSale;
use App\Rules\ValidatePOSCreditQty;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Rules\ValidateIfItemIsOnEtim;
use App\Rules\ValidateIfStockIsEtims;
use App\Rules\ValidatePOSCreditLimit;
use App\Jobs\UpdateCustomerPromoStatus;
use App\Http\Controllers\BaseController;
use App\Rules\ValidateStockAvailability;
use phpDocumentor\Reflection\Types\This;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Sale\POSSaleRequest;
use App\Rules\ValidateRegisterActiveSales;
use App\PurchaseOrder\PurchaseOrderReceivable;
use App\DirectRetailCredit\DirectRetailsCredit;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Rules\ValidatePostSaleEtimsCodeAvailability;
use App\Http\Requests\Refund\RetailsSaleRefundRequest;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use App\Http\Requests\RetailsCredit\RetailPOSSaleRequest;

class POSSaleController extends BaseController
{

    public function create(POSSaleRequest $request)
    {
        $validator = Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',
            'external_ref' => new ValidateRegisterActiveSales($request),
            // 'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request), new ValidateBatchNo($request), new ValidateExpiryOnSale($request), new ValidateBatchQty($request)],
            'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request), new ValidateBatchNo($request)],

            'order_no' => "required",

        ]);
        if (AllSalesTotalReport::where('external_ref', $request->external_ref)->exists()) {
            return response()->json(['message' => 'Sale already processed'], 200);
        }
        if ($request->credit_pay > 0) {
            $validator = Validator::validate($request->all(), [

                'credit_pay' => [new ValidatePOSCreditLimit($request)],
            ]);
        }
        $receipt_no = $this->receiptNumberFinal();
        DB::transaction(function () use ($request, $receipt_no) {
            $unique_code = Parent::generateDocNumber("R_SALE");
            $department_id  =   Department::where('show_pos', 1)->first()->id;
            AllSalesTotalReport::create(
                ['order_no' => $receipt_no, 'receipt_no' => $receipt_no] +
                    $request->validated() +
                    ['cashier_id' => Parent::user_id(), 'department_id' => $department_id] +
                    ['branch_id' => Parent::branch_id(), 'user_id' => $request->order_data_offline[0]['user_id']]
            );

            if ($request->customer_id) {
                $extra_non_point = $request->receipt_total - $request->loyalty_points;
                $loyalty_rate =  Auth::user()->branch->loyalty_points_rate;
                $loyalty_points = $loyalty_rate * $extra_non_point;
                Customer::where(['id' => $request->customer_id])->increment("redeemed_loyalty_points", $request->loyalty_points);
                Customer::where(['id' => $request->customer_id])->increment("loyalty_points", $loyalty_points);
            }
            if ($request->cash_pay > 0) {
                $this->insertCashLedger($request, $receipt_no, $unique_code);
            }
            if ($request->card_pay > 0) {
                $this->insertCardLedger($request, $receipt_no, $unique_code);
            }
            if ($request->mpesa_pay > 0) {
                $this->insertMpesaLedger($request, $receipt_no, $unique_code);
            }
            if ($request->credit_pay > 0) {
                $this->insertCustomerCredit($request, $receipt_no, $unique_code);
            }
            $this->creditOutputVATAccount($request, $receipt_no, $request->total_vat, $unique_code);
            $this->creditInventoryAccount($request, $receipt_no, ($request->receipt_total - $request->total_vat), $unique_code);
            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];
                $rs = Stock::where('id', $value['stock_id'])->first();
                $purchaseDate = null;
                if (!empty($value['batch_no'])) {
                    PurchaseOrderReceivableWithBatch::withoutGlobalScope(BranchScope::class)->where(['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']])->increment('qty_sold', $value['qty']);

                    $batchres = PurchaseOrderReceivableWithBatch::withoutGlobalScope(BranchScope::class)
                        ->where('stock_id', $value['stock_id'])
                        ->where('batch_no', $value['batch_no'])
                        ->orderByDesc('created_at')       // or ->latest('id')
                        ->first();              // string|Carbon|null

                    // Normalize to date string (optional, depending on your column type/cast)
                    $purchaseDate = $batchres ? Carbon::parse($batchres->expiry_date)->toDateString() : null;
                }
                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_amount'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $receipt_no,
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $value['customer_name'],
                        'batch_no' => $value['batch_no'],
                        'discount' =>  $value['discount'],
                        'item_discount' =>  $rs->item_discount,
                        'purchase_date'        => $purchaseDate,
                        'supplier_id'        => $batchres ? $batchres->supplier_id : null,
                    ]
                        + Parent::branch_array()
                );

                //insert ledger

                if (Parent::isInventory($value['stock_id'])) {
                    //Inventory::where(['stock_id' => $value['stock_id'], 'department_id' => $department_id])->decrement('qty', $value['qty']);
                    Inventory::updateOrCreate(
                        ['stock_id' => $value['stock_id'], 'department_id' => $department_id, 'branch_id' => Parent::branch_id()],
                        ['qty' => DB::raw('qty -' . $value['qty'])]
                    );
                }
                POSOrder::where(['order_no' => $value['order_no']])->delete();
                dispatch(new UpdateCustomerPromoStatus($request->customer_id));
            }
            for ($i = 0; $i < Auth::user()->branch->print_copies; $i++) {

                if (Auth::user()->branch->print_type == 'server') {
                    $this->printReceipt($request, $receipt_no);
                }
            }
        }, 5);
        if (Auth::user()->branch->print_type == 'offline') {
            $res_details =   AllSaleDetailsReport::where('order_no', $receipt_no)->with(['stock', 'customer', 'branch', 'user'])->get();
            $res_total =   AllSalesTotalReport::where('receipt_no', $receipt_no)->with(['customer', 'branch', 'user'])->get();
            return response()->json(["res_details" => $res_details, 'res_total' => $res_total]);
        }
        return  $receipt_no;
    }
    function downloadPDF(Request $request, $receipt_no)
    {
        $currentTime = Carbon::now();
        $saleDate = $currentTime->toDateTimeString();
        $res = AllSaleDetailsReport::with([
            'stock',
            'unit',
            'branch',
            'user'
        ])->where("order_no", $receipt_no)->first();

        $res_order = AllSaleDetailsReport::with([
            'stock',
            'unit',
            'branch',
            'user'
        ])->where("order_no", $receipt_no)->get();
        $customer_info = AllSalesTotalReport::with([
            'customer'

        ])->where("order_no", $receipt_no)->first();
        $folderPath = public_path('pdf');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . "receipt" . '.pdf';

        if (file_exists($path)) {
            unlink($path);
        }
        $data = ['customer_info' => $customer_info, "res" => $res, 'res_order' => $res_order, 'saleDate' => $saleDate, 'request' => $request];
        $pdf = PDF::loadView('pdf.receipt', $data); //compact('data') 
        $pdf->save($path);
    }
    function creditInventoryAccount($request, $receipt_no, $value, $unique_code = null)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "Inventory"])->first();

        if (!$accounts_data) {
            return;
        }



        $insert_data = array(
            'other_account_name' => "Retail Sales",
            'account_id' => $accounts_data->id,
            'account_type' => 'POS. Inventory',
            'entry_code' => $receipt_no,
            'ref' => $receipt_no,
            'details' => " Sales",
            'credit_amount' => $value,
            'description' => "Retail Sales",
            'entry_date' => $request->paid_date,
            'unique_code' => $unique_code
        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function debitInventoryAccount($request, $receipt_no, $debit_amount, $unique_code = null)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "Inventory"])->first();

        if (!$accounts_data) {
            return;
        }



        $insert_data = array(
            'other_account_name' => "Retail Sales Return",
            'account_id' => $accounts_data->id,
            'account_type' => 'POS. Inventory Return',
            'entry_code' => $receipt_no,
            'ref' => $receipt_no,
            'details' => " Sales Exchange",
            'debit_amount' => $debit_amount,
            'description' => "Retail Sales Return",
            'entry_date' => $request->paid_date,
            'unique_code' => $unique_code

        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function creditOutputVATAccount(Request $request, $receipt_no, $total_vat, $unique_code = null)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "output VAT"])->first();
        if ($total_vat == 0) {
            return;
        }
        if (!$accounts_data) {
            return;
        }


        $insert_data = array(
            'other_account_name' => "Customer",
            'account_id' => $accounts_data->id,
            'account_type' => 'Output VAT (SALES)',
            'entry_code' => $receipt_no,
            'ref' => $receipt_no,
            'details' => "Output VAT ( RETAIL SALES)",
            'credit_amount' => $total_vat,
            'description' => "POS Sales VAT",
            'entry_date' => $request->paid_date,
            'unique_code' => $unique_code

        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function insertCashLedger(Request $request, $receipt_no, $unique_code = null)
    {

        $account_id =  Auth::user()->branch->account_id;
        if (!$account_id) {
            return;
        }

        $cash_amount = $request->cash_pay +
            $request->receipt_balance;


        $insert_data = array(
            'other_account_name' => "Customer",
            'account_id' =>  $account_id,
            'account_type' => 'Cash Payment',
            'entry_code' => $receipt_no . ' / ' . $request->payment_ref,
            'ref' => $receipt_no . ' / ' . $request->payment_ref,
            'details' => "POS SALE",
            'debit_amount' => $cash_amount,
            'description' => "Cash Payment",
            'entry_date' => $request->paid_date,
            'unique_code' => $unique_code

        );
        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function debitOutputVATAccount(Request $request, $vat_amount, $receipt_no, $unique_code = null)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "output VAT"])->first();
        if ($vat_amount == 0) {
            return;
        }
        if (!$accounts_data) {
            return;
        }


        $insert_data = array(
            'other_account_name' => "Customer",
            'account_id' => $accounts_data->id,
            'account_type' => 'Input VAT (RETAIL RETURN)',
            'entry_code' => $receipt_no,
            'ref' => $receipt_no,
            'details' => "Input VAT (RETAIL RETURN )",
            'debit_amount' => $vat_amount,
            'description' => "POS RETURN VAT",
            'entry_date' => $request->paid_date,
            'unique_code' => $unique_code

        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function insertCardLedger(Request $request, $receipt_no, $unique_code = null)
    {

        if (!$request->card_terminal_account_id) {
            return;
        }
        $res = CardTerminalAccount::where(['id' => $request->card_terminal_account_id])->first();

        $insert_data = array(
            'other_account_name' => "Customer",
            'account_id' => $res->account_id,
            'account_type' => 'Card Payment',
            'entry_code' => $receipt_no . ' / ' . $request->payment_ref,
            'ref' => $receipt_no . ' / ' . $request->payment_ref,
            'details' => "POS SALE",
            'debit_amount' => $request->card_pay,
            'description' => "Card Payment",
            'entry_date' => $request->paid_date,
            'unique_code' => $unique_code
        );
        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function insertCustomerCredit($request, $receipt_no, $unique_code = null)
    {
        $insert_data = array(

            'customer_id' => $request->customer_id,
            'ref' => $receipt_no,
            'type' => 'IN',
            'entry_date' => Parent::currentDate(),
            'description' => 'Retail Credit Sale',
            'debit' => $request->credit_pay,
            'amount_due' => 0,
            'balance' => 0,
            'unique_code' => $unique_code
        );
        CustomerLedger::create($insert_data + Parent::user_branch_id());
    }
    function insertMpesaLedger(Request $request, $receipt_no, $unique_code = null)
    {
        $mpesa_account_id =  Auth::user()->branch->mpesa_account_id;
        if (!$mpesa_account_id) {
            return;
        }




        $insert_data = array(
            'other_account_name' => "Customer",
            'account_id' =>  $mpesa_account_id,
            'account_type' => 'Mpesa Payment',
            'entry_code' => $receipt_no . ' / ' . $request->payment_ref,
            'ref' => $receipt_no . ' / ' . $request->payment_ref,
            'details' => "POS SALE",
            'debit_amount' => $request->mpesa_pay,
            'description' => "Mpesa Payment",
            'entry_date' => $request->paid_date,
            'unique_code' => $unique_code

        );
        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    public function testPrint(Request $request)
    {
        $connector = new WindowsPrintConnector($request->res_items[0]['branch']['invoice_printer']);
        $printer = new Printer($connector);

        $fonts = [
            Printer::FONT_A,
            Printer::FONT_B,
            Printer::FONT_C
        ];
        $copies = $request->res_items[0]['branch']['print_copies'];
        for ($i = 0; $i < $copies; $i++) {
            // Business Name - Large, Centered, Bold
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setFont($fonts[1]);
            $printer->setTextSize(2, 2);
            $printer->text($request->res_items[0]['branch']['business_name'] . "\n");

            // Address and Contact Info
            $printer->setEmphasis(false);
            $printer->setFont($fonts[1]);
            $printer->setTextSize(1.5, 1.5);
            $printer->text($request->res_items[0]['branch']['address'] . "\n");
            $printer->text($request->res_items[0]['branch']['postal_address'] . "\n");
            $printer->text($request->res_items[0]['branch']['phone_no'] . "\n");
            $printer->text($request->res_items[0]['branch']['till_no'] . "\n");
            $printer->text(Parent::currentDateTime() . "\n");
            $printer->text("------------------------------------\n");

            // Sale Receipt Title
            $printer->setEmphasis(true);
            $printer->setFont($fonts[1]);
            $printer->setTextSize(2, 1);
            $printer->text("SALE RECEIPT\n");
            $printer->text("------------------------------------\n");

            // Receipt Details
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1, 1);
            $printer->setFont($fonts[1]);
            $printer->text("Document PIN: " . $request->res_items[0]['branch']['kra_pin'] . "\n");
            $printer->text("Receipt No: " . $request->res_items[0]['order_no'] . "\n");
            if ($request->res_items[0]['customer_id']) {
                $printer->text("Customer: " . $request->res_items[0]['customer']['company_name'] . "\n");
                $printer->text("Customer PIN: " . $request->res_items[0]['customer']['pin'] . "\n");
            }
            $printer->text("Served By: " . $request->res_items[0]['user']['name'] . "\n");
            $printer->text("---------------------------------------\n");

            // Table Header for Items
            $printer->setEmphasis(true);
            $printer->text("QTY  ITEM                   @COST    TOTAL\n");
            $printer->setEmphasis(false);
            $printer->text("----------------------------------------\n");

            // Items Loop
            $bill_total = 0;
            foreach ($request->order_info as $value) {
                $bill_total += $value['row_total'];
                $printer->setFont($fonts[1]);
                $printer->text(
                    str_pad($value['qty'], 4) .
                        str_pad(wordwrap($value['stock']['product_name'], 20, "\n", true), 20) .
                        str_pad(number_format($value['price'], 2), 12, " ", STR_PAD_LEFT) .
                        str_pad(number_format($value['row_total'], 2), 16, " ", STR_PAD_LEFT) .
                        "\n"
                );
            }
            $printer->text("--------------------------------------------\n");

            // Payments
            $printer->setFont($fonts[1]);
            $printer->setEmphasis(true);
            if ($request->res_items[0]['card_pay'] > 0) {
                $printer->text("CARD PAYMENT:  " . number_format($request->res_items[0]['card_pay'], 2) . "\n");
            }
            if ($request->res_items[0]['cash_pay'] > 0) {
                $printer->text("CASH PAYMENT:  " . number_format($request->res_items[0]['cash_pay'], 2) . "\n");
                $printer->text("CASH BALANCE:  " . number_format($request->res_items[0]['receipt_balance'], 2) . "\n");
            }
            if ($request->res_items[0]['mpesa_pay'] > 0) {
                $printer->text("M-PESA PAYMENT:  " . number_format($request->res_items[0]['mpesa_pay'], 2) . "\n");
            }
            $printer->text("VAT AMOUNT:    " . number_format($request->res_items[0]['total_vat'], 2) . "\n");
            $printer->text("RECEIPT TOTAL: " . number_format($request->res_items[0]['receipt_total'], 2) . "\n");

            $printer->text("--------------------------------------------\n");

            // Closing Message
            $printer->setFont($fonts[1]);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 1);
            $printer->text($request->res_items[0]['branch']['footer_text'] . "\n");
            $printer->text("\n\n");

            // Cut and Close
            $printer->cut();
        }
        $printer->close();
    }
    public function printReceipt(Request $request, $receipt_no, $responseData = null)
    {

        $res = AllSaleDetailsReport::with([
            'stock',
            'unit',
            'branch',
            'user'
        ])->where("order_no", $receipt_no)->first();

        $res_order = AllSaleDetailsReport::with([
            'stock',
            'unit',
            'branch',
            'user'
        ])->where("order_no", $receipt_no)->get();
        $customer_info = AllSalesTotalReport::with([
            'customer'

        ])->where("order_no", $receipt_no)->first();
        // Enter the share name for your USB printer here
        //return ($res);

        if (!$res->branch->invoice_printer) {
            return;
        }
        $connector = null;
        $connector = new WindowsPrintConnector($res->branch->invoice_printer);

        $printer = new Printer($connector);
        $fonts = array(
            Printer::FONT_A,
            Printer::FONT_B,
            Printer::FONT_C
        );
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->setFont($fonts[1]);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($res->branch->business_name . " \n");


        $printer->setTextSize(1, 1);
        $printer->setFont($fonts[0]);
        $printer->text($res->branch->address . "\n");
        $printer->text($res->branch->postal_address . "\n");

        //$printer->text($res->branch->phone_no . "\n");
        $printer->text(Parent::currentDateTime() . "\n");
        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);

        $printer->text('Sale Receipt' . "\n\n");

        $printer->setTextSize(1, 1);
        $printer->setFont($fonts[0]);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text('Document PIN: ' . Auth::user()->branch->kra_pin . ' ' . "\n");
        $printer->text('Receipt No: ' . $receipt_no . ' ' . "\n");
        if ($customer_info->customer->company_name) {
            $printer->text('Customer : ' . $customer_info->customer->company_name . ' ' . "\n");
            $printer->text('Customer PIN : ' . $customer_info->customer->pin . ' ' . "\n");
        }
        $printer->text('Served By: ' . $res->user->name . ' ' . "\n");

        $printer->setEmphasis(true);
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);
        $printer->text('QTY' . '  ' . 'Particulars' . '  ' . '@cost' . '  ' . ' Amt' . "\n\n");
        $bill_total = 0;
        $total_vat = 0;
        foreach ($res_order as $value) {
            $bill_total += $value['row_total'];
            $total_vat += $value['row_vat'];
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);
            $printer->setFont($fonts[0]);
            $printer->text($value['qty'] . '    ' . $value['stock']['product_name']  . "\n");

            $printer->text($value['notes'] . "\n");
            $printer->setFont($fonts[1]);
            $printer->setTextSize(2, 1);
            $printer->text($value['qty'] . '      X       ' . $value['price'] . '      ' . number_format($value['row_total']) . "\n");
        }
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("\n\n" . '==============================' . "\n");

        if ($request->card_pay > 0) {
            $printer->text('CARD PAYMENT:  ' . number_format($request->card_pay, 2) . "\n");
        }

        if ($request->cash_pay > 0) {
            $printer->text('CASH PAYMENT:  ' . number_format($request->cash_pay, 2) . "\n");
            if ($request->receipt_balance != 0) {
                $printer->text('Change:  ' . number_format($request->receipt_balance, 2) . "\n");
            }
        }
        if ($request->mpesa_pay > 0) {
            $printer->text('M-PESA PAYMENT:  ' . number_format($request->mpesa_pay, 2) . "\n");
        }


        $printer->text('VAT AMOUNT:  ' . number_format($total_vat, 2) . "\n");
        $printer->text('RECEIPT TOTAL:  ' . number_format($bill_total, 2) . "\n");

        $printer->text("\n\n" . '==============================' . "\n");
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);
        if ($res_order[0]['delivery_time']) {
            $printer->text("DELIVERY TIME:" . $res_order[0]['delivery_time'] . "\n");
        }
        $printer->setFont($fonts[0]);
        $printer->setTextSize(1, 1);
        $printer->text($res->branch->till_no . "\n");
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);
        $printer->text('CUSTOMER INFO' . "\n");
        $printer->setFont($fonts[0]);
        $printer->setTextSize(1, 1);
        $printer->text("Cashier:" . '  ' . Auth::user()->name . "\n\n");
        if ($responseData) {

            if (Auth::user()->branch->etims_type == 'vscu') {

                $date = DateTime::createFromFormat('YmdHis', $responseData['data']['scuReceiptDate']);
                $level = Printer::QR_ECLEVEL_H;
                $printer->qrCode($responseData['data']['invoiceVerificationUrl']);
                $printer->text("\n");
                $printer->text('CONTROL UNIT INFO ' . "\n");
                $printer->text('Signature: ' . $responseData['data']['signature'] . "\n");
                $printer->text('SCU Receipt No: ' .  $responseData['data']['scdcId'] . '-' . $responseData['data']['scuReceiptNo'] . "\n");
                $printer->text('CU Timestamp:' . $date->format('Y-m-d H:i:s') . "\n");
            }
        }
        $printer->text($res->branch->footer_text . "\n");

        $printer->selectPrintMode();
        $printer->text("\n");
        $printer->cut();

        /* Close printer */
        $printer->close();
    }


    public function dailySalesReport()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSalesTotalReport::with([
            'customer',
            'user',
            'cashier',
        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('paid_date', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        $main_query->selectRaw('*,
        SUM(receipt_total) AS sum_receipt_total,
        SUM(cash_pay+receipt_balance) AS sum_cash_pay,
        SUM(mpesa_pay) AS sum_mpesa_pay,
        SUM(card_pay) AS sum_card_pay,
        SUM(credit_pay) AS sum_credit_pay,
        SUM(total_vat) AS sum_total_vat
        ')
            ->when(request('sort_sum_receipt_total'), function ($query) {
                $query->orderBy('sum_receipt_total', request('sort_sum_receipt_total'));
            })
            ->when(request('sort_vat'), function ($query) {
                $query->orderBy('sum_total_vat', request('sort_vat'));
            })
            ->when(request('sort_cash_paid'), function ($query) {
                $query->orderBy('sum_cash_pay', request('sort_cash_paid'));
            })
            ->when(request('sort_mpesa_paid'), function ($query) {
                $query->orderBy('sum_mpesa_pay', request('sort_mpesa_paid'));
            })
            ->when(request('sort_card_paid'), function ($query) {
                $query->orderBy('sum_card_pay', request('sort_card_paid'));
            });

        $main_query->latest('created_at')->groupBy('paid_date');

        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        } else {
            $res = $main_query->get();
        }
        return response()->json($res);
    }
    public function profitLossDetails()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSaleDetailsReport::with([
            'customer',
            'user',
            'cashier',
            'stock',
            'unit'
        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {
            $query->whereBetween(DB::raw("DATE(created_at)"), [$from, $to]);
        });

        $main_query->when(request('search', '') != '', function ($query) {
            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('stock', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
        });



        // Sorting options
        $main_query
            ->when(request('sort_qty'), function ($query) {
                $query->orderBy('qty', request('sort_qty'));
            })
            ->when(request('sort_purchases'), function ($query) {
                $query->orderBy('cost_total', request('sort_purchases'));
            })
            ->when(request('sort_total'), function ($query) {
                $query->orderBy('row_total', request('sort_total'));
            });
        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }
        // Apply HAVING clause to filter only records where sum_qty > 0
        $main_query->where('stock_id', request('stock_id'))->havingRaw('qty > 0')->latest('created_at');

        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        } else {
            $res = $main_query->get();
        }

        return response()->json($res);
    }
    public function profitLossSummary()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSaleDetailsReport::with([
            'customer',
            'user',
            'cashier',
            'stock',
            'unit'
        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to]);
        });

        $main_query->when(request('search', '') != '', function ($query) {
            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('stock', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
        });

        $main_query->selectRaw('*, 
            SUM(row_total) AS sum_row_total, 
            SUM(cost_price*qty) AS sum_cost_total, 
            SUM(row_vat) AS sum_row_vat, 
            SUM(qty) AS sum_qty, 
            (SUM(row_total) - SUM(cost_price*qty)) AS profit_loss, 
            (((SUM(row_total) - SUM(cost_total)) * 100) / SUM(row_total)) AS profit_margin
        ');

        // Sorting options
        $main_query->when(request('sort_margin'), function ($query) {
            $query->orderBy('profit_margin', request('sort_margin'));
        })
            ->when(request('sort_profit_loss'), function ($query) {
                $query->orderBy('profit_loss', request('sort_profit_loss'));
            })
            ->when(request('sort_qty'), function ($query) {
                $query->orderBy('sum_qty', request('sort_qty'));
            })
            ->when(request('sort_purchases'), function ($query) {
                $query->orderBy('sum_cost_total', request('sort_purchases'));
            })
            ->when(request('sort_total'), function ($query) {
                $query->orderBy('sum_row_total', request('sort_total'));
            });
        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }
        $main_query->when(request('branch_id') != '', function ($query) {
            $query->where('branch_id', request('branch_id'));
        });
        // Apply HAVING clause to filter only records where sum_qty > 0
        $main_query->groupBy('stock_id')->havingRaw('SUM(qty) > 0')->latest('created_at');

        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        } else {
            $res = $main_query->get();
        }

        return response()->json($res);
    }



    public function profitLossTotals()
    {

        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSaleDetailsReport::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('stock', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
        });
        $main_query->selectRaw('
        SUM(row_total) AS sum_row_total,
        SUM(cost_price*qty) AS sum_cost_total,
        SUM(row_vat) AS sum_row_vat,
        SUM(qty) AS sum_qty
      
        ');

        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }
        $main_query->when(request('branch_id') != '', function ($query) {
            $query->where('branch_id', request('branch_id'));
        });
        $res = $main_query->first();

        return response()->json($res);
    }

    public function fetchSaleItems()
    {
        $main_query = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)->with([
            'user',
            'cashier',
            'unit',
            'stock',
            'customer'
        ])->where('order_no', request('order_no'));
        $data = $main_query->get();
        return response()->json($data);
    }

    public function fetchArchivedSaleItems()
    {
        $main_query = AllSaleDetailsReport::onlyTrashed()->with([
            'user',
            'cashier',
            'unit',
            'stock',
            'customer'
        ])->where('order_no', request('order_no'));
        $data = $main_query->get();
        return response()->json($data);
    }

    public function detailsReport()
    {
        ini_set('max_execution_time', 2400);
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSaleDetailsReport::with([
            'user',
            'cashier',
            'unit',
            'stock',
            'customer',
            'supplier'
        ]);
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('order_date', [$from, $to]);
        });




        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('batch_no', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('stock', function ($q) {
                    $q->where('product_name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('supplier', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        })
            ->when(request('sort_qty'), function ($query) {
                $query->orderBy('qty', request('sort_qty'));
            })
            ->when(request('sort_total'), function ($query) {
                $query->orderBy('row_total', request('sort_total'));
            })
            ->when(request('sort_price'), function ($query) {
                $query->orderBy('price', request('sort_price'));
            })
            ->when(request('sort_discount'), function ($query) {
                $query->orderBy('discount', request('sort_discount'));
            })
            ->when(request('sort_cost_price'), function ($query) {
                $query->orderBy('cost_price', request('sort_cost_price'));
            });
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }
        $main_query->latest();

        if (request('page') > 0) {
            $res = $main_query->paginate(100);
        } else {
            $res = $main_query->get();
        }
        return response()->json($res);
    }




    function fetchGroupedCategory()
    {



        $main_query = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)->join("stocks", "stocks.id", '=', 'all_sale_details_reports.stock_id')
            ->join('product_categories', 'product_categories.id', "=", "stocks.product_category_id");
        if (request('from') != "" && request('to') != "") {
            $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();
            $main_query->whereBetween('order_date', [$from, $to]);
        }
        $main_query->selectRaw('product_categories.name AS category_name,
            stocks.product_category_id AS product_category_id,
            all_sale_details_reports.stock_id,SUM(all_sale_details_reports.row_total) AS sum_row_total')
            ->when(request('sort_sum_main_total'), function ($query) {
                $query->orderBy('sum_row_total', request('sort_sum_main_total'));
            })
            ->groupBy(['stocks.product_category_id']);
        $main_query->where('all_sale_details_reports.branch_id', Parent::branch_id());
        $res1 = $main_query->get();
        $category_array = array();

        foreach ($res1 as $value) {

            $raw_query =  AllSaleDetailsReport::with(['stock'])
                ->whereHas('stock', function ($query) use ($value) {
                    $query->where('product_category_id', $value['product_category_id']);
                });
            if (request('from') != "" && request('to') != "") {
                $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
                $to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();
                $raw_query->whereBetween('order_date', [$from, $to]);
            }
            $raw_query->selectRaw("stock_id,price,
            SUM(row_total) AS sum_row_total,
            
            SUM(qty) AS sum_qty")
                ->when(request('sort_sum_row_total'), function ($query) {
                    $query->orderBy('sum_row_total', request('sort_sum_row_total'));
                })
                ->when(request('sort_sum_qty'), function ($query) {
                    $query->orderBy('sum_qty', request('sort_sum_qty'));
                })

                ->groupBy(['stock_id']);

            $res3 = $raw_query->get()->toArray();

            $category_array[] = array(
                "category_id" => $value['product_category_id'],
                "category_name" => $value['category_name'],
                'category_total' => $value['sum_row_total'],
                "data" => $res3
            );
        }
        return response()->json($category_array);
    }

    function fetchGroupedUser()
    {
        $main_query = AllSalesTotalReport::with(['user', 'cashier',]);
        if (request('from') != "" && request('to') != "") {
            $from =  request('from');
            $to = request('to');
            $main_query->whereBetween('created_at', [$from, $to]);
        }
        $main_query->when(request("search") != "", function ($q) {
            $q->where("order_type", request("search"))
                ->orWhereHas("cashier", function ($query) {
                    $query->where("name", "LIKE", '%' . request('search') . '%');
                });
        });
        $main_query->when(request("branch_id") != "", function ($q) {
            $q->where("branch_id", request("branch_id"));
        });



        if (request('user_activity') == "sales") {

            $main_query->groupBy(["user_id"]);
        } else {
            $main_query->groupBy(["cashier_id"]);
        }
        if (!Parent::checkForPermission()) {
            $main_query->where('user_id', Parent::user_id());
        }

        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }
        $main_query->selectRaw("*,SUM(receipt_total) AS sum_receipt_total")
            ->when(request('sort_sum_receipt_total'), function ($query) {
                $query->orderBy('sum_receipt_total', request('sort_sum_receipt_total'));
            });
        $res = $main_query->get();



        return response()->json($res);
    }
    function allSalesQuery()
    {
        $user_id = request('user_id');
        $cashier_id = request('cashier_id');
        $customer_id = request('customer_id');
        $from = request('from') ? date(request('from')) : null;
        $to = request('to') ? date(request('to')) : null;
        $from_time = request('from_time') ? date(request('from_time')) : null;
        $to_time = request('to_time') ? date(request('to_time')) : null;
        $order_type = request('order_type');
        $report_type = request('report_type'); // Expecting daily, bi-weekly, etc.

        $main_query = AllSalesTotalReport::with([
            'branch',
            'user',
            'cashier',
            'customer',
            'card_terminal_account',
            'details.stock'

        ]);

        // Apply filters
        $main_query->when($order_type != '', function ($query) use ($order_type) {
            $query->where('order_type', $order_type);
        });

        $main_query->when($user_id != '', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });

        $main_query->when($customer_id != '', function ($query) use ($customer_id) {
            $query->where('customer_id', $customer_id);
        });

        $main_query->when($cashier_id != '', function ($query) use ($cashier_id) {
            $query->where('cashier_id', $cashier_id);
        });

        $main_query->when(request('branch_id') != '', function ($query) {
            $query->where('branch_id', request('branch_id'));
        });

        // Handle datetime range filtering based on report type
        $main_query->when($report_type, function ($query) use ($report_type) {
            $now = Carbon::now()->setTimezone('UTC'); // Ensure UTC timezone is set

            switch ($report_type) {
                case 'daily':
                    $query->whereDate('created_at', $now->toDateString());
                    break;

                case 'bi-weekly':
                    $startOfWeek = $now->copy()->startOfWeek(); // Start of the week
                    $midOfWeek = $startOfWeek->copy()->addDays(7); // Mid-week for bi-weekly
                    $query->whereBetween('created_at', [$startOfWeek->toDateTimeString(), $midOfWeek->toDateTimeString()]);
                    break;

                case 'weekly':
                    $query->whereBetween('created_at', [
                        $now->startOfWeek()->toDateTimeString(),
                        $now->endOfWeek()->toDateTimeString()
                    ]);
                    break;

                case 'monthly':
                    $startOfMonth = $now->copy()->startOfMonth()->toDateTimeString();
                    $endOfMonth = $now->copy()->endOfMonth()->toDateTimeString();
                    $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                    break;

                case 'quarterly':
                    $startOfQuarter = $now->copy()->startOfQuarter()->toDateTimeString();
                    $endOfQuarter = $now->copy()->endOfQuarter()->toDateTimeString();
                    $query->whereBetween('created_at', [$startOfQuarter, $endOfQuarter]);
                    break;

                case 'yearly':
                    $startOfYear = $now->copy()->startOfYear()->toDateTimeString();
                    $endOfYear = $now->copy()->endOfYear()->toDateTimeString();
                    $query->whereBetween('created_at', [$startOfYear, $endOfYear]);
                    break;

                default:
                    // Optionally, log or handle invalid report types
                    Log::warning('Invalid report type provided: ' . $report_type);
                    break;
            }
        });


        // Handle datetime range filtering (custom date range)
        $main_query->when($from && $to, function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to]);
        });

        $main_query->when($from_time && $to_time, function ($query) use ($from_time, $to_time) {
            $query->whereBetween('created_at', [$from_time, $to_time]);
        });

        // Handle search filter
        $main_query->when(request('search', '') != '', function ($query) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'LIKE', "%$search%")
                    ->orWhere('receipt_no', 'LIKE', "%$search%")
                    ->orWhere('payment_ref', 'LIKE', "%$search%")
                    ->orWhere('receipt_total', 'LIKE', "%$search%")
                    ->orWhere('mpesa_pay', 'LIKE', "%$search%")
                    ->orWhere('card_pay', 'LIKE', "%$search%")
                    ->orWhere('credit_pay', 'LIKE', "%$search%")
                    ->orWhere('total_vat', 'LIKE', "%$search%")
                    ->orWhereRaw("cash_pay + receipt_balance LIKE ?", ["%$search%"])
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('company_name', 'LIKE', "%$search%");
                    })

                    ->orWhereHas('cashier', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    });


                // ->orWhereHas('details.stock', function ($q) use ($search) {
                //     $q->where('product_name', 'LIKE', "%$search%"); // Include product name in search
                // });
            });
        });

        return $main_query;
    }

    function fetchAllSales()
    {


        $main_query = $this->allSalesQuery();

        $main_query->when(request('sort_total'), function ($query) {
            $query->orderBy('receipt_total', request('sort_total'));
        })
            ->when(request('sort_vat'), function ($query) {
                $query->orderBy('total_vat', request('sort_vat'));
            })
            ->when(request('sort_cash_pay'), function ($query) {
                $query->orderBy('cash_pay', request('sort_cash_pay'));
            })
            ->when(request('sort_mpesa_pay'), function ($query) {
                $query->orderBy('mpesa_pay', request('sort_mpesa_pay'));
            })
            ->when(request('sort_card_pay'), function ($query) {
                $query->orderBy('card_pay', request('sort_card_pay'));
            })
            ->when(request('sort_credit_pay'), function ($query) {
                $query->orderBy('credit_pay', request('sort_credit_pay'));
            })
            ->when(request('sort_receipt_no'), function ($query) {
                $query->orderBy('receipt_no', request('sort_receipt_no'));
            })
            ->when(request('sort_created_at'), function ($query) {
                $query->orderBy('created_at', request('sort_created_at'));
            })
            ->when(request('sort_discount_pay'), function ($query) {
                $query->orderBy('discount_pay', request('sort_discount_pay'));
            });
        if (request('is_non_etims')) {

            $main_query->whereNull('sale_detail_url');
        }
        if (!Parent::checkForPermission()) {
            if (!request('is_non_etims')) {
                $main_query->where('user_id', Parent::user_id());
            }
        }
        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }
        if (request('sales_filter') == 'vscu_etims_sales') {
            $main_query->whereNotNull('invoiceVerificationUrl');
        }
        if (request('sales_filter') == 'non_vscuetims_sales') {
            $main_query->whereNull('invoiceVerificationUrl');
        }
        if (request('currentRoute') == 'mpesa_cashout_report') {
            $main_query->where('mpesa_pay', '>', 0);
        }
        $main_query->latest('created_at');

        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        } else {
            $res = $main_query->get();
        }
        return response()->json($res);
    }
    function searchCustomerSales()
    {
        $main_query = $this->allSalesQuery();
        $main_query->selectRaw('*,
    SUM(receipt_total) AS sum_receipt_total,
    SUM(cash_pay+receipt_balance) AS sum_cash_pay,
    SUM(mpesa_pay) AS sum_mpesa_pay,
    SUM(card_pay) AS sum_card_pay,
    SUM(credit_pay) AS sum_credit_pay,
    SUM(total_vat) AS sum_total_vat,
    SUM(discount_pay) AS sum_discount_pay,
      SUM(loyalty_points) AS sum_loyalty_points
    ');
        $main_query->withoutGlobalScope(BranchScope::class);

        $main_query->whereNotNull("customer_id")->groupBy('customer_id');
        $main_query->when(request('sort_total'), function ($query) {
            $query->orderBy('sum_receipt_total', request('sort_total'));
        });
        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        } else {
            $res = $main_query->get();
        }
        return response()->json($res);
    }

    function searchCustomerSalesItems()
    {
        $from = request('from') ? date(request('from')) : null;
        $to = request('to') ? date(request('to')) : null;
        $main_query = AllSaleDetailsReport::with(["stock", "user", 'branch'])->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('stock', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%')
                        ->orWhere('code', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('branch', function ($q) {
                    $q->where('branch', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
        });
        // $main_query->orderBy("order_no");
        $main_query->when(request('customer_id'), function ($query) {
            $query->where('customer_id', request('customer_id'));
        })
            ->when(request('sort_created_at'), function ($query) {
                $query->orderBy('created_at', request('sort_created_at'));
            })
            ->when(request('sort_row_total'), function ($query) {
                $query->orderBy('row_total', request('sort_row_total'));
            })
            ->when(request('sort_selling_price'), function ($query) {
                $query->orderBy('price', request('sort_selling_price'));
            })
            ->when(request('sort_qty'), function ($query) {
                $query->orderBy('qty', request('sort_qty'));
            });
        $main_query->withoutGlobalScope(BranchScope::class);
        $data = request('page') > 0 ?   $main_query->paginate(60) : $main_query->get();
        return $data;
    }


    function fetchAllSalesArchived()
    {
        $user_id = request('user_id');
        $cashier_id = request('cashier_id');
        $from = date(request('from'));
        $to = date(request('to'));
        $order_type = request('order_type');
        $main_query = AllSalesTotalReport::onlyTrashed()->with([
            'user',
            'cashier',
            'customer',
            'card_terminal_account'
        ]);
        $main_query->when($order_type != '', function ($query) use ($order_type) {

            $query->where('order_type',  $order_type);
        });
        $main_query->when($user_id != '', function ($query) use ($user_id) {

            $query->where('user_id',  $user_id);
        });
        $main_query->when($cashier_id != '', function ($query) use ($cashier_id) {

            $query->where('cashier_id',  $cashier_id);
        });
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });

        if (request('is_non_etims')) {

            $main_query->whereNull('sale_detail_url');
        }

        $main_query->latest('created_at');

        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        } else {
            $res = $main_query->get();
        }
        return response()->json($res);
    }
    function fetchAllSaleTotal()
    {
        $main_query = $this->allSalesQuery();
        if (request('is_non_etims')) {

            $main_query->whereNull('sale_detail_url');
        }
        if (!Parent::checkForPermission()) {
            if (!request('is_non_etims')) {
                $main_query->where('user_id', Parent::user_id());
            }
        }
        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }

        $main_query->selectRaw('
    SUM(receipt_total) AS sum_receipt_total,
    SUM(cash_pay+receipt_balance) AS sum_cash_pay,
    SUM(mpesa_pay) AS sum_mpesa_pay,
    SUM(card_pay) AS sum_card_pay,
    SUM(credit_pay) AS sum_credit_pay,
    SUM(total_vat) AS sum_total_vat,
    SUM(discount_pay) AS sum_discount_pay,
      SUM(loyalty_points) AS sum_loyalty_points
    ');

        $res = $main_query->first();

        return response()->json($res);
    }


    function cardPaymentQuery()
    {
        $user_id = request('user_id');
        $branch_id = request('branch_id');
        $cashier_id = request('cashier_id');
        $card_terminal_account_id = request('card_terminal_account_id');
        $from = date(request('from'));
        $to = date(request('to'));
        $order_type = request('order_type');
        $main_query = AllSalesTotalReport::with([
            'user',
            'cashier',
            'customer',
            'card_terminal_account.account',
            'branch'
        ]);
        if ($branch_id) {
            $main_query->where('branch_id', $branch_id);
        }

        $main_query->when($order_type != '', function ($query) use ($order_type) {

            $query->where('order_type',  $order_type);
        });
        $main_query->when($user_id != '', function ($query) use ($user_id) {

            $query->where('user_id',  $user_id);
        });
        $main_query->when($cashier_id != '', function ($query) use ($cashier_id) {

            $query->where('cashier_id',  $cashier_id);
        });
        $main_query->when($card_terminal_account_id != '', function ($query) use ($card_terminal_account_id) {

            $query->where('card_terminal_account_id',  $card_terminal_account_id);
        });
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        })->when(request('sort_total'), function ($query) {
            $query->orderBy('receipt_total', request('sort_total'));
        })->when(request('sort_card_pay'), function ($query) {
            $query->orderBy('card_pay', request('sort_card_pay'));
        });
        return $main_query;
    }
    function fetchCardPaymentReport()
    {
        $main_query = $this->cardPaymentQuery();
        if (!Parent::checkForPermission()) {
            //   $main_query->where('user_id', Parent::user_id());
        }
        if (Parent::showBranchPermission()) {
            // $main_query->withoutGlobalScope(BranchScope::class);
        }
        $main_query->withoutGlobalScope(BranchScope::class)->where('card_pay', '>', 0)->latest('created_at');

        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        } else {
            $res = $main_query->get();
        }
        return response()->json($res);
    }
    function fetchCardPaymentTotalReport()
    {
        $main_query = $this->cardPaymentQuery();
        if (!Parent::checkForPermission()) {
            //  $main_query->where('user_id', Parent::user_id());
        }
        if (Parent::showBranchPermission()) {
            // $main_query->withoutGlobalScope(BranchScope::class);
        }
        $main_query->withoutGlobalScope(BranchScope::class)->where('card_pay', '>', 0)->latest('created_at');
        $main_query->selectRaw('
    SUM(receipt_total) AS sum_receipt_total,
   
    SUM(card_pay) AS sum_card_pay
  
    ');
        $res = $main_query->first();

        return response()->json($res);
    }

    function groupedDeptTotals()
    {
        $main_query = AllSaleDetailsReport::with(["stock", "department"]);
        if (request('from') != "" && request('to') != "") {

            $from = request('from');
            $to = request('to');
            $main_query->whereBetween('created_at', [$from, $to]);
        }
        $main_query->selectRaw("*,SUM(row_total) AS sum_row_total")->groupBy('department_id');
        $res1 = $main_query->get();
        return response()->json($res1);
    }

    function settleCredit(Request $request)
    {
        $this->validate($request, [
            'card_pay' => 'numeric|required|gte:0',
            'cash_pay' => 'numeric|required|gte:0',
            'mpesa_pay' => 'numeric|required|gte:0',
            'id' => 'required',

        ]);
        AllSalesTotalReport::where(['id' => $request->id])->update([
            'cash_pay' => $request->cash_pay,
            'card_pay' => $request->card_pay,
            'mpesa_pay' => $request->mpesa_pay,
            'credit_pay' => 0
        ]);
        return true;
    }
    function reverseEtimsSale(Request $request)
    {


        $sale = AllSalesTotalReport::whereNotNull('sale_detail_url')->where('receipt_no', $request->order_data[0]['order_no'])->first();
        if ($sale) {
            for ($i = 0; $i < count($request->order_data); $i++) {
                $value = $request->order_data[$i];
                $stock = Stock::where(['id' => $value['stock_id']])->first();
                $items_array[] = array(
                    'id' => $stock->item_id,
                    'unit_price' => (float)$value['price'],
                    'quantity' => (float)$value['qty'],
                    'discount_rate' => 0,
                    "discount_amount" => 0
                );
            }
            $pay_method = "07";
            if ($sale->card_pay > 0) {
                $pay_method = "05";
            }
            if ($sale->mpesa_pay > 0) {
                $pay_method = "06";
            }
            if ($sale->cash_pay > 0) {
                $pay_method = "01";
            }
            if ($sale->credit_pay > 0) {
                $pay_method = "02";
            }
            $data = [];
            if ($sale->etims_customer_pin) {

                $data = array(
                    "customer_pin" => $sale->etims_customer_pin,
                    "customer_name" => $sale->etims_customer_name,
                    "trader_invoice_number" => $sale->receipt_no,
                    "receipt_type_code" => "R",
                    "payment_type_code" => $pay_method,
                    "invoice_status_code" => "02",
                    'original_invoice_number' => (int)$sale->receipt_number,
                    "items" => $items_array

                );
            } else {
                $data = array(
                    "trader_invoice_number" => $sale->receipt_no,
                    "receipt_type_code" => "R",
                    "payment_type_code" => $pay_method,
                    "invoice_status_code" => "02",
                    'original_invoice_number' => (int)$sale->receipt_number,
                    "items" => $items_array

                );
            }

            try {
                $url = Parent::digitaxBaseUrl() . 'sales';
                $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


                $responseData = $response->json();


                $statusCode = $response->status();

                if ($statusCode == 201) {


                    return  response()->json($responseData);
                }
                if ($statusCode == 400) {
                    return response()->json([$responseData], 400);
                }
            } catch (Exception) {
            }
        } else {
            $this->deleteSale($request, null);
            return response()->json("", 201);
        }
    }
    function validateEtimsCreditnote(Request $request)
    {
        Validator::validate($request->all(), [
            'digitax_id' => 'required',
        ]);
        try {
            $getResultsUrl = Parent::digitaxBaseUrl() . 'sales/' . $request->digitax_id;
            $response = Http::withHeaders(Parent::apiHeaders())->get($getResultsUrl, null);
            $responseData = $response->json();
            $statusCode = $response->status();
            if ($statusCode == 200) {

                if ($responseData['queue_status'] == 'completed') {

                    $this->deleteSale($request, $responseData);
                    return response()->json($responseData, 200);
                } else {
                    return response()->json($responseData, 200);
                }
            }
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
    function deleteSale($request, $responseData)
    {

        DB::transaction(function () use ($request, $responseData) {
            $insert_etims = null;
            if ($responseData) {
                $insert_etims = $this->updateEtimsToSale($request->all(), $responseData);
            }
            $creditNo = Parent::generateDocNumber("CR");

            for ($i = 0; $i < count($request->order_data); $i++) {
                $value = $request->order_data[$i];
                $rs = Stock::where('id', $value['stock_id'])->first();

                AllSaleDetailsReport::where('id', $value['id'])->delete();

                if (Parent::isInventory($value['stock_id'])) {
                    Inventory::where(['stock_id' => $value['stock_id'], 'department_id' => $value['department_id']])->increment('qty', $value['qty']);
                }
                AllSalesTotalReport::where('receipt_no', $value['order_no'])->delete();

                $insert = [

                    'stock_id' => $value['stock_id'],
                    'all_sale_details_report_id' => $value['id'],
                    'receipt_qty' => $value['qty'],
                    'qty' => $value['qty'],
                    'row_total' => $value['row_total'],
                    'row_vat' => $value['row_vat'],
                    'credit_sp' => $value['price'],
                    'credit_vat' => $value['row_vat'],
                    'credit_amount' => $value['row_total'],
                    'credit_date' => Parent::currentDate(),
                    'receipt_no' => $value['order_no'],
                    'credit_no' => $creditNo,
                    'status' => 'completed'
                ];

                if ($insert_etims) {

                    POSCredit::create($insert + $insert_etims + Parent::user_branch_id());
                } else {

                    POSCredit::create($insert + Parent::user_branch_id());
                }

                GeneralLedger::where('entry_code', $value['order_no'])->delete();
            }
        }, 5);
    }
    function querySummaryStatement()
    {
        $user_id = request('user_id');
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  AllSaleDetailsReport::with(['user', 'stock'])->when(request('search', '') != '', function ($query) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            })->orWhereHas('stock', function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%');
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('order_date', [$from, $to]);
                }
            )

            ->when($user_id != '', function ($query) use ($user_id) {

                $query->where('user_id',  $user_id);
            })
            ->selectRaw('*,
            SUM(row_total) AS sum_receipt_total,
            SUM(qty) AS total_qty
           ')->groupBy('stock_id');
        if (request('show_desc') == "true") {

            $main_query->orderBy('total_qty', 'DESC');
        } else {

            $main_query->orderBy('total_qty', 'ASC');
        }


        if (request('page') > 0) {
            $paginatedData = $main_query->paginate(50);
        } else {
            $paginatedData = $main_query->get();
        }
        return $paginatedData;
    }
    function parsedData()
    {
        $paginatedData = $this->querySummaryStatement();

        $data = [];
        $current_total = 0;
        foreach ($paginatedData as  $value) {

            $current_total += $value->debit;
            $current_total -= $value->credit;
            $current_qty = 0;
            $loc_query = Department::where('show_pos', 1)->first();
            if ($loc_query) {
                $inventory_res = Inventory::where(['stock_id' => $value->stock_id, 'department_id' => $loc_query->id])->first();
                if ($inventory_res) {
                    $current_qty = $inventory_res->qty;
                }
            }

            $data[] = [
                'id' => $value->id,
                'stock' => $value->stock,


                'sum_receipt_total' => $value->sum_receipt_total,
                'total_qty' => $value->total_qty,
                'current_qty' => $current_qty
            ];
        }
        return $data;
    }

    function saleSumaryWithActivity()
    {
        $data = $this->parsedData();
        $paginatedData = $this->querySummaryStatement();
        $currentPage = $paginatedData->currentPage();
        $perPage = $paginatedData->perPage();
        $total = $paginatedData->total();

        $paginatedResult = new LengthAwarePaginator(
            $data,
            $total,
            $perPage,
            $currentPage,
            ['path' => url()->current()]
        );

        return response()->json($paginatedResult);
    }
    function combineStockSummary()
    {

        $finalArray = [];
        $fromDate = request('from');
        $toDate = request('to');
        $stock = Stock::select('name', 'id', 'product_name')->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('product_name', 'LIKE', '%' . request('search') . '%');
            });
        })->get();
        $loc_query = Department::where('show_pos', 1)->first();
        if (!$loc_query) {
            return;
        }
        foreach ($stock as  $value) {
            $id = $value['id'];
            $inventory = Inventory::where(['stock_id' => $id, 'department_id' => $loc_query->id])
                ->first();
            $pos_sales = AllSaleDetailsReport::sumQtyAndPriceByStockId($id, $fromDate, $toDate);
            $invoice_sales = Invoice::sumQtyAndPriceByStockId($id, $fromDate, $toDate);
            $total_amount = ($pos_sales['sum_qty_and_price'] + $invoice_sales['sum_qty_and_price']);
            if ($total_amount <= 0) {
                continue;
            }
            if (!is_array($pos_sales) || !is_array($invoice_sales)) {
                // Handle the error appropriately, e.g., log the error, skip the iteration, etc.
                echo "Error fetching sales data for stock_id: {$value['id']}\n";
                continue; // Skip this iteration if data is not valid
            }
            $finalArray[] = array(
                'stock_id' => $value['id'],
                'pos_qty_sale' => $pos_sales['sum_qty'],
                'pos_total_sale' => isset($pos_sales['sum_qty_and_price']) ? $pos_sales['sum_qty_and_price'] : 0,
                'invoice_qty_sale' => $invoice_sales['sum_qty'],
                'invoice_total_sale' => $invoice_sales['sum_qty_and_price'],
                "total_qty" => ($pos_sales['sum_qty'] + $invoice_sales['sum_qty']),
                "total_amount" => ($pos_sales['sum_qty_and_price'] + $invoice_sales['sum_qty_and_price']),
                "product_name" => $value['product_name'],
                'current_qty' => $inventory ? $inventory->qty : 0
            );
        }


        $finalArray = array_filter($finalArray, 'is_array');

        if (request('sort_total_qty') == 'DESC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {
                return $b['total_qty'] - $a['total_qty'];
            });
        }
        if (request('sort_total_qty') == 'ASC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $a['total_qty'] <=> $b['total_qty'];
            });
        }


        if (request('sort_sum_retail_qty_and_price') == 'DESC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $b['pos_total_sale'] - $a['pos_total_sale'];
            });
        }
        if (request('sort_sum_retail_qty_and_price') == 'ASC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $a['pos_total_sale'] <=> $b['pos_total_sale'];
            });
        }


        if (request('sort_invoice_total') == 'DESC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $b['invoice_total_sale'] - $a['invoice_total_sale'];
            });
        }
        if (request('sort_invoice_total') == 'ASC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $a['invoice_total_sale'] <=> $b['invoice_total_sale'];
            });
        }

        if (request('sort_invoice_qty') == 'DESC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $b['invoice_qty_sale'] - $a['invoice_qty_sale'];
            });
        }
        if (request('sort_invoice_qty') == 'ASC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $a['invoice_qty_sale'] <=> $b['invoice_qty_sale'];
            });
        }
        if (request('sort_retail_qty') == 'DESC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $b['pos_qty_sale'] - $a['pos_qty_sale'];
            });
        }
        if (request('sort_retail_qty') == 'ASC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $a['pos_qty_sale'] <=> $b['pos_qty_sale'];
            });
        }
        if (request('sort_total') == 'DESC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $b['total_amount'] - $a['total_amount'];
            });
        }
        if (request('sort_total') == 'ASC') {

            $finalArray[] = usort($finalArray, function ($a, $b) {

                return $a['total_amount'] <=> $b['total_amount'];
            });
        }
        $itemsPerPage = 500;
        $page = request('page');

        $paginator = $this->paginate($finalArray, $itemsPerPage, $page);

        return response()->json($paginator);
    }
    function combineStockSummaryExcel()
    {

        $finalArray = [];
        $fromDate = request('from');
        $toDate = request('to');
        $stock = Stock::select('name', 'id', 'product_name')->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('code', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('product_name', 'LIKE', '%' . request('search') . '%');
            });
        })->get();
        $loc_query = Department::where('show_pos', 1)->first();
        foreach ($stock as  $value) {
            $id = $value['id'];
            $inventory = Inventory::where(['stock_id' => $id, 'department_id' => $loc_query->id])
                ->first();
            $pos_sales = AllSaleDetailsReport::sumQtyAndPriceByStockId($id, $fromDate, $toDate);
            $invoice_sales = Invoice::sumQtyAndPriceByStockId($id, $fromDate, $toDate);
            $total_amount = ($pos_sales['sum_qty_and_price'] + $invoice_sales['sum_qty_and_price']);
            if ($total_amount <= 0) {
                continue;
            }
            $finalArray[] = array(
                'stock_id' => $value['id'],
                'pos_qty_sale' => $pos_sales['sum_qty'],
                'pos_total_sale' => $pos_sales['sum_qty_and_price'],
                'invoice_qty_sale' => $invoice_sales['sum_qty'],
                'invoice_total_sale' => $invoice_sales['sum_qty_and_price'],
                "total_qty" => ($pos_sales['sum_qty'] + $invoice_sales['sum_qty']),
                "total_amount" => ($pos_sales['sum_qty_and_price'] + $invoice_sales['sum_qty_and_price']),
                "product_name" => $value['product_name'],
                'current_qty' => $inventory ? $inventory->qty : 0
            );
        }
        //   $data = json_decode($finalArray, true);
        if (request('show_desc') == 'true') {

            $finalArray[] = usort($finalArray, function ($a, $b) {
                return $b['total_qty'] - $a['total_qty'];
            });
        } else {

            $finalArray[] = usort($finalArray, function ($a, $b) {
                return $a['total_qty'] - $b['total_qty'];
            });
        }


        return response()->json($finalArray);
    }
    public static function paginate(array $items, $perPage, $page)
    {
        $total = count($items);
        $offset = ($page - 1) * $perPage;

        $items = array_slice($items, $offset, $perPage);

        return new LengthAwarePaginator($items, $total, $perPage, $page);
    }


    function saleSumaryWithActivityExport()
    {
        $paginatedData = $this->querySummaryStatement();

        $data = [];

        foreach ($paginatedData as  $value) {

            $current_qty = 0;
            $loc_query = Department::where('show_pos', 1)->first();
            if ($loc_query) {
                $inventory_res = Inventory::where(['stock_id' => $value->stock_id, 'department_id' => $loc_query->id])->first();
                if ($inventory_res) {
                    $current_qty = $inventory_res->qty;
                }
            }

            $data[] = [
                'id' => $value->id,
                'stock' => $value->stock,


                'sum_receipt_total' => $value->sum_receipt_total,
                'total_qty' => $value->total_qty,
                'current_qty' => $current_qty
            ];
        }
        return $data;
    }
    function getSaleDetails()
    {
        $order_no = request('order_no');
        $res =  AllSaleDetailsReport::where(['order_no' => $order_no])->with(['stock', 'unit', 'customer'])->get();
        return $res;
    }








    //OSCU etims sales

    public function etimsCompleteSale(POSSaleRequest $request)
    {
        $validator = Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',


            // 'production_cost_data.*.total_amount' => 'numeric|required|gt:0',
            //  'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request)],


            'order_data_offline.*.stock_id' => [new ValidateIfItemIsOnEtim($request), new ValidateStockAvailability($request), new ValidateBatchNo($request), new ValidateExpiryOnSale($request), new ValidateBatchQty($request)],

            'order_no' => "required",

        ]);
        $receipt_no = $this->receiptNumberFinal();
        try {
            $url = Parent::digitaxBaseUrl() . 'sales';
            $pay_method = "07";
            if ($request->card_pay > 0) {
                $pay_method = "05";
            }
            if ($request->mpesa_pay > 0) {
                $pay_method = "06";
            }
            if ($request->cash_pay > 0) {
                $pay_method = "01";
            }
            if ($request->credit_pay > 0) {
                $pay_method = "02";
            }
            $items_array = [];
            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];
                $stock = Stock::where(['id' => $value['stock_id']])->first();
                $items_array[] = array(
                    'id' => $stock->item_id,
                    'unit_price' => (float)$value['selling_price'],
                    'quantity' => (float)$value['qty'],
                    'discount_rate' => 0,
                    "discount_amount" => 0
                );
            }
            $data = [];
            $customer = Customer::whereNotNull('pin')->find($request->customer_id);
            if ($customer) {

                $data = array(
                    "customer_pin" => $customer->pin,
                    "customer_name" => $customer->company_name,
                    "trader_invoice_number" => $receipt_no,
                    "receipt_type_code" => "S",
                    "payment_type_code" => $pay_method,
                    "invoice_status_code" => "02",
                    "items" => $items_array

                );
            } else {
                $data = array(
                    "trader_invoice_number" => $receipt_no,
                    "receipt_type_code" => "S",
                    "payment_type_code" => $pay_method,
                    "invoice_status_code" => "02",
                    "items" => $items_array

                );
            }

            //     print_r(json_encode($data,true));

            $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


            $responseData = $response->json();

            // print_r('???????' . json_encode($responseData, true));
            $statusCode = $response->status();
            //   print_r('code>>>>' . $statusCode);
            if ($statusCode == 201) {




                //     $re = $this->insertAfterEtims($request, $responseData);
                return response()->json($responseData, 201);

                //    return  response()->json(["results" => $re]);
            }
            if ($statusCode == 400) {
                return response()->json([$responseData], 400);
            }
            if ($statusCode == 409) {
                return response()->json([$responseData], 409);
            }
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
    //check if sale has been validated by etims
    function validateAndCompleteEtimsSale(Request $request)
    {
        Validator::validate($request->all(), [
            'digitax_id' => 'required',
        ]);
        try {
            $getResultsUrl = Parent::digitaxBaseUrl() . 'sales/' . $request->digitax_id;
            $response = Http::withHeaders(Parent::apiHeaders())->get($getResultsUrl, null);
            $responseData = $response->json();
            $statusCode = $response->status();
            //print_r(json_encode($responseData));
            //   print_r("status>>>>>" . $statusCode);
            if ($statusCode == 200) {

                if ($responseData['queue_status'] == 'completed') {

                    $this->insertAfterEtims($request, $responseData);

                    return response()->json($responseData, 200);
                } else {
                    if (Auth::user()->branch->etims_type == 'oscu') {
                        $this->addEtimsAfterDigitaxFail($request, $responseData);
                    }
                    return response()->json($responseData, 200);
                }
            }
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
    function updateEtimsToSale($e, $responseData)
    {


        $insert_etims = array(
            'etims_timestamp' => Parent::currentDateTime(),
            'etims_date' => $e['date'],
            'etims_time' => $e['time'],
            'trader_invoice_number' => $e['trader_invoice_number'],
            'digitax_id' => $e['digitax_id'],
            'serial_number' => $e['serial_number'],
            'receipt_number' => $e['receipt_number'],
            'sale_detail_url' => $responseData['etims_url'],
            'etims_customer_name' => $e['customer_name'],
            'etims_customer_pin' => isset($e['customer_pin']) ? $e['customer_pin'] : "",

            'digitax_id' => $e['digitax_id'],
            'taxable_amount_a' => $e['sales_tax_summary']['taxable_amount_a'],
            'taxable_amount_b' => $e['sales_tax_summary']['taxable_amount_b'],
            'taxable_amount_c' => $e['sales_tax_summary']['taxable_amount_c'],
            'taxable_amount_d' => $e['sales_tax_summary']['taxable_amount_d'],
            'taxable_amount_e' => $e['sales_tax_summary']['taxable_amount_e'],
            'tax_rate_a' => $e['sales_tax_summary']['tax_rate_a'],
            'tax_rate_b' => $e['sales_tax_summary']['tax_rate_b'],
            'tax_rate_c' => $e['sales_tax_summary']['tax_rate_c'],
            'tax_rate_d' => $e['sales_tax_summary']['tax_rate_d'],
            'tax_rate_e' => $e['sales_tax_summary']['tax_rate_e'],
            'tax_amount_a' => $e['sales_tax_summary']['tax_amount_a'],
            'tax_amount_b' => $e['sales_tax_summary']['tax_amount_b'],
            'tax_amount_c' => $e['sales_tax_summary']['tax_amount_c'],
            'tax_amount_d' => $e['sales_tax_summary']['tax_amount_d'],
            'tax_amount_e' => $e['sales_tax_summary']['tax_amount_e'],

        );
        return $insert_etims;
    }
    function insertAfterEtims(Request $request, $responseData)
    {

        $receipt_no = $request->trader_invoice_number;
        $insert_etims = [];
        if (Auth::user()->branch->etims_type == 'oscu') {

            $insert_etims = $this->updateEtimsToSale($request->all(), $responseData);
        }
        if (Auth::user()->branch->etims_type == 'vscu') {
            $receipt_no = $responseData['data']['traderInvoiceNo'];
            $insert_etims = array(
                'signature' => $responseData['data']['signature'],
                'scdcId' => $responseData['data']['scdcId'],
                'scuReceiptDate' => $responseData['data']['scuReceiptDate'],
                'scuReceiptNo' => $responseData['data']['scuReceiptNo'],
                'invoiceVerificationUrl' => $responseData['data']['invoiceVerificationUrl'],


                'totalTaxableAmount' => $responseData['data']['totalTaxableAmount'],
                'paymentType' => $responseData['data']['paymentType'],
                'totalTaxAmount' => $responseData['data']['totalTaxAmount'],
                'salesTypeCode' => $responseData['data']['salesTypeCode'],
                'receiptTypeCode' => $responseData['data']['receiptTypeCode'],
                'salesStatusCode' => $responseData['data']['salesStatusCode'],
                'internalData' => $responseData['data']['internalData'],

            );
        }

        DB::transaction(function () use ($request, $insert_etims, $receipt_no, $responseData) {
            $department_id  =   Department::where('show_pos', 1)->first()->id;

            AllSalesTotalReport::create(['order_no' => $receipt_no, 'receipt_no' => $receipt_no] + $insert_etims + $request->all() +
                ['cashier_id' => Parent::user_id(), 'department_id' => $department_id] +
                Parent::user_branch_id());
            $this->performAccountingInserts($request, $receipt_no);
            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];
                $rs = Stock::where('id', $value['stock_id'])->first();
                if ($value['batch_no']) {
                    PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']])->increment('qty_sold', $value['qty']);
                }
                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_amount'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $receipt_no,
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $value['customer_name'],
                        'batch_no' => $value['batch_no']

                    ]
                        + Parent::branch_array()
                );

                //insert ledger

                if (Parent::isInventory($value['stock_id'])) {
                    Inventory::updateOrCreate(
                        ['stock_id' => $value['stock_id'], 'department_id' => $department_id, 'branch_id' => Parent::branch_id()],
                        ['qty' => DB::raw('qty -' . $value['qty'])]
                    );
                }
                POSOrder::where(['order_no' => $value['order_no']])->delete();
            }

            $this->printReceipt($request, $receipt_no, $responseData);
        }, 5);
    }
    function addEtimsAfterDigitaxFail(Request $request, $responseData)
    {
        $receipt_no = $request->trader_invoice_number;
        DB::transaction(function () use ($request,  $receipt_no, $responseData) {
            $department_id  =   Department::where('show_pos', 1)->first()->id;

            AllSalesTotalReport::create(['order_no' => $receipt_no, 'receipt_no' => $receipt_no] + ['sale_detail_url' => null, 'digitax_id' => $responseData['digitax_id']] + $request->all() +
                ['cashier_id' => Parent::user_id(), 'department_id' => $department_id] +
                Parent::user_branch_id());
            $this->performAccountingInserts($request, $receipt_no);
            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];
                $rs = Stock::where('id', $value['stock_id'])->first();
                if ($value['batch_no']) {
                    PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']])->increment('qty_sold', $value['qty']);
                }
                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_amount'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $receipt_no,
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $value['customer_name'],
                        'batch_no' => $value['batch_no']

                    ]
                        + Parent::branch_array()
                );

                //insert ledger

                if (Parent::isInventory($value['stock_id'])) {
                    Inventory::updateOrCreate(
                        ['stock_id' => $value['stock_id'], 'department_id' => $department_id, 'branch_id' => Parent::branch_id()],
                        ['qty' => DB::raw('qty -' . $value['qty'])]
                    );
                }
                POSOrder::where(['order_no' => $value['order_no']])->delete();
            }

            $this->printReceipt($request, $receipt_no, $responseData);
        }, 5);
    }
    function performAccountingInserts($request, $receipt_no = null)
    {
        if ($request->card_pay > 0) {
            $this->insertCardLedger($request, $receipt_no);
        }
        if ($request->mpesa_pay > 0) {
            $this->insertMpesaLedger($request, $receipt_no);
        }
        if ($request->credit_pay > 0) {
            $this->insertCustomerCredit($request, $receipt_no);
        }
        $this->creditInventoryAccount($request, $receipt_no, ($request->receipt_total - $request->total_vat));

        $this->creditOutputVATAccount($request, $receipt_no, $request->total_vat);
    }
    //etims backsales
    public function etimsBackSale(Request $request)
    {
        $order_data_offline = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)->where('order_no', $request->order_no)->get();

        $count = 0;
        for ($i = 0; $i < count($order_data_offline); $i++) {
            $value = $order_data_offline[$i];

            $stock = Stock::where(['id' => $value['stock_id']])->first();

            if (!$stock->item_id) {
                $count += 1;
            }
        }
        if ($count > 0) {
            return response()->json(["message" => "some items not registered to etims"], 400);
        }
        try {
            $url = Parent::digitaxBaseUrl() . 'sales';
            $items_array = [];
            for ($i = 0; $i < count($order_data_offline); $i++) {
                $value = $order_data_offline[$i];

                $stock = Stock::where(['id' => $value['stock_id']])->first();
                $items_array[] = array(
                    'id' => $stock->item_id,
                    'unit_price' => (float)$value['price'],
                    'quantity' => (float)$value['qty'],
                    'discount_rate' => 0,
                    "discount_amount" => 0
                );
            }
            $data = array(
                "trader_invoice_number" => $request->order_no,
                "receipt_type_code" => "S",
                "payment_type_code" => "07",
                "invoice_status_code" => "02",
                "items" => $items_array

            );


            $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


            $responseData = $response->json();

            $statusCode = $response->status();

            if ($statusCode == 201) {
                AllSalesTotalReport::withoutGlobalScope(BranchScope::class)->where('receipt_no', $request->order_no)->update(
                    [
                        'digitax_id' => $responseData['digitax_id'] ?? null
                    ]
                );
                return  response()->json($responseData);
            }
            if ($statusCode == 400) {
                return response()->json($responseData, 400);
            }
            if ($statusCode == 409) {
                return response()->json($responseData, 400);
            }
        } catch (Exception $e) {
            // return response()->json(["error" => $e->getMessage()], 500);
        }
    }
    function checkEtimsStatusSale(Request $request)
    {
        Validator::validate($request->all(), [
            'digitax_id' => 'required',
        ]);
        try {
            $getResultsUrl = Parent::digitaxBaseUrl() . 'sales/' . $request->digitax_id;
            $response = Http::withHeaders(Parent::apiHeaders())->get($getResultsUrl, null);
            $responseData = $response->json();
            $statusCode = $response->status();
            if ($statusCode == 200) {

                if ($responseData['queue_status'] == 'COMPLETED') {

                    // $this->insertAfterEtims($request, $responseData);
                    $insert_etims   = $this->updateEtimsToSale($responseData, $responseData);
                    AllSalesTotalReport::where('order_no', $request->order_no)->update($insert_etims);

                    return response()->json($responseData, 200);
                } else {

                    return response()->json($responseData, 200);
                }
            }
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
    //validate etims backsale
    function validateEtimsBackSale(Request $request)
    {
        Validator::validate($request->all(), [
            'digitax_id' => 'required',
        ]);
        try {
            $getResultsUrl = Parent::digitaxBaseUrl() . 'sales/' . $request->digitax_id;
            $response = Http::withHeaders(Parent::apiHeaders())->get($getResultsUrl, null);
            $responseData = $response->json();
            $statusCode = $response->status();

            if ($statusCode == 200) {

                if ($responseData['queue_status'] == 'completed') {

                    $insert_etims   = $this->updateEtimsToSale($request->all(), $responseData);
                    AllSalesTotalReport::where('order_no', $request->order_no)->update($insert_etims);

                    return response()->json($responseData, 200);
                } else {
                    return response()->json($responseData, 200);
                }
            }
        } catch (Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }
    function etimsQuery()
    {
        $user_id = request('user_id');
        $cashier_id = request('cashier_id');

        $from = date(request('from'));
        $to = date(request('to'));
        $order_type = request('order_type');
        $main_query = AllSalesTotalReport::with([
            'user',
            'cashier',
            'customer',
            'card_terminal_account.account'
        ]);
        $main_query->when($order_type != '', function ($query) use ($order_type) {

            $query->where('order_type',  $order_type);
        });
        $main_query->when($user_id != '', function ($query) use ($user_id) {

            $query->where('user_id',  $user_id);
        });
        $main_query->when($cashier_id != '', function ($query) use ($cashier_id) {

            $query->where('cashier_id',  $cashier_id);
        });

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')
                ->orWhere('etims_customer_pin', 'LIKE', '%' . request('search') . '%')
                ->orWhere('serial_number', 'LIKE', '%' . request('search') . '%')
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->orWhereHas('customer', function ($q) {
                    $q->where('company_name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });
        return $main_query;
    }
    function fetchAllEtimsReport()
    {
        $main_query = $this->etimsQuery();

        if (Parent::showBranchPermission()) {
            $main_query->withoutGlobalScope(BranchScope::class);
        }
        $main_query->whereNotNull('sale_detail_url')->latest('created_at');

        if (request('page') > 0) {
            $res = $main_query->paginate(30);
        } else {
            $res = $main_query->get();
        }
        return response()->json($res);
    }

    function fetchAllEtimsTotalReport()
    {
        $waiter_id = request('waiter_id');
        $cashier_id = request('cashier_id');
        $order_type = request('order_type');
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = AllSalesTotalReport::with([
            'customer',
            'user',
            'cashier',
        ]);
        $main_query->when($waiter_id != '', function ($query) use ($waiter_id) {

            $query->where('waiter_id',  $waiter_id);
        });
        $main_query->when($order_type != '', function ($query) use ($order_type) {

            $query->where('order_type',  $order_type);
        });
        $main_query->when($cashier_id != '', function ($query) use ($cashier_id) {

            $query->where('cashier_id',  $cashier_id);
        });
        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('order_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('receipt_no', 'LIKE', '%' . request('search') . '%')
                ->orWhere('payment_ref', 'LIKE', '%' . request('search') . '%')

                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                })

                ->orWhereHas('cashier', function ($q) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                });
        });




        if (request('is_etims')) {

            $main_query->whereNotNull('sale_detail_url');
        }
        $main_query->selectRaw('*,
    SUM(receipt_total) AS sum_receipt_total,
    SUM(cash_pay+receipt_balance) AS sum_cash_pay,
    SUM(mpesa_pay) AS sum_mpesa_pay,
    SUM(card_pay) AS sum_card_pay,
    SUM(credit_pay) AS sum_credit_pay,
    SUM(tax_amount_a+tax_amount_b+tax_amount_c+tax_amount_d+tax_amount_e) AS sum_total_vat
    ');
        $res = $main_query->first();

        return response()->json($res);
    }

    function receiptNumberFinal()
    {
        $invoice_no =  '1';
        $res = AllSalesTotalReport::withTrashed()->withoutGlobalScope(BranchScope::class)->orderBy('id', 'DESC')->first();
        if ($res) {
            $prev_invoice_no = $res->id;


            $current_invoice_no = $prev_invoice_no + 1;
            $invoice_no = '0' . $current_invoice_no;
        }

        return  $invoice_no;
    }






    //CREDIT NOTE TOPUPSALE

    public function exchangeWithOriginalReceiptAndTopUpPayment(POSSaleRequest $request)
    {
        $validator = Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',
            'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request), new ValidateBatchNo($request), new ValidateExpiryOnSale($request), new ValidateBatchQty($request)],

        ]);
        $new_receipt_num = null;
        DB::transaction(function () use ($request, &$new_receipt_num) {
            $res = POSCredit::with(['all_sale_details_report'])->where(['credit_no' => $request->credit_no])->with(['stock'])->get();
            $receipt_no = null;

            if (!$res->isEmpty()) {
                $total_credit_vat = 0;
                $credit_amount = 0;
                foreach ($res as $value) {
                    $receipt_no = $value['receipt_no'];
                    $total_credit_vat += $value['row_vat'];
                    $credit_amount += $value['row_total'];
                    if ($value['deduct_stock']) {
                        if (Parent::isInventory($value['stock_id'])) {

                            // Inventory::where([
                            //     'branch_id' => Parent::branch_id(),
                            //     'stock_id' => $value['stock_id'],
                            //     'department_id' => $value['all_sale_details_report']['department_id']
                            // ])->increment('qty', $value['qty']);

                            Inventory::updateOrCreate(
                                [
                                    'stock_id' => $value['stock_id'],
                                    'department_id' => $value['all_sale_details_report']['department_id'],
                                    'branch_id' => Parent::branch_id()
                                ],
                                ['qty' => DB::raw('qty +' . $value['qty'])]
                            );
                        }
                    }

                    AllSaleDetailsReport::where(['order_no' => $value['receipt_no'], 'stock_id' => $value['stock_id']])->update([
                        'qty' => DB::raw('qty -' . $value['qty']),
                        'row_total' => DB::raw('row_total -' . $value['row_total']),
                        'row_vat' => DB::raw('row_vat -' . $value['row_vat'])
                    ]);
                    AllSalesTotalReport::where(['receipt_no' => $value['receipt_no']])->update([

                        'total_vat' => DB::raw('total_vat -' . $value['row_vat'])
                    ]);

                    if ($value['all_sale_details_report']['batch_no']) {
                        PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['all_sale_details_report']['batch_no']])->decrement('qty_sold', $value['qty']);
                    }
                }
            }


            $new_receipt_num = $this->saleAsNew($request, $total_credit_vat, $credit_amount);
            POSCredit::where(['credit_no' => $request->credit_no])->update(['status' => 'completed', 'cu_invoice_no' => $request->cu_invoice_no]);
        }, 5);
        return $new_receipt_num;
    }
    function saleAsNew(Request $request, $total_credit_vat, $credit_amount)
    {
        $validator = Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',




            'order_no' => "required",

        ]);
        $receipt_no = $this->receiptNumberFinal();
        DB::transaction(function () use ($request, $receipt_no, $total_credit_vat, $credit_amount) {
            $department_id  =   Department::where('show_pos', 1)->first()->id;
            AllSalesTotalReport::create(
                ['order_no' => $receipt_no, 'receipt_no' => $receipt_no] +
                    $request->validated() +
                    ['cashier_id' => Parent::user_id(), 'department_id' => $department_id] +
                    ['branch_id' => Parent::branch_id(), 'user_id' => $request->order_data_offline[0]['user_id']]
            );

            if ($request->card_pay > 0) {
                $this->insertCardLedger($request, $receipt_no);
            }
            if ($request->mpesa_pay > 0) {
                $this->insertMpesaLedger($request, $receipt_no);
            }
            if ($request->cash_pay > 0) {
                $this->insertCashLedger($request, $receipt_no);
            }
            if ($request->credit_pay > 0) {
                $this->insertCustomerCredit($request, $receipt_no);
            }
            $this->debitOutputVATAccount($request, $total_credit_vat, $receipt_no);


            $this->debitInventoryAccount($request, $receipt_no, ($credit_amount - $total_credit_vat));
            $receipt_total = 0;
            $total_vat = 0;
            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];
                $receipt_total += $value['row_amount'];
                $total_vat += $value['row_vat'];

                $customer = Customer::where('id', $value['customer_id'])->first();
                $customer_name = $customer ? $customer->company_name : "";
                $rs = Stock::where('id', $value['stock_id'])->first();
                if ($value['batch_no']) {
                    PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['batch_no']])->increment('qty_sold', $value['qty']);
                }
                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_amount'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $receipt_no,
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $customer_name,
                        'batch_no' => $value['batch_no']

                    ]
                        + Parent::branch_array()
                );

                //insert ledger

                if (Parent::isInventory($value['stock_id'])) {
                    Inventory::where(['stock_id' => $value['stock_id'], 'department_id' => $department_id])->decrement('qty', $value['qty']);
                }
            }
            //ledger entries
            $this->creditInventoryAccount($request, $receipt_no, ($receipt_total - $total_vat));
            $this->creditOutputVATAccount($request, $receipt_no, $total_vat);

            $this->printReceipt($request, $receipt_no);
        }, 5);


        return  $receipt_no;
    }
    //RETURN AND REFUND
    public function commitRefund(RetailsSaleRefundRequest $request)
    {
        DB::transaction(function () use ($request) {
            $res = POSCredit::with(['all_sale_details_report'])->where(['credit_no' => $request->credit_no])->with(['stock'])->get();
            $receipt_no = null;
            if (!$res->isEmpty()) {
                $receipt_total = 0;
                $total_vat = 0;
                foreach ($res as $value) {
                    $total_vat += $value['row_vat'];
                    $receipt_total += $value['row_total'];
                    $receipt_no = $value['receipt_no'];
                    if ($value['deduct_stock']) {
                        if (Parent::isInventory($value['stock_id'])) {

                            Inventory::where([
                                'branch_id' => Parent::branch_id(),
                                'stock_id' => $value['stock_id'],
                                'department_id' => $value['all_sale_details_report']['department_id']
                            ])->increment('qty', $value['qty']);
                        }
                    }

                    AllSaleDetailsReport::where(['order_no' => $value['receipt_no'], 'stock_id' => $value['stock_id']])->update([
                        'qty' => DB::raw('qty -' . $value['qty']),
                        'row_total' => DB::raw('row_total -' . $value['row_total']),
                        'row_vat' => DB::raw('row_vat -' . $value['row_vat'])
                    ]);
                    AllSalesTotalReport::where(['receipt_no' => $value['receipt_no']])->update([

                        'total_vat' => DB::raw('total_vat -' . $value['row_vat']),
                        'receipt_total' => DB::raw('receipt_total -' . $value['row_total'])
                    ]);

                    if ($value['all_sale_details_report']['batch_no']) {
                        PurchaseOrderReceivableWithBatch::where(['stock_id' => $value['stock_id'], 'batch_no' => $value['all_sale_details_report']['batch_no']])->decrement('qty_sold', $value['qty']);
                    }
                }

                RetailSalesRefund::create($request->validated() + Parent::user_branch_id());
            }
            //ledger entries
            $this->debitInventoryAccount($request, $request->credit_no, ($receipt_total - $total_vat));
            $this->debitOutputVATAccount($request, $total_vat, $request->credit_no);
            $insert_data = array(
                'other_account_name' => "Retail Sales Refund",
                'account_id' => $request->account_id,
                'account_type' => 'retail',
                'entry_code' => $request->credit_no,
                'ref' => $request->credit_no,
                'details' => "Refund",
                'credit_amount' => $request->refund_amount,
                'description' => "Refund",
                'entry_date' => $request->credit_date,

            );
            GeneralLedger::create($insert_data + Parent::user_branch_id());
            POSCredit::where(['credit_no' => $request->credit_no])->update(['status' => 'completed']);
        }, 5);
        return true;
    }
    function openDawer()
    {
        $invoice_printer = Auth::user()->branch->invoice_printer;
        $connector = null;
        $connector = new WindowsPrintConnector($invoice_printer);

        $printer = new Printer($connector);
        $fonts = array(
            Printer::FONT_A,
            Printer::FONT_B,
            Printer::FONT_C
        );
    }




    function vscuEtimsSale(POSSaleRequest $request)
    {
        $validator = Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',


            'payment_ref' => new ValidateRegisterActiveSales($request),

            'order_data_offline.*.stock_id' => [new ValidateIfStockIsEtims($request), new ValidateStockAvailability($request), new ValidateBatchNo($request), new ValidateExpiryOnSale($request), new ValidateBatchQty($request)],

            'order_no' => "required",

        ]);

        try {
            $customerPin = "";
            if ($request->customer_id) {
                $customerPin = Customer::find($request->customer_id)->pin;
                $customerPin = $customerPin ? $customerPin : "";
            }
            $url = Parent::pgmBaseUrl() . 'invoices';

            $receipt_no = $this->receiptNumberFinal();

            $order_data_offline = $request->order_data_offline;
            $items_array = [];
            for ($i = 0; $i < count($order_data_offline); $i++) {
                $value = $order_data_offline[$i];
                $stock = Stock::where(['id' => $value['stock_id']])->first();
                $items_array[] = array(
                    'itemCode' => $stock->etims_code,
                    'pkg' => 0,
                    'qty' => (float)$value['qty'],
                    'unitPrice' => (float)$value['selling_price'],
                    'amount' => (float)$value['row_amount'],
                    'discountAmount' => 0,

                );
            }
            $data = [
                "traderInvoiceNo" => $receipt_no,
                "totalAmount" => $request->receipt_total,
                "paymentType" => "01",
                "salesTypeCode" => "N",
                "receiptTypeCode" => "S",
                "salesStatusCode" =>  "01",
                "salesDate" => date('YmdHis'),
                "currency" => "KES",
                "exchangeRate" => 1.0,
                "salesItems" => $items_array,
                'customerPin' => $customerPin

            ];


            $response = Http::withHeaders(Parent::pgmApiHeaders())->post($url, $data);


            $responseData = $response->json();
            $statusCode = $response->status();

            // print_r($statusCode);
            if ($statusCode == 200) {
                if ($responseData['status'] == 200) {

                    //    return $this->insertSaleInfo($request, $receipt_no, $responseData);
                    return  $this->insertAfterEtims($request, $responseData);
                }
                //  return response()->json($data);
            }
            if ($statusCode == 400) {

                if ($responseData['status'] == 400) {
                    return response()->json(['error' => $responseData['message']], 400);
                }
            } else {
                return response()->json(['error' => $responseData], 500);
            }
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    function postSalesetimsSale(Request $request)
    {
        $validator = Validator::validate($request->all(), [
            'order_no' => [new ValidatePostSaleEtimsCodeAvailability()],
        ]);

        try {
            $url = Parent::pgmBaseUrl() . 'invoices';

            $receipt_no = $this->receiptNumberFinal();

            $res = AllSaleDetailsReport::with(['stock'])->where(['order_no' => $request->order_no])->get();
            $all_sales = AllSalesTotalReport::where(['order_no' => $request->order_no])->first();
            $items_array = [];
            for ($i = 0; $i < count($res); $i++) {
                $value = $res[$i];
                $stock = Stock::where(['id' => $value['stock_id']])->first();
                $items_array[] = array(
                    'itemCode' => $stock->etims_code,
                    'pkg' => 0,
                    'qty' => (float)$value['qty'],
                    'unitPrice' => (float)$value['price'],
                    'amount' => (float)($value['qty'] * $value['price']),
                    'discountAmount' => 0,

                );
            }
            $data = [
                "traderInvoiceNo" => $all_sales->receipt_no,
                "totalAmount" => (float)$all_sales->receipt_total,
                "paymentType" => "01",
                "salesTypeCode" => "N",
                "receiptTypeCode" => "S",
                "salesStatusCode" =>  "01",
                "salesDate" => date('YmdHis'),
                "currency" => "KES",
                "exchangeRate" => (float)1.0,
                "salesItems" => $items_array

            ];


            $response = Http::withHeaders(Parent::apiHeaders())->post($url, $data);


            $responseData = $response->json();
            $statusCode = $response->status();
            //print_r($responseData);
            // print_r($statusCode);
            if ($statusCode == 200) {
                if ($responseData['status'] == 200) {
                    $etims_data = array(
                        'signature' => $responseData['data']['signature'],
                        'scdcId' => $responseData['data']['scdcId'],
                        'scuReceiptDate' => $responseData['data']['scuReceiptDate'],
                        'scuReceiptNo' => $responseData['data']['scuReceiptNo'],
                        'invoiceVerificationUrl' => $responseData['data']['invoiceVerificationUrl'],
                        'exchangeRate' => $responseData['data']['exchangeRate'],

                    );
                    AllSalesTotalReport::where(
                        ['order_no' =>  $request->order_no]
                    )->update($etims_data);

                    if (Auth::user()->branch->print_type == 'server') {
                        $this->reprintReceipt($request->order_no, $responseData);
                    }
                    return true;
                } else {
                    return response()->json(['error' => "Unkown Error occurred"], 500);
                }
                //  return response()->json($data);
            }
            if ($statusCode == 400) {

                if ($responseData['status'] == 400) {
                    return response()->json(['error' => $responseData['message']], 400);
                }
            } else {
                return response()->json(['error' => $responseData], 500);
            }
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function reprintReceipt($order_no, $responseData = null)
    {


        $res = AllSaleDetailsReport::with([
            'stock',
            'unit',
            'branch',
            'user'
        ])->where("order_no", $order_no)->first();
        $res_order = AllSaleDetailsReport::with([
            'stock',
            'unit',
            'branch',
            'user'
        ])->where("order_no", $order_no)->get();
        $sale_info = AllSalesTotalReport::with([
            'customer'

        ])->where("order_no", $order_no)->first();
        // Enter the share name for your USB printer here
        //return ($res);
        $user_printer = Auth::user()->printer;
        $printer = $user_printer ? $user_printer : $res->branch->invoice_printer;
        $connector = null;

        $connector = new WindowsPrintConnector($printer);

        $printer = new Printer($connector);
        $fonts = array(
            Printer::FONT_A,
            Printer::FONT_B,
            Printer::FONT_C
        );
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->setFont($fonts[1]);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($res->branch->business_name . " \n");


        $printer->setTextSize(1, 1);
        $printer->setFont($fonts[0]);
        $printer->text("" . $res->branch->address . "\n");
        // $printer->text($res->branch->postal_address . "\n");
        $printer->text("TEL: " . $res->branch->phone_no . "\n");
        $printer->text("PIN: " . $res->branch->kra_pin . "\n");

        $printer->text(Parent::currentDateTime() . "\n");
        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);

        $printer->text('TAX INVOICE' . "\n\n");
        $printer->setTextSize(1, 1);
        $printer->setFont($fonts[0]);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text('Receipt No: ' . $order_no . ' ' . "\n");
        if ($sale_info->customer->company_name) {
            $printer->text('Customer : ' . $sale_info->customer->company_name . ' ' . "\n");
            $printer->text('Customer PIN : ' . $sale_info->customer->pin . ' ' . "\n");
        }
        $printer->text('Served By: ' . Parent::removeSecondName($res->user->name)  . ' ' . "\n");

        $printer->setEmphasis(true);
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);
        $printer->text('QTY' . '  ' . 'Particulars' . '  ' . '@cost' . '  ' . ' Amt' . "\n\n");
        $bill_total = 0;
        foreach ($res_order as $value) {
            $bill_total += $value['row_total'];
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);
            $printer->setFont($fonts[0]);
            $printer->text($value['qty'] . '    ' . $value['stock']['product_name']  . "\n");

            $printer->text($value['notes'] . "\n");
            $printer->setFont($fonts[1]);
            $printer->setTextSize(2, 1);
            $printer->text($value['qty'] . '      X       ' . $value['price'] . '      ' . number_format($value['row_total']) . "\n");
        }
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("\n\n" . '==============================' . "\n");

        if ($sale_info->card_pay > 0) {
            $printer->text('CARD PAYMENT:  ' . number_format($sale_info->card_pay, 2) . "\n");
        }

        if ($sale_info->cash_pay > 0) {
            $printer->text('CASH PAYMENT:  ' . number_format($sale_info->cash_pay, 2) . "\n");
            if ($sale_info->receipt_balance != 0) {
                $printer->text('Change:  ' . number_format($sale_info->receipt_balance, 2) . "\n");
            }
        }
        if ($sale_info->mpesa_pay > 0) {
            $printer->text('M-PESA PAYMENT:  ' . number_format($sale_info->mpesa_pay, 2) . "\n");
        }
        if ($sale_info->loyalty_points > 0) {
            $printer->text('REDEEMED LOYALTY POINTS:  ' . number_format($sale_info->loyalty_points, 2) . "\n");
        }
        if ($sale_info->receipt_balance != 0) {

            $printer->text('CHANGE:  ' . number_format(str_replace('-', '', $sale_info->receipt_balance), 2) . "\n");
        }


        $printer->text('VAT AMOUNT:  ' . number_format($sale_info->total_vat, 2) . "\n");
        $printer->text('RECEIPT TOTAL:  ' . number_format($bill_total, 2) . "\n");

        $printer->text("\n\n" . '==============================' . "\n");
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);
        if ($res_order[0]['delivery_time']) {
            $printer->text("DELIVERY TIME:" . $res_order[0]['delivery_time'] . "\n");
        }
        $printer->setFont($fonts[0]);
        $printer->setTextSize(1, 1);
        $printer->text($res->branch->till_no . "\n");
        $printer->setFont($fonts[1]);
        $printer->setTextSize(2, 1);
        if ($sale_info->customer_id) {
            $printer->text('LOYALTY POINTS:  ' . $this->getRedeemablePoints($sale_info) . "\n");
        }
        $printer->setFont($fonts[0]);
        $printer->setTextSize(1, 1);

        $printer->text('Thank you for shopping with us!' . "\n");
        //  $printer->text(' Goods once sold are not to be returned' . "\n");
        $printer->text($res->branch->footer_1 . "\n");
        if ($sale_info->qr_link != "") {
            $level = Printer::QR_ECLEVEL_H;
            $printer->qrCode($sale_info->qr_link, $level);
            $printer->text("\n");
            $printer->text('CONTROL UNIT INFO ' . "\n");
            $printer->text('CU SERIAL NO: ' . $sale_info->cu_serial_number . "\n");
            $printer->text('CU INVOICE NO: ' . $sale_info->cu_invoice_number . "\n");
            $printer->text('CU Timestamp:' . $sale_info->cu_timestamp . "\n");
        }
        if ($responseData) {



            $date = DateTime::createFromFormat('YmdHis', $responseData['data']['scuReceiptDate']);
            $level = Printer::QR_ECLEVEL_H;
            $printer->qrCode($responseData['data']['invoiceVerificationUrl']);
            $printer->text("\n");
            $printer->text('CONTROL UNIT INFO ' . "\n");
            $printer->text('Signature: ' . $responseData['data']['signature'] . "\n");
            $printer->text('SCU Receipt No: ' .  $responseData['data']['scuReceiptNo'] . "\n");
            $printer->text('SCU Serial No: ' .  $responseData['data']['scdcId'] . "\n");

            $printer->text('CU Timestamp:' . $date->format('Y-m-d H:i:s') . "\n");
        }
        $printer->text("\n" . '------------------------------' . "\n");


        //$filename = 'output.txt';
        // file_put_contents($filename, $printer . PHP_EOL);
        $printer->selectPrintMode();
        $printer->text("\n");
        $printer->cut();

        /* Close printer */
        $printer->pulse();
        $printer->close();
    }


    function completePreoderSale(POSSaleRequest $request)
    {
        $validator = Validator::validate($request->all(), [

            'payment_ref' => new ValidateRegisterActiveSales($request),
            'credit_pay' => 'required|numeric|gte:0',
            'customer_name' => 'required',

            'customer_id' => 'required'
        ]);
        if ($request->credit_pay > 0) {
            $validator = Validator::validate($request->all(), [

                'credit_pay' => [new ValidatePOSCreditLimit($request)],
            ]);
        }
        $receipt_no = $this->receiptNumberFinal();
        DB::transaction(function () use ($request, $receipt_no) {
            $unique_code = Parent::generateDocNumber("P_ORDER");
            $preorder = $request->order_data; //Preorder::with(['stock', 'customer'])->where(['order_status' => 'progress', "customer_id" => $request->customer_id])->latest()->get();
            $department_id  =   Department::where('show_pos', 1)->first()->id;


            AllSalesTotalReport::create(
                ['order_no' => $receipt_no, 'receipt_no' => $receipt_no, 'customer_name' => $request->customer_name] +
                    $request->validated() +
                    ['cashier_id' => Parent::user_id(), 'department_id' => $department_id] +
                    ['branch_id' => Parent::branch_id(), 'user_id' => $preorder[0]['user_id']]
            );
            if ($request->cash_pay > 0) {
                $this->insertCashLedger($request, $receipt_no, $unique_code);
            }
            if ($request->card_pay > 0) {
                $this->insertCardLedger($request, $receipt_no, $unique_code);
            }
            if ($request->mpesa_pay > 0) {
                $this->insertMpesaLedger($request, $receipt_no, $unique_code);
            }
            if ($request->credit_pay > 0) {
                $this->insertCustomerCredit($request, $receipt_no, $unique_code);
            }
            $this->creditOutputVATAccount($request, $receipt_no, $request->total_vat, $unique_code);
            $this->creditInventoryAccount($request, $receipt_no, ($request->receipt_total - $request->total_vat), $unique_code);
            for ($i = 0; $i < count($preorder); $i++) {
                $value = $preorder[$i];
                $rs = Stock::where('id', $value['stock_id'])->first();

                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['order_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_total'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $receipt_no,
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $request->customer_name,

                        'discount' => 0,
                    ]
                        + Parent::branch_array()
                );

                //insert ledger



            }
            Preorder::where(['order_status' => 'progress', "customer_id" => $request->customer_id])->update(['order_status' => 'completed', 'order_no' => $receipt_no, 'unique_code' => $unique_code]);

            for ($i = 0; $i < Auth::user()->branch->print_copies; $i++) {

                if (Auth::user()->branch->print_type == 'server') {
                    $this->printReceipt($request, $receipt_no);
                }
            }
        }, 5);
        if (Auth::user()->branch->print_type == 'offline') {
            $res_details =   AllSaleDetailsReport::where('order_no', $receipt_no)->with(['stock', 'customer', 'branch', 'user'])->get();
            $res_total =   AllSalesTotalReport::where('receipt_no', $receipt_no)->with(['customer', 'branch', 'user'])->get();
            return response()->json(["res_details" => $res_details, 'res_total' => $res_total]);
        }
        return  $receipt_no;
    }


    function completeDirectRetailExchangeSales(RetailPOSSaleRequest $request)
    {
        Validator::validate($request->all(), [
            'order_data_offline' => 'required|array',
            'order_data_offline.*.qty' => 'required|numeric|gt:0',
            'payment_ref' => new ValidateRegisterActiveSales($request),
            'order_data_offline.*.stock_id' => [new ValidateStockAvailability($request)],



        ]);
        $total_credit_vat = $request->total_vat;

        $receipt_no = $this->receiptNumberFinal();
        DB::transaction(function () use ($request, $receipt_no, $total_credit_vat) {
            $unique_code = Parent::generateDocNumber("DCR");
            $department_id  =   Department::where('show_pos', 1)->first()->id;
            AllSalesTotalReport::create(
                ['order_no' => $receipt_no, 'receipt_no' => $receipt_no] +
                    $request->validated() +
                    ['cashier_id' => Parent::user_id(), 'department_id' => $department_id] +
                    ['branch_id' => Parent::branch_id(), 'user_id' => $request->order_data_offline[0]['user_id']]
            );
            if ($request->card_pay > 0) {
                $this->insertCardLedger($request, $receipt_no, $unique_code);
            }
            if ($request->mpesa_pay > 0) {
                $this->insertMpesaLedger($request, $receipt_no, $unique_code);
            }
            if ($request->cash_pay > 0) {
                $this->insertCashLedger($request, $receipt_no, $unique_code);
            }
            if ($request->credit_pay > 0) {
                $this->insertCustomerCredit($request, $receipt_no, $unique_code);
            }

            $receipt_total = $request->receipt_total;

            $total_vat = $total_credit_vat;
            for ($i = 0; $i < count($request->order_data_offline); $i++) {
                $value = $request->order_data_offline[$i];



                $customer = Customer::where('id', $value['customer_id'])->first();
                $customer_name = $customer ? $customer->company_name : "";
                $rs = Stock::where('id', $value['stock_id'])->first();

                AllSaleDetailsReport::create(
                    [
                        'stock_id' => $value['stock_id'],
                        "product_category_id" => Parent::product_category_id($value['stock_id']),
                        'unit_id' => $rs->unit_id,

                        'user_id' => $value['user_id'],
                        'cashier_id' => Parent::user_id(),

                        'department_id' => $department_id,

                        'order_date' => $value['credit_date'],
                        'qty' => $value['qty'],
                        'price' => $value['selling_price'],
                        'row_total' => $value['row_total'],
                        'row_vat' => $value['row_vat'],

                        'order_no' => $receipt_no,
                        'cost_price' => $rs->purchase_price,
                        'cost_total' => ($rs->purchase_price * $value['qty']),
                        'customer_id' => $value['customer_id'],
                        'customer_name' => $customer_name,


                    ]
                        + Parent::branch_array()
                );

                //insert ledger

                if (Parent::isInventory($value['stock_id'])) {
                    Inventory::where(['stock_id' => $value['stock_id'], 'department_id' => $department_id])->decrement('qty', $value['qty']);
                }
            }
            //ledger entries
            $this->creditInventoryAccount($request, $receipt_no, ($receipt_total - $total_vat), $unique_code);
            $this->creditOutputVATAccount($request, $receipt_no, $total_vat, $unique_code);
            DirectRetailsCredit::where(['credit_no' => $request->order_data_offline[0]['credit_no'], 'customer_id' => $request->order_data_offline[0]['customer_id']])
                ->update([
                    'ref_no' =>  $this->getRefNo(),
                    'unique_code' => $unique_code,
                    'receipt_no' => $receipt_no,
                    'status' => 'completed'
                ]);

            $this->printReceipt($request, $receipt_no);
        }, 5);


        return  $receipt_no;
    }
    function getRefNo()
    {

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $orderno = $today . '-' . 'CR' . sprintf('%04d', $string);
        return $orderno;
    }

    function fetchSalesByReceiptNo()
    {


        $main_query = $this->allSalesQuery();



        $res = $main_query->withoutGlobalScope(BranchScope::class)->where('receipt_no', request('ref_no'))->first();
        $main_query = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)->with([
            'user',
            'cashier',
            'unit',
            'stock',
            'customer'
        ])->where('order_no', request('ref_no'));
        $data = $main_query->get();
        return response()->json(["form_data" => $res, 'order_items' => $data]);
    }


    public function fetchLiveBalances()
    {
        $branch = Branch::first(); // Get first branch (or use auth-based filtering)

        // Initialize totals
        $cash_total = 0;
        $mpesa_total = 0;
        $card_totals = [];

        // Fetch total payments for branch
        if ($branch) {
            $sales = AllSalesTotalReport::where('branch_id', $branch->id)
                ->selectRaw('
                    SUM(cash_pay) AS cash_total,
                    SUM(mpesa_pay) AS mpesa_total
                ')
                ->first();

            $cash_total = $sales->cash_total ?? 0;
            $mpesa_total = $sales->mpesa_total ?? 0;
        }

        // Fetch card totals, grouped by card terminal
        $card_payments = AllSalesTotalReport::where('branch_id', $branch->id)
            ->whereNotNull('card_terminal_account_id') // Ensure terminal is assigned
            ->selectRaw('card_terminal_account_id, SUM(card_pay) AS card_total')
            ->groupBy('card_terminal_account_id')
            ->get();

        foreach ($card_payments as $payment) {
            $card_account = CardTerminalAccount::find($payment->card_terminal_account_id);
            $card_totals[] = [
                'card_name' => $card_account->account->account ?? 'Unknown',
                'card_total' => $payment->card_total,
            ];
        }

        // Response similar to `fetchLiveBalance()`
        $response = [
            'cash_total' => $cash_total,
            'mpesa_total' => $mpesa_total,
            'card_totals' => $card_totals,
        ];

        return response()->json($response);
    }

    public function getDeadStock(Request $request)
    {
        try {
            $branchId = $request->input('branch_id', auth()->user()->branch_id);
            $noDays = $request->input('no_days', 30); // Default to 30 days
            $qtyLimit = $request->input('qty_limit', 0); // Default to 0 (no sales)
            $dormantDate = now()->subDays($noDays)->startOfDay()->toDateTimeString();

            // Validate inputs
            if (!is_numeric($noDays) || $noDays < 0) {
                return response()->json(['error' => 'Invalid number of days'], 400);
            }
            if (!is_numeric($qtyLimit) || $qtyLimit < 0) {
                return response()->json(['error' => 'Invalid quantity limit'], 400);
            }
            if (!$branchId) {
                return response()->json(['error' => 'Branch ID is required'], 400);
            }

            // Get stock IDs with their total sold quantities within the no_days period
            $soldStockQuantities = AllSaleDetailsReport::withoutGlobalScope(BranchScope::class)
                //->where('branch_id', $branchId)
                ->where('order_date', '>=', $dormantDate)
                ->groupBy('stock_id')
                ->selectRaw('stock_id, SUM(qty) as total_sold_qty')
                ->pluck('total_sold_qty', 'stock_id')
                ->toArray();

            // Filter stock IDs where total sold qty is <= qty_limit or not sold at all
            $eligibleStockIds = array_filter($soldStockQuantities, function ($qty) use ($qtyLimit) {
                return $qty <= $qtyLimit;
            });
            $eligibleStockIds = array_keys($eligibleStockIds);

            // Main query to fetch purchased stock that is dormant and has low/no sales
            $main_query = Stock::select('stocks.*')
                ->join('purchase_order_receivable_with_batches as porwb', function ($join) use ($branchId) {
                    $join->on('stocks.id', '=', 'porwb.stock_id')
                  //      ->where('porwb.branch_id', '=', $branchId)
                        ->whereNull('porwb.deleted_at');
                })
                ->join('inventories', function ($join) use ($branchId) {
                    $join->on('stocks.id', '=', 'inventories.stock_id')
                       // ->where('inventories.branch_id', '=', $branchId)
                        ->whereNull('inventories.deleted_at');
                })
                ->whereNull('stocks.deleted_at') // Ensure stocks are not soft-deleted
                ->groupBy('stocks.id')
                ->selectRaw('SUM(COALESCE(inventories.qty, 0)) as current_qty')
                ->selectRaw('GROUP_CONCAT(DISTINCT porwb.batch_no) as batch_numbers')
                ->selectRaw('GROUP_CONCAT(DISTINCT porwb.expiry_date) as expiry_dates')
                ->selectRaw('COALESCE((SELECT SUM(qty) FROM all_sale_details_reports WHERE stock_id = stocks.id AND branch_id = ? AND order_date >= ?), 0) as total_sold_qty', [$branchId, $dormantDate])
                ->havingRaw('SUM(COALESCE(inventories.qty, 0)) > 0')
                ->havingRaw("MIN(porwb.received_date) <= '{$dormantDate}'");

            // Filter by eligible stock IDs (low/no sales) or stocks not sold at all
            $main_query->where(function ($query) use ($eligibleStockIds, $soldStockQuantities) {
                $query->whereNotIn('stocks.id', array_keys($soldStockQuantities)) // Not sold in period
                    ->orWhereIn('stocks.id', $eligibleStockIds); // Sold <= qty_limit
            });

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $main_query->where(function ($q) use ($request) {
                    $q->where('stocks.product_name', 'like', '%' . $request->search . '%')
                        ->orWhere('stocks.code', 'like', '%' . $request->search . '%');
                });
            }

            // Apply sorting by quantity if provided
            if ($request->sort_qty) {
                $main_query->orderBy('current_qty', $request->sort_qty);
            }

            // Paginate or get all results
            $res = $request->page > 0 ? $main_query->paginate(30) : $main_query->get();

            return response()->json($res);
        } catch (\Exception $e) {
            Log::error('Error in getDeadStock: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching dead stock'], 500);
        }
    }
  


    function getRedeemablePoints(Request $request)
    {
        $customer_id = $request->customer_id;
        $res = Customer::where('id', $customer_id)->selectRaw('(loyalty_points-redeemed_loyalty_points)  As redeemable_points')->first();
        return $res->redeemable_points;
    }
}
