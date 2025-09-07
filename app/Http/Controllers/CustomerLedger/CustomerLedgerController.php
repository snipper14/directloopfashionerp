<?php

namespace App\Http\Controllers\CustomerLedger;

use Carbon\Carbon;
use App\Customer\Customer;
use Illuminate\Http\Request;
use App\Ledger\CustomerLedger;
use App\Transaction\Transaction;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Scopes\BranchScope;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerLedgerController extends BaseController
{
    public function create(Request $request)
    {
        $this->validate($request, [
            'dr_balance' => 'required|gte:0',
            'cr_balance' => 'required|gte:0',
            'customer_id' => 'required',
            'description' => "required"
        ]);
        DB::transaction(function () use ($request) {




            CustomerLedger::create([
                'debit' => $request->dr_balance,
                'credit' => $request->cr_balance,
                'customer_id' => $request->customer_id,

                'entry_date' => $request->entry_date,
                'description' => $request->description,

                'type' => 'B',
                'ref' => 'Balance',
            ] + Parent::user_branch_id());

            $cust = Customer::where('id', $request->customer_id)->first();
        }, 5);
        return true;
    }

    public function customerLedgerStatement(Request $request)
    {
        $from = date(request('from'));
        $to = date(request('to'));
        //->where('customer_ledgers.branch_id', Parent::branch_id())
        $main_query =  CustomerLedger::withoutGlobalScope(BranchScope::class)->with(['user', 'branch', 'customer'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('entry_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('debit', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('credit', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('company_name', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('company_phone', 'LIKE', '%' . request('search') . '%')
                            ->orWhere('pin', 'LIKE', '%' . request('search') . '%');
                    })

                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )->when(request('search', '') != '', function ($query) {
                $query->whereHas('customer', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            })

            ->groupBy('customer_ledgers.customer_id')
            ->when(request('sort_total_balance'), function ($query) {
                $query->orderBy('total_balance', request('sort_total_balance'));
            })
            ->when(request('sort_customer'), function ($q) {
                $q->join('customers', 'customers.id', '=', 'customer_ledgers.customer_id')
                    ->orderBy('customers.company_name', request('sort_customer'));
            })
            ->orderBy('entry_date', 'ASC')->selectRaw('customer_ledgers.*,SUM(customer_ledgers.debit-customer_ledgers.credit) AS total_balance');
        $paginatedData = $main_query->get();
        if (request('page') > 0) {
            $paginatedData = $main_query->paginate(30);
        }

        return response()->json($paginatedData);
    }
    function getAmountOwed(Request $request)
    {
        $customer_id = $request->customer_id;
        $res = CustomerLedger::where('customer_id', $customer_id)->selectRaw('*,SUM(debit-credit) AS total_balance')->first();

        $pending_amount = 0;
        if ($res) {
            $pending_amount = $res->total_balance;
        }
        return $pending_amount;
    }
    function queryStatement()
    {
        $id = request('customer_id');
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  CustomerLedger::with(['user', 'branch', 'customer'])->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('entry_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('debit', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('credit', 'LIKE', '%' . request('search') . '%');
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )->where(['customer_id' => $id])->orderBy('entry_date', 'ASC');
        $paginatedData = $main_query->get();
        if (request('page') > 0) {
            $paginatedData = $main_query->paginate(100);
        }
        return $paginatedData;
    }
    function paginatedStatement()
    {
        $paginatedData = $this->queryStatement();

        $data = [];
        $current_total = 0;
        foreach ($paginatedData as  $value) {

            $current_total += $value->debit;
            $current_total -= $value->credit;


            $data[] = [
                'id' => $value->id,
                'ref' => $value->ref,
                'entry_date' => $value->entry_date,
                'type' => $value->type,
                'description' => $value->description,
                'debit' => $value->debit,
                'credit' => $value->credit,
                'customer' => $value->customer,
                'user' => $value->user,
                'branch' => $value->branch,
                "balance" => $current_total
            ];
        }
        return $data;
    }
    

private function baseCustomerQueryDesc(Request $request)
{
    $id   = $request->customer_id;
    $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : null;
    $to   = $request->filled('to')   ? date('Y-m-d', strtotime($request->to))   : null;

    return CustomerLedger::with(['user','branch','customer'])
        ->when($request->filled('search'), function ($query) use ($request) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ref', 'like', "%$s%")
                  ->orWhere('type', 'like', "%$s%")
                  ->orWhere('entry_date', 'like', "%$s%")
                  ->orWhere('debit', 'like', "%$s%")
                  ->orWhere('credit', 'like', "%$s%");
            });
        })
        ->when($from && $to, fn($q) => $q->whereBetween('entry_date', [$from, $to]))
        ->where('customer_id', $id)
        // newest first (we go backwards)
        ->orderBy('entry_date', 'desc')
        ->orderBy('id', 'desc');
}

public function fetchCustomerStatement(Request $request)
{
    $perPage = max(1, (int) $request->input('per_page', 100));

    // same filters & DESC order for all parts
    $base = $this->baseCustomerQueryDesc($request);

    // current page
    $pageQuery = (clone $base);
    $paginated = $pageQuery->paginate($perPage);

    if ($paginated->isEmpty()) {
        return response()->json($paginated);
    }

    // 1) Closing balance across ALL filtered rows (A/R: debit increases, credit decreases)
    $closing = (clone $base)
        ->selectRaw('COALESCE(SUM(debit - credit), 0) AS t')
        ->value('t');

    // 2) First (newest) row on this DESC page
    $first = $paginated->first();

    // 3) Sum of rows NEWER than the first row on this page
    $sumNewer = (clone $base)
        ->where(function ($q) use ($first) {
            $q->where('entry_date', '>', $first->entry_date)
              ->orWhere(function ($qq) use ($first) {
                  $qq->where('entry_date', $first->entry_date)
                     ->where('id', '>', $first->id);
              });
        })
        ->selectRaw('COALESCE(SUM(debit - credit), 0) AS s')
        ->value('s');

    // 4) Page starting balance = closing - sum(newer)
    $running = (float) $closing - (float) $sumNewer;

    // 5) Build page rows (newest → oldest), showing cumulative balance
    $rows = [];
    foreach ($paginated as $cl) {
        $delta = (float)$cl->debit - (float)$cl->credit; // customer: debit up, credit down

        $rows[] = [
            'id'          => $cl->id,
            'ref'         => $cl->ref,
            'entry_date'  => $cl->entry_date,
            'type'        => $cl->type,
            'description' => $cl->description,
            'debit'       => $cl->debit,
            'credit'      => $cl->credit,
            'customer'    => $cl->customer,
            'user'        => $cl->user,
            'branch'      => $cl->branch,
            'balance'     => $running, // ✅ does not reset across pages
        ];

        // step to next (older) row
        $running -= $delta;
    }

    $result = new LengthAwarePaginator(
        $rows,
        $paginated->total(),
        $paginated->perPage(),
        $paginated->currentPage(),
        ['path' => url()->current(), 'query' => request()->query()]
    );

    return response()->json($result);
}


    function fetchAll(Request $request)
    {

        $result =  $this->paginatedStatement();
        return response()->json($result);
    }

    public function generateCustTotal()
    {
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  CustomerLedger::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('ref', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('entry_date', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('debit', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('credit', 'LIKE', '%' . request('search') . '%');
            });
        })
            ->when(
                request('from', '') != '' && request('to', '') != '',
                function ($query) use ($from, $to) {

                    $query->whereBetween('entry_date', [$from, $to]);
                }
            )->when(request('search', '') != '', function ($query) {
                $query->whereHas('customer', function ($query) {
                    $query->where('company_name', 'LIKE', '%' . request('search') . '%');
                });
            })
            ->selectRaw('SUM(debit-credit) AS total_balance')

            ->orderBy('entry_date', 'ASC');
        $res = $main_query->first();

        return response()->json($res);
    }

    public function generateCusStatment(Request $request)
    {
        $id = request('customer_id');
        $data = $this->paginatedStatement();

        $current = $this->datePeriodReport($id, 0); //$current_res ? $current_res->balance : 0;

        $thirty = $this->datePeriodReport($id, 30);

        $sixty =  $this->datePeriodReport($id, 60);
        $ninety =  $this->datePeriodReport($id, 90);
        $one_20 =  $this->datePeriodReport($id, 120);
        $one_50 =  $this->datePeriodReport($id, 150);


        $one_80 = $this->datePeriodReport($id, 180);

        $folderPath = public_path('pdf');

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . 'customer_statement' . '.pdf';

        $pdf = PDF::loadView('pdf.customer_statement', [
            'data' => $data,
            'thirty' => $thirty,
            'current' => $current,
            'sixty' => $sixty,
            'ninety' => $ninety,
            'one_20' => $one_20,
            'one_50' => $one_50,
            'one_80' => $one_80,
        ]);
        $pdf->save($path);
        return response()->download($path);
    }
    function datePeriodReport($id, $days)
    {

        $res = CustomerLedger::where(['customer_id' => $id])->where('entry_date', '<=', Carbon::now()->subDays($days))->get();
        $current_total = 0;

        foreach ($res as  $value) {

            $current_total += $value->debit;
            $current_total -= $value->credit;
        }
        return $current_total;
    }
    function destroy(Request $request)
    {
        $id = $request->route('id');
        CustomerLedger::where('customer_id', $id)->delete();
        return true;
    }
    function deleteStatement(Request $request)
    {
        $id = $request->route('id');
        CustomerLedger::where('id', $id)->delete();
        return true;
    }





    public function import(Request $request)
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

                    $cust_data = Customer::where('id', $importData[1])->first();
                    if ($cust_data) {

                        $insertData = array(


                            'balance' => 0,
                            'customer_id' => $importData[1],

                            'entry_date' => date("Y-m-d"),
                            'description' => 'Opening Balance',
                            'debit' => $importData[2],
                            'credit' => $importData[3],
                            'type' => 'B',
                            'ref' => 'Balance',
                        );
                        CustomerLedger::create($insertData + Parent::user_branch_id());
                        $cust = Customer::where('id', $cust_data->id)->first();
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



        // Redirect to index

    }
    function checkIfRecordExist($id)
    {
        $res = CustomerLedger::where(['customer_id' => $id])->first();
        return $res ? true : false;
    }

    public function getExcell()
    {
        $result =  CustomerLedger::with(['customer' => function ($q) {
            $q->orderBy('company_name');
        }])->whereRaw('id IN (select MAX(id) FROM customer_ledgers GROUP BY customer_id)')
            ->get();
        return response()->json($result);
    }

    function fetchPaginatedCustomers($paginate = true)
    {
        $main_query = Customer::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('company_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('address', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('company_phone', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('postal_address', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('city', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('company_email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('bank_name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('bank_branch', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('account_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('contact_person', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('contact_email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('contact_phone', 'LIKE', '%' . request('search') . '%');
            });
        })
            ->when(request('customer_id', '') != '', function ($query) {
                $query->where('id', request('customer_id'));
            })
            ->orderBy('id', 'DESC');

        if ($paginate) {
            return $main_query->paginate(30);
        } else {
            return $main_query->get(); // 👈 return full list for manual pagination
        }
    }

    function agingQueryData()
    {
        $current_total = 0;
        $paginatedData = $this->fetchPaginatedCustomers();
        foreach ($paginatedData as  $value) {




            $data[] = [
                'current' => $this->datePeriodReport($value->id, 0),
                'thirty' => $this->datePeriodReport($value->id, 30),
                'sixty' => $this->datePeriodReport($value->id, 60),
                'ninety' => $this->datePeriodReport($value->id, 90),
                'one_twenty' => $this->datePeriodReport($value->id, 120),
                'one_fifty' => $this->datePeriodReport($value->id, 150),
                'one_eighty' => $this->datePeriodReport($value->id, 180),
                'customer' => $value


            ];
        }
        return $data;
    }
    public function getAgingReport()
    {
        // Get full list of customers (not paginated yet)
        $allCustomers = $this->fetchPaginatedCustomers(false); // We'll fix this below
        $currentPage = (int) request('page', 1);
        $perPage = 30;

        // Generate aging data for all customers
        $data = [];
        foreach ($allCustomers as $customer) {
            $data[] = [
                'current' => $this->datePeriodReport($customer->id, 0),
                'thirty' => $this->datePeriodReport($customer->id, 30),
                'sixty' => $this->datePeriodReport($customer->id, 60),
                'ninety' => $this->datePeriodReport($customer->id, 90),
                'one_twenty' => $this->datePeriodReport($customer->id, 120),
                'one_fifty' => $this->datePeriodReport($customer->id, 150),
                'one_eighty' => $this->datePeriodReport($customer->id, 180),
                'customer' => $customer,
            ];
        }

        // Sort if requested
        $sortColumn = request('sort_column');
        $sortDirection = strtolower(request('sort_direction')) === 'asc' ? 'asc' : 'desc';

        if (in_array($sortColumn, ['current', 'thirty', 'sixty', 'ninety', 'one_twenty', 'one_fifty', 'one_eighty'])) {
            usort($data, function ($a, $b) use ($sortColumn, $sortDirection) {
                $aVal = $a[$sortColumn] ?? 0;
                $bVal = $b[$sortColumn] ?? 0;
                return $sortDirection === 'asc'
                    ? $aVal <=> $bVal
                    : $bVal <=> $aVal;
            });
        }

        // Paginate manually
        $total = count($data);
        $items = array_slice($data, ($currentPage - 1) * $perPage, $perPage);

        $paginatedResult = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => url()->current()]
        );

        return response()->json($paginatedResult);
    }



    public function exportAgingReport()
    {
        $data = $this->agingQueryData();

        return response()->json($data);
    }

    public function fetchPrintStatement()
    {
        $id = request('customer_id');
        $data = $this->paginatedStatement();

        $current = $this->datePeriodReport($id, 0); //$current_res ? $current_res->balance : 0;

        $thirty = $this->datePeriodReport($id, 30);

        $sixty =  $this->datePeriodReport($id, 60);
        $ninety =  $this->datePeriodReport($id, 90);
        $one_20 =  $this->datePeriodReport($id, 120);
        $one_50 =  $this->datePeriodReport($id, 150);


        $one_80 = $this->datePeriodReport($id, 180);
        return response()->json([
            'data' => $data,
            'current' => $current,
            'thirty' => $thirty,
            'sixty' => $sixty,
            'ninety' => $ninety,
            'one_20' => $one_20,
            'one_50' => $one_50,
            'one_80' => $one_80
        ]);
    }
}
