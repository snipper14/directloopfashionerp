<?php

namespace App\Http\Controllers\Invoice;

use App\Sale\Sale;
use Carbon\Carbon;
use App\Stock\Stock;
use App\Cart\OrderCart;
use App\Invoices\Invoice;
use App\Customer\Customer;
use App\Inventory\Inventory;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Ledgers\GeneralLedger;
use App\Ledgers\LedgerAccount;
use App\Transaction\Transaction;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidateCustomerCreditLimit;
use App\Http\Requests\Invoice\InvoiceRequest;

class InvoiceController extends BaseController
{


    function creditInventoryAccount($request, $invoice_no, $inventory_amount, $unique_id)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "Inventory"])->first();

        if ($accounts_data) {
            $insert_data = array(
                'other_account_name' => "Invoice",
                'account_id' => $accounts_data->id,
                'account_type' => 'Invoice. Inventory',
                'entry_code' => $invoice_no,
                'ref' => $invoice_no,
                'details' => " Invoice",
                'credit_amount' => $inventory_amount,
                'description' => "Invoice Sale",
                'entry_date' => $request->invoice_date,
                'unique_id' => $unique_id

            );

            GeneralLedger::create($insert_data + Parent::user_branch_id());
        }
    }
    function debitCustLedger($invoice_date, $customer_id, $invoice_no, $total_debit, $unique_id)
    {
        $res =  CustomerLedger::where(['customer_id' => $customer_id])->orderBy('created_at', 'desc')->first();
        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $total_balance = $balance + $total_debit;
        $date = $invoice_date;
        $insert_data = array(

            'customer_id' => $customer_id,
            'ref' => $invoice_no,
            'type' => 'IN',
            'entry_date' => $date,
            'description' => 'Invoice',
            'debit' => $total_debit,
            'amount_due' => $total_debit,
            'balance' => $total_balance,
            'unique_id' => $unique_id
        );
        CustomerLedger::create($insert_data + Parent::user_branch_id());
    }

    function creditCustLedger(Request $request, $invoice_no, $total_debit)
    {

        $res =  CustomerLedger::where(['customer_id' => $request->customer_id])->orderby('created_at', 'desc')->first();
        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $total_balance = $balance - $total_debit;
        $date = date('Y-m-d');
        $insert_data = array(

            'customer_id' => $request->customer_id,
            'ref' => $invoice_no,
            'type' => 'IN',
            'entry_date' => $date,
            'description' => 'Cancelled Invoice',
            'credit' => $total_debit,
            'amount_due' => $total_debit,
            'balance' => $total_balance
        );

        CustomerLedger::create($insert_data + Parent::user_branch_id());
        DB::enableQueryLog();
        $cust = Customer::where('id', $request->customer_id)->first()->company_name;
    }

    public function orders(Request $request)
    {
        $order_no = $request->route('order_no');
        $data = Sale::with(['stock', 'customer'])->where('order_no', $order_no)->get();
        return response()->json(['results' => $data]);
    }


    function invoiceNumber($order_no)
    {

        $res = Invoice::where('order_no', $order_no)->first();
        if ($res) {
            return  $res->invoice_no;
        }
        $latest = Invoice::orderBy('id', 'desc')->first();

        $random_id = rand(1000, 10000);

        $today = Carbon::today()->format('dmy');
        $string = preg_replace("/[^0-9\.]/", '', $random_id);
        $invoiceNo = $today . '-' . 'INV' . sprintf('%04d', $string);
        $isUnique = Invoice::where('invoice_no', $invoiceNo)->first();
        if ($isUnique) {
            $invoiceNo = $today . '-' . 'INV' . ($latest->id + 1);
        }
        return  $invoiceNo . Parent::user_id();
    }
    function invoiceNumberFinal()
    {
        $invoice_no = 'INV' . '1';
        $res = Invoice::orderBy('id', 'DESC')->first();
        if ($res) {
            $prev_invoice_no = $res->invoice_no;

            $text_to_remove = "INV";
            $invoice_no_sanitized = str_replace($text_to_remove, "", $prev_invoice_no);
            $current_invoice_no = $invoice_no_sanitized + 1;
            $invoice_no = 'INV' . $current_invoice_no;
        }

        return  $invoice_no;
    }
    private function fetchQuery()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Invoice::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('order_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('order_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('invoice_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('d_note', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('notes', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('batch_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('customer', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('stock', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('department', function ($query) {
                        $query->where('department', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('user', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('invoice_date', [$from, $to]);
                }
            )
            ->when(
                request('department_id', '') != '',
                function ($query) use ($from, $to) {

                    $query->where('department_id', request('department_id'));
                }
            )
            ->when(
                request('customer_id', '') != '',
                function ($query) use ($from, $to) {

                    $query->where('customer_id', request('customer_id'));
                }
            )
            ->when(
                request('user_id', '') != '',
                function ($query) use ($from, $to) {

                    $query->where('user_id', request('user_id'));
                }
            )
            ->with(['stock.product_category', 'customer', 'user', 'branch', 'department']);
        return $raw_query;
    }
    public function fetch()
    {
        $raw_query = $this->fetchQuery()
            ->groupBy('order_no')
            ->selectRaw('*, sum(qty) as sum_qty,sum(row_total) as invoice_total,sum(tax_amount) as total_tax,sum(discount) as sum_discount')

            ->when(request('sort_total'), function ($query) {
                $query->orderBy('invoice_total', request('sort_total'));
            })
            ->when(request('sort_qty'), function ($query) {
                $query->orderBy('sum_qty', request('sort_qty'));
            })
            ->when(request('sort_vat'), function ($query) {
                $query->orderBy('total_tax', request('sort_vat'));
            })->orderBy('id', 'DESC');

        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(35);
        }
        return response()->json($data);
    }
    public function fetchexecutiveProductSalesReport()
    {
        $raw_query = $this->fetchQuery()
            ->selectRaw('*, sum(qty*purchase_price) as sum_purchase_price')->groupBy('id')
            ->orderBy('id', 'DESC');

        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(35);
        }
        return response()->json($data);
    }
    public function fetchGroupedUserTotalsReport()
    {
        $raw_query = $this->fetchQuery()
            ->selectRaw('*, sum(qty*purchase_price) as sum_purchase_price,sum(qty) as sum_qty,sum(row_total) as invoice_total')
            ->groupBy('user_id')
            ->orderBy('invoice_total', 'DESC');

        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(35);
        }
        return response()->json($data);
    }
    public function fetchGroupedLocationTotalsReport()
    {
        $raw_query = $this->fetchQuery()
            ->selectRaw('*, sum(qty*purchase_price) as sum_purchase_price,sum(qty) as sum_qty,sum(row_total) as invoice_total')
            ->groupBy('department_id')
            ->orderBy('invoice_total', 'DESC');

        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(35);
        }
        return response()->json($data);
    }
    function fetchfecutiveCummulativeProductSalesReport()
    {
        $raw_query = $this->fetchQuery()
            ->groupBy('stock_id')
            ->selectRaw('*, sum(qty) as sum_qty, sum(qty*purchase_price) as sum_purchase_price,sum(row_total) as invoice_total')
            ->orderBy('id', 'DESC');

        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(35);
        }
        return response()->json($data);
    }
    public function fetchTotals()
    {
        $raw_query = $this->fetchQuery()
            ->selectRaw('sum(tax_amount) AS total_vat,sum(qty) as sum_qty,sum(row_total) as invoice_total');



        $res = $raw_query->first();

        return $res;
    }
    public function fetchCustomerGroupedInvoiceTotals()
    {
        $raw_query = $this->fetchQuery()

            ->selectRaw('*,sum(qty) as sum_qty,
            sum(row_total) as invoice_total,
            SUM(amount_paid) AS sum_amount_paid,
            SUM(unpaid_amount) AS sum_unpaid_amount,
            SUM(tax_amount) AS sum_tax_amount')->groupBy('customer_id');



        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(105);
        }
        return response()->json($data);
    }

    public function invoiceItems(Request $request)
    {
        $invoice_no = $request->route('invoice_no');
        $data = Invoice::with(['stock', 'customer', 'user'])->where(['invoice_no' => $invoice_no, 'branch_id' => Parent::branch_id()])->get();
        return response()->json(["results" => $data]);
    }

    public function cancelInvoice(Request $request)
    {
        DB::transaction(function () use ($request) {
            $invoice_no = $request->invoice_no;
            $results = Invoice::where(['invoice_no' => $invoice_no, 'branch_id' => Parent::branch_id()])->get();
            $inv =   Invoice::where(['invoice_no' => $invoice_no])->first();
            CustomerLedger::bulkDelete(['unique_id' => $inv->unique_id]);
            GeneralLedger::bulkDelete(['unique_id' => $inv->unique_id]);
            if (!$results->isEmpty()) {

                Sale::where(['order_no' => $results[0]->order_no])->delete();
                $order_total = 0;

                foreach ($results as $value) {
                    if (Parent::isInventory($value['stock_id'])) {
                        Inventory::where([
                            'department_id' => $value['department_id'],
                            'stock_id' => $value['stock_id'],

                        ])->increment('qty', $value['qty']);
                    }
                    Invoice::find($value['id'])->delete();

                    $order_total += $value['row_total'];
                }
            }
        }, 5);
    }

    public function generateInvoice(Request $request)
    {
        $invoice_no = request('invoice_no');
        $data = Invoice::with(['stock',  'customer', 'branch', 'user'])->where('invoice_no', $invoice_no)->get();
        $folderPath = public_path('pdf');

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . $invoice_no . '.pdf';
        if (request('discount_status') == "show") {
            $pdf = PDF::loadView('pdf.invoice_discount', compact('data'));
        } else {
            $pdf = PDF::loadView('pdf.invoice', compact('data'));
        }

        $pdf->save($path);
        return response()->download($path);
    }

    public function fetchUnpaidInvoices()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = Invoice::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('invoice_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('d_note', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('notes', 'LIKE', '%' . request('search') . '%');
            });
        })->where([
            'customer_id' => request('customer_id')
        ])
            ->where('unpaid_amount', '>', 0)
            ->with(['stock', 'customer', 'user', 'branch', 'department'])->groupBy('order_no')
            ->selectRaw('*, sum(qty) as sum_qty,sum(row_total) as invoice_total')
            ->orderBy('id', 'DESC');

        $data =  $raw_query->get();
        if (request('page') > 0) {
            $data =  $raw_query->paginate(25);
        }
        return response()->json($data);
    }

    function fetchGroupedCategory()
    {




        $user_id = request('user_id');


        $main_query = Invoice::with(["stock", "product_category"]);
        if (request('from') != "" && request('to') != "") {
            // $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
            // $to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();
            $from = request('from');
            $to = request('to');
            $main_query->whereBetween('invoice_date', [$from, $to]);
        }
        $main_query->when($user_id != '', function ($query) use ($user_id) {

            $query->where('user_id',  $user_id);
        });

        $main_query->selectRaw("*,SUM(row_total) AS sum_row_total")->groupBy('product_category_id');
        $res1 = $main_query->get();
        $category_array = array();

        foreach ($res1 as $value) {

            $raw_query =  Invoice::with(['stock'])
                ->whereHas('stock', function ($query) use ($value) {
                    $query->where('product_category_id', $value['product_category_id']);
                });

            if (request('from') != "" && request('to') != "") {
                //  $from = Carbon::createFromFormat('Y-m-d', request('from'))->startOfDay();
                //$to = Carbon::createFromFormat('Y-m-d', request('to'))->endOfDay();
                $from = request('from');
                $to = request('to');
                $raw_query->whereBetween('created_at', [$from, $to]);
            }
            $raw_query->selectRaw("stock_id,price,
            SUM(row_total) AS sum_row_total,
             SUM(qty) AS sum_qty")
                ->groupBy(['stock_id']);
            $res3 = $raw_query->get()->toArray();

            $category_array[] = array(
                "category_id" => $value['product_category_id'],
                "category_name" => $value['product_category']->name,
                'category_total' => $value['sum_row_total'],
                "data" => $res3
            );
        }
        return response()->json($category_array);
    }
    function groupedDeptTotals()
    {
        $main_query = Invoice::with(["stock", "department"]);

        if (request('from') != "" && request('to') != "") {

            $from = request('from');
            $to = request('to');
            $main_query->whereBetween('created_at', [$from, $to]);
        }
        $main_query->selectRaw("*,SUM(row_total) AS sum_row_total")->groupBy('department_id');
        $res1 = $main_query->get();



        return response()->json($res1);
    }

    function saveNotes(Request $request)
    {
        $this->validate($request, [

            'notes' => 'nullable',
            'd_note' => 'nullable',
            'invoice_no' => 'required',
        ]);
        Invoice::where('invoice_no', $request->invoice_no)->update(['notes' => $request->notes, 'd_note' => $request->d_note]);
        return true;
    }
    function saveAndDownloadInvoice(Request $request)
    {

        $invoice_no = $this->invoiceNumberFinal();
        DB::transaction(function () use ($invoice_no, $request) {
            $results = OrderCart::where(['customer_id' => request('customer_id')])->get();

            // $invoice_no = $this->invoiceNumberFinal(); //$this->invoiceNumber($request->order_no);
            $total_invoice = 0;
            $total_vat = 0;
            $unique_id = Parent::uniqueID();
            foreach ($results as $value) {
                $total_invoice += $value['row_total'];
                $total_vat += $value['tax_amount'];
                Invoice::create([

                    "stock_id" => $value['stock_id'],
                    'customer_id' => $value['customer_id'],
                    'department_id' => $value['department_id'],
                    'product_category_id' => $value['product_category_id'],
                    'discount' => $value['discount'],
                    'purchase_price' => $value['purchase_price'],
                    'order_date' => $value['order_date'],
                    'order_no' => $value['order_no'],
                    'qty' => $value['qty'],
                    'price' => $value['selling_price'],
                    'row_total' => $value['row_total'],

                    'tax_amount' => $value['tax_amount'],
                    "invoice_date" => request('invoice_date'),
                    "invoice_no" => $invoice_no,
                    "unpaid_amount" => request('grand_total'),
                    "order_user_id" => $value['user_id'],
                    "notes" => request('notes'),
                    "d_note" => request('d_note'),
                    "cu_invoice_no" => request('cu_invoice_no'),
                    'batch_no' => $value['batch_no'],
                    'unique_id' => $unique_id

                ] + Parent::user_branch_id());
            }
            OrderCart::where(['customer_id' => request('customer_id')])->delete();
            $this->debitCustLedger(request('invoice_date'), request('customer_id'), $invoice_no, $total_invoice, $unique_id);
            $this->insertOutputVATAccount($total_vat,  $invoice_no, $unique_id);
            $inventory_amount = $total_invoice - $total_vat;
            $this->creditInventoryAccount($request, $invoice_no, $inventory_amount, $unique_id);
        }, 5);



        $data = Invoice::with(['stock',  'customer', 'branch', 'user'])->where([
            'invoice_no' => $invoice_no,
            'customer_id' => request('customer_id')
        ])->get();
        return response()->json($data);
    }

    function insertOutputVATAccount($total_vat, $invoice_no, $unique_id)
    {
        $accounts_data =  LedgerAccount::where(['account_type' => "output VAT"])->first();
        if ($total_vat == 0) {
            return;
        }
        if (!$accounts_data) {
            return;
        }
        $customer = Customer::where('id', request('customer_id'))->first();

        $insert_data = array(
            'other_account_name' => $customer->company_name,
            'account_id' => $accounts_data->id,
            'account_type' => 'Output VAT (Invoice)',
            'entry_code' => $invoice_no,
            'ref' => $invoice_no,
            'details' => "Output VAT ( Invoice)",
            'credit_amount' => $total_vat,
            'description' => "Invoice VAT",
            'entry_date' => request('invoice_date'),
            'unique_id' => $unique_id

        );

        GeneralLedger::create($insert_data + Parent::user_branch_id());
    }
    function getInvoicePrint()
    {
        $invoice_no = request('invoice_no');
        $data = Invoice::with(['stock',  'customer', 'branch', 'user'])->where([
            'invoice_no' => $invoice_no,
            'customer_id' => request('customer_id')
        ])->get();
        return response()->json($data);
    }


    public function profitLossInvoiceSummary()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $main_query = Invoice::with([
            'customer',
            'user',
            'product_category',
            'stock',

        ]);

        $main_query->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {
            $query->whereBetween('invoice_date', [$from, $to]);
        });

        $main_query->when(request('search', '') != '', function ($query) {
            $query->where('invoice_no', 'LIKE', '%' . request('search') . '%')
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
        invoices.id AS invoice_id,
        SUM(row_total) AS sum_row_total, 
        SUM(purchase_price * qty) AS sum_cost_total, 
        SUM(tax_amount) AS sum_row_vat, 
        SUM(qty) AS sum_qty, 
        (SUM(row_total) - SUM(purchase_price * qty)) AS profit_loss, 
        (CASE 
            WHEN SUM(row_total) = 0 THEN 0 
            ELSE (((SUM(row_total) - SUM(purchase_price * qty)) * 100) / NULLIF(SUM(row_total), 0)) 
        END) AS profit_margin
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
        $main_query = Invoice::when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('invoice_date', [$from, $to]);
        });
        $main_query->when(request('search', '') != '', function ($query) {

            $query->where('invoice_no', 'LIKE', '%' . request('search') . '%')

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
        SUM(purchase_price * qty) AS sum_cost_total,
        SUM(tax_amount) AS sum_row_vat,
        SUM(qty) AS sum_qty
      
        ');


        $res = $main_query->first();

        return response()->json($res);
    }

    public function itemizedReport()
    {
        $searchParams = [
            'stock_name' => request('search'),
            'customer_id' => request('customer_id'),

        ];
        $from = request('from');
        $to = request('to');

        // Base query for totals and grouping
        $baseQuery = Invoice::with(['customer', 'stock', 'branch']);


        if (request('is_grouped')) {
            $baseQuery->groupBy('stock_id');
        }
        // Apply filters
        if (!empty($searchParams['stock_name'])) {
            $baseQuery->whereHas('stock', function ($q) use ($searchParams) {
                $q->where('name', 'like', '%' . $searchParams['stock_name'] . '%');
            });
        }

        if (!empty($searchParams['customer_id'])) {
            $baseQuery->where('customer_id', $searchParams['customer_id']);
        }
        //DATE FILTERS
        $baseQuery->when(request('from', '') != '' && request('to', '') != '', function ($query) use ($from, $to) {

            $query->whereBetween('created_at', [$from, $to]);
        });
        // Apply sorting

        if (request('sort_stock_name')) {
            $baseQuery->join('stocks', 'stocks.id', '=', 'invoices.stock_id')
                ->orderBy('stocks.name', request('sort_stock_name'))
                ->select('invoices.*'); // ensure only invoice columns are returned
        }
        if (request('sort_quantity')) {
            $baseQuery->orderBy('qty',  request('sort_quantity'));
        }

        if (request('sort_selling_price')) {
            $baseQuery->orderBy('price',  request('sort_selling_price'));
        }

        if (request('sort_purchase_amount')) {
            $baseQuery->orderBy('purchase_price',  request('sort_purchase_amount'));
        }
        if (request('sort_total_amount')) {
            $baseQuery->orderBy('row_total',  request('sort_total_amount'));
        }

        if (request('sort_date')) {
            $baseQuery->orderBy('created_at',  request('sort_date'));
        }


        // Get the grouped results
        $groupedResults = request('page') > 0 ? $baseQuery->paginate(50) : $baseQuery->get();



        // Format the stock items array
        return response()->json($groupedResults);
    }
    public function scopeWithStockSummary()
    {
        $searchParams = [
            'stock_name' => request('search'),
            'customer_id' => request('customer_id'),

        ];

        // Base query for totals and grouping
        $baseQuery = Invoice::with(['customer', 'stock'])
            ->selectRaw('
                stock_id,
                SUM(invoices.qty) as total_quantity,
                SUM(row_total) as total_amount,
                COUNT(*) as invoice_count
            ')
            ->groupBy('stock_id');

        // Apply filters
        if (!empty($searchParams['stock_name'])) {
            $baseQuery->whereHas('stock', function ($q) use ($searchParams) {
                $q->where('name', 'like', '%' . $searchParams['stock_name'] . '%');
            });
        }

        if (!empty($searchParams['customer_id'])) {
            $baseQuery->where('customer_id', $searchParams['customer_id']);
        }
        //DATE FILTERS
        $from = request('from');
        $to = request('to');

        if (!empty($from)) {
            // Convert to start of the day
            $baseQuery->whereDate('created_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if (!empty($to)) {
            // Convert to end of the day
            $baseQuery->whereDate('created_at', '<=', Carbon::parse($to)->endOfDay());
        }
        // Apply sorting

        if (request('sort_stock_name')) {
            $baseQuery->join('stocks', 'invoices.stock_id', '=', 'stocks.id')
                ->orderBy('stocks.name', request('sort_stock_name'));
        }
        if (request('sort_total_quantity')) {
            $baseQuery->orderBy('total_quantity',  request('sort_total_quantity'));
        }
        if (request('sort_total_amount')) {
            $baseQuery->orderBy('total_amount',  request('sort_total_amount'));
        }


        // Get the grouped results
        $groupedResults = request('page') > 0 ? $baseQuery->paginate(50) : $baseQuery->get();



        // Format the stock items array


        // Return both arrays
        $result = [
            'totals' => $this->totalItemized(),
            'stock_items' => $groupedResults,

        ];

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    function totalItemized()
    {
        $searchParams = [
            'stock_name' => request('search'),
            'customer_id' => request('customer_id'),

        ];
        $totalsQuery = Invoice::query();

        if (!empty($searchParams['stock_name'])) {
            $totalsQuery->whereHas('stock', function ($q) use ($searchParams) {
                $q->where('name', 'like', '%' . $searchParams['stock_name'] . '%');
            });
        }

        if (!empty($searchParams['customer_id'])) {
            $totalsQuery->where('customer_id', $searchParams['customer_id']);
        }

        if (!empty($from)) {
            $totalsQuery->whereDate('created_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if (!empty($to)) {
            $totalsQuery->whereDate('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        $totals = $totalsQuery->selectRaw('
    SUM(qty) as overall_quantity,
    SUM(row_total) as overall_amount,
    COUNT(*) as overall_invoice_count
')->first();
        return $totals;
    }
}
