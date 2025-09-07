<?php

namespace App\Http\Controllers\SupplierInvoice;

use App\Suppliers\Supplier;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Transaction\Transaction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\SupplierInvoice\SupplierInvoice;
use App\Http\Requests\SupplierInvoice\SupplierInvoiceRequest;
use App\Http\Requests\SupplierInvoiceParticulars\SupplierInvoiceParticularsRequest;
use App\Scopes\BranchScope;
use App\SupplierInvoiceParticulars\SupplierInvoiceParticular;

class SupplierInvoiceController extends BaseController
{
    public function create(SupplierInvoiceRequest $request)
    {
        $data =   SupplierInvoice::create($request->validated() +
            ['unpaid_amount' => $request->invoice_total]
            + Parent::user_branch_id());
        if ($data->exists) {
            $this->creditLedger($request);
            return response()->json(['results' => $data]);
        }
    }

    public function creditLedger(SupplierInvoiceRequest $request)
    {
        $res =  CreditLedger::where(['supplier_id' => $request->supplier_id])->orderBy('id', 'desc')->first();
        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $total_credit =  $request->invoice_total;
        $total_balance = $balance + $total_credit;

        $data = array(

            'supplier_id' => $request->supplier_id,
            'ref' => $request->invoice_no,
            'type' => 'IN',
            'entry_date' => $request->invoice_date,
            'description' => $request->description,
            'credit' => $request->invoice_total,
            'amount_due' => $total_credit,
            'balance' => $total_balance
        );
        CreditLedger::create($data + Parent::user_branch_id());

        $supp = Supplier::where('id', $request->supplier_id)->first();
        $insert_ledger_data = array(

            'ref' => $request->invoice_no,
            'type' => 'IN',
            'entry_date' => $request->invoice_date,
            'description' => "Supplier Invoice",
            'credit' => $supp->company_name,
            'debit' => "Stock Acc",
            'amount' => $total_credit,
        );
        Transaction::create($insert_ledger_data + Parent::user_branch_id());
    }
    public function save_particulars(SupplierInvoiceParticularsRequest $request)
    {
        $data = SupplierInvoiceParticular::create($request->validated());
        $res = SupplierInvoiceParticular::with(['stock'])->where(["invoice_no" => $request->invoice_no, 'supplier_invoice_id' => $request->supplier_invoice_id])->orderBy('id', 'DESC')->get();

        return response()->json(['results' => $res]);
    }
    public function fetch()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = SupplierInvoice::withoutGlobalScope(BranchScope::class)->where('supplier_invoices.branch_id', Parent::branch_id())->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('invoice_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('invoice_date', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['supplier'])
            ->when(request('sort_supplier'), function ($q) {
                $q->join('suppliers', 'suppliers.id', '=', 'supplier_invoices.supplier_id')
                    ->orderBy('suppliers.company_name', request('sort_supplier'));
            })
            ->when(request('sort_invoice_date'), function ($q) {

                $q->orderBy('supplier_invoices.invoice_date', request('sort_invoice_date'));
            })
            ->when(request('sort_invoice_total'), function ($q) {

                $q->orderBy('supplier_invoices.invoice_total', request('sort_invoice_total'));
            })
            ->when(request('sort_paid_amount'), function ($q) {

                $q->orderBy('supplier_invoices.paid_amount', request('sort_paid_amount'));
            })
            ->when(request('sort_unpaid_amount'), function ($q) {

                $q->orderBy('supplier_invoices.unpaid_amount', request('sort_unpaid_amount'));
            })
            ->orderBy('id', 'DESC')
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('supplier_invoices.invoice_date', [$from, $to]);
                }
            )->select('supplier_invoices.*');

        $data = $raw_query->get();
        if (request('page') > 0) {
            $data = $raw_query->paginate(30);
            return response()->json(['results' => $data]);
        }
        return response()->json(['results' => $data]);
    }
    public function fetchGroupedInvoices()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = SupplierInvoice::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('invoice_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('invoice_date', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['supplier'])->orderBy('id', 'DESC')
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('invoice_date', [$from, $to]);
                }
            );
        $raw_query->selectRaw("*,SUM(invoice_total) AS sum_invoice_total,SUM(paid_amount) AS sum_paid_amount,SUM(unpaid_amount) AS sum_unpaid_amount")->groupBy('supplier_id');
        $data = $raw_query->get();
        if (request('page') > 0) {
            $data = $raw_query->paginate(30);
            return response()->json($data);
        }
        return response()->json($data);
    }
    public function fetchTotal()
    {
        $from = date(request('from'));
        $to = date(request('to'));
        $raw_query = SupplierInvoice::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('invoice_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('invoice_date', 'LIKE', '%' . request('search') . '%')

                    ->orWhereHas('supplier', function ($query) {
                        $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->when(
            request('from', '') != '' && request('to', '') != '',
            function ($query) use ($from, $to) {

                $query->whereBetween('invoice_date', [$from, $to]);
            }
        )
            ->selectRaw('SUM(invoice_total) AS sum_invoice');

        $data = $raw_query->first();

        return response()->json($data);
    }


    public function invoiceParticulars(Request $request)
    {
        $res = SupplierInvoiceParticular::with(['stock'])->where(["invoice_no" => $request->invoice_no, 'supplier_invoice_id' => $request->supplier_invoice_id])->orderBy('id', 'DESC')->get();

        return response()->json(['results' => $res]);
    }
    public function destroy(Request $request)
    {
        $this->debitLedger($request);
        SupplierInvoice::find($request->supplier_invoice_id)->delete();
        SupplierInvoiceParticular::where([
            "invoice_no" => $request->invoice_no,
            'supplier_invoice_id' => $request->supplier_invoice_id
        ])->delete();
        return true;
    }

    public function debitLedger(Request $request)
    {
        $invoice_res = SupplierInvoice::where(['invoice_no' => $request->invoice_no, 'supplier_id' => $request->supplier_id])->with('supplier')->first();
        $res =  CreditLedger::where(['supplier_id' => $invoice_res->supplier_id])->orderBy('id', 'desc')->first();
        $balance = 0;
        if ($res) {
            $balance = $res->balance;
        }
        $total_credit =  $invoice_res->invoice_total;
        $total_balance = $balance - $total_credit;

        $data = array(

            'supplier_id' => $invoice_res->supplier_id,
            'ref' => $request->invoice_no,
            'type' => 'IN',
            'entry_date' => date('Y-m-d'),
            'description' => "Cancelled Invoice",
            'debit' => $invoice_res->invoice_total,
            'amount_due' => $total_credit,
            'balance' => $total_balance
        );
        CreditLedger::create($data + Parent::user_branch_id());

      
    }
    public function destroy_particular(Request $request)
    {
        $id = $request->route('id');
        SupplierInvoiceParticular::find($id)->delete();
        return true;
    }


    public function fetchInvoice()
    {


        $main_query = CreditLedger::withoutGlobalScope(BranchScope::class)//->where('credit_ledgers.branch_id',Parent::branch_id())
        ->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            });
        })->with(['supplier'])->groupBy('supplier_id') //->where('balance','!=','0')
          
            ->when(request('sort_supplier'), function ($q) {
                $q->join('suppliers', 'suppliers.id', '=', 'credit_ledgers.supplier_id')
                    ->orderBy('suppliers.company_name', request('sort_supplier'));
            })
            ->when(request('sort_total_debit'), function ($q) {
               
                    $q->orderBy('total_debit', request('sort_total_debit'));
            })
            ->when(request('sort_total_credit'), function ($q) {
               
                $q->orderBy('total_credit', request('sort_total_credit'));
        })
        ->when(request('sort_actual_balance'), function ($q) {
               
            $q->orderBy('actual_balance', request('sort_actual_balance'));
    })
            ->selectRaw('credit_ledgers.*,SUM(credit_ledgers.debit) as total_debit,SUM(credit_ledgers.credit) AS total_credit,
            (SUM(credit_ledgers.credit)-SUM(credit_ledgers.debit)) AS actual_balance')->orderBy('actual_balance', "DESC");
        $res = $main_query->get();
        if (request('page') > 0) {
            $res = $main_query->paginate(50);
        }
        return response()->json($res);
    }
    public function fetchSupplierCredit()
    {


        $res = CreditLedger::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            });
        })->with(['supplier'])->groupBy('supplier_id')
            ->where(["supplier_id" => request("supplier_id")])
            ->selectRaw('*,SUM(debit) as total_debit,SUM(credit) AS total_credit')
            //->where('total_credit-total_debit','!=','0')
            ->orderBy('balance', "DESC")->first();
        return response()->json($res);
    }
    function fetchTotalBalances()
    {
        $res = CreditLedger::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereHas('supplier', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            });
        })->selectRaw('SUM(debit) as total_debit,SUM(credit) AS total_credit,
        (SUM(credit)-SUM(debit)) AS actual_balance')->first();
        return response()->json($res);
    }
    public function fetchAllInvoice()
    {
        $data = Supplier::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('company_name', 'LIKE', '%' . request('search') . '%');
            });
        })->with(['invoice_sum', 'payment_totals'])->where(Parent::branch_array())->orderBy('id', 'DESC')->get();
        return response()->json(['results' => $data]);
    }

    public function fetchUnpaidSupplierInvoices()
    {
        $raw_query = SupplierInvoice::where(['supplier_id' => request('supplier_id')])
            ->where('unpaid_amount', '>', 0)
            ->orderBy('id', 'DESC');

        $data = $raw_query->get();

        return response()->json($data);
    }

    function importOpeningBalance(Request $request)
    {
        ini_set('max_execution_time', 2400);
        $request->validate([
            'import_file' => 'required|max:50000'
        ]);
        $folderPath = public_path('uploads');
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $file = $request->file('import_file');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();


        //  return response()->json(['message' => 'uploaded successfully'], 200);

        $valid_extension = array("csv");

        // 2MB in Bytes
        $maxFileSize = 2097152;

        // Check file extension
        if (in_array(strtolower($extension), $valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {

                // File upload location
                $location = 'uploads';

                // Upload file
                $file->move($location, $filename);

                // Import CSV to Database
                $filepath = public_path($location . "/" . $filename);

                // Reading file
                $file = fopen($filepath, "r");

                $importData_arr = array();
                $i = 0;

                while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                    $num = count($filedata);

                    // Skip first row (Remove below comment if you want to skip the first row)
                    if ($i == 0) {
                        $i++;
                        continue;
                    }
                    for ($c = 0; $c < $num; $c++) {
                        $importData_arr[$i][] = $filedata[$c];
                    }
                    $i++;
                }
                fclose($file);

                // Insert to MySQL database

                foreach ($importData_arr as $importData) {

                    $cust_data = Supplier::where('id', $importData[1])->first();
                    if ($cust_data) {

                        $insertData = array(


                            'balance' => 0,
                            'supplier_id' => $importData[1],

                            'entry_date' => date("Y-m-d"),
                            'description' => 'Opening Balance',
                            'debit' => $importData[2],
                            'credit' => $importData[3],
                            'type' => 'B',
                            'ref' => 'Balance',
                        );
                        CreditLedger::create($insertData + Parent::user_branch_id());
                        $cust = Supplier::where('id', $cust_data->id)->first();
                        $insert_ledger_data = array(


                            'ref' => "balance",
                            'entry_date' => date("Y-m-d"),
                            'description' =>  'Initial Balance',
                            'credit' => "Balance",
                            'debit' => $cust->company_name,
                            'amount' => $importData[2],
                        );
                        Transaction::create($insert_ledger_data + Parent::user_branch_id());
                    }
                }

                return response()->json(["status" => "success", "message" => "successfully uploaded"], 200);
            } else {
                return response()->json(["status" => "error", "message" => "File too large. File must be less than 2MB."], 200);
            }
        } else {
            return response()->json(["status" => "error", "message" => "Invalid File Extension."], 200);
        }
    }
}
