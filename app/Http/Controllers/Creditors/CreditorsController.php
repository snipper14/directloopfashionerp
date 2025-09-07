<?php

namespace App\Http\Controllers\Creditors;

use Carbon\Carbon;
use App\Suppliers\Supplier;
use App\Ledger\CreditLedger;
use Illuminate\Http\Request;
use App\Transaction\Transaction;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Pagination\LengthAwarePaginator;

class CreditorsController extends BaseController
{
    public function create(Request $request)
    {

        $this->validate($request, [
            'balance' => 'required',
            'supplier_id' => 'required'
        ]);
        $isOpening = CreditLedger::where('supplier_id', $request->supplier_id)->first();
        $balance = $request->balance;
        if ($isOpening) {
            $data = CreditLedger::where('supplier_id', $request->supplier_id)->orderBy('id', 'DESC')->first();

            $balance += $data->balance;
        }
        CreditLedger::create([
            'balance' => $balance,
            'supplier_id' => $request->supplier_id,

            'entry_date' => $request->entry_date,
            'description' => $request->description,
            'credit' => $request->balance,

            'type' => 'B',
            'ref' => 'Balance',
        ] + Parent::user_branch_id());

        $supp = Supplier::where('id', $request->supplier_id)->first();
        $insert_ledger_data = array(

            'ref' => " Balance",

            'entry_date' => $request->entry_date,
            'description' => "Opening Balance",
            'debit' => "Opening Account",
            'credit' => $supp->company_name,
            'amount' => $request->balance,
        );
        Transaction::create($insert_ledger_data + Parent::user_branch_id());
        return true;
    }
    function queryStatementData()
    {
        $id = request('supplier_id');
        $from = date(request('from'));
        $to = date(request('to'));

        $main_query =  CreditLedger::with(['supplier', 'branch', 'user'])->when(request('search', '') != '', function ($query) {
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
            )
            ->where(['supplier_id' => $id]);

        $paginatedData = $main_query->get();
        if (request('page') > 0) {
            $paginatedData =  $main_query->paginate(100);
        }
        return $paginatedData;
    }
    function queryPaginatedSupplierStmt()
    {
        $paginatedData = $this->queryStatementData();
        $data = [];
        $current_total = 0;
        foreach ($paginatedData as  $value) {

            $current_total -= $value->debit;
            $current_total += $value->credit;


            $data[] = [
                'id' => $value->id,
                'ref' => $value->ref,
                'entry_date' => $value->entry_date,
                'type' => $value->type,
                'description' => $value->description,
                'debit' => $value->debit,
                'credit' => $value->credit,
                'branch' => $value->branch,
                'user' => $value->user,
                'supplier' => $value->supplier,
                "balance" => $current_total
            ];
        }
        return $data;
    }


    private function baseSupplierQueryDesc(Request $request)
    {
        $id   = $request->supplier_id;
        $from = $request->filled('from') ? date('Y-m-d', strtotime($request->from)) : null;
        $to   = $request->filled('to')   ? date('Y-m-d', strtotime($request->to))   : null;

        return CreditLedger::with(['supplier', 'branch', 'user'])
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
            ->where('supplier_id', $id)
            // newest first (we are going backwards)
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc');
    }

    public function fetchSupplierStatement(Request $request)
    {
        $perPage = max(1, (int) $request->input('per_page', 100));

        // same filters & DESC order for all pieces
        $base = $this->baseSupplierQueryDesc($request);

        // current page
        $pageQuery = (clone $base);
        $paginated = $pageQuery->paginate($perPage);

        if ($paginated->isEmpty()) {
            return response()->json($paginated);
        }

        // 1) Closing balance across ALL filtered rows (credit - debit for supplier A/P)
        $closing = (clone $base)
            ->selectRaw('COALESCE(SUM(credit - debit), 0) AS t')
            ->value('t');

        // 2) First row on this DESC page (the newest row on the page)
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
            ->selectRaw('COALESCE(SUM(credit - debit), 0) AS s')
            ->value('s');

        // 4) Page starting balance (after applying the first row & all older rows)
        $running = (float) $closing - (float) $sumNewer;

        // 5) Build rows, subtracting delta as we go backwards (DESC)
        $rows = [];
        foreach ($paginated as $cl) {
            $delta = (float)$cl->credit - (float)$cl->debit; // supplier: credit increases balance

            $rows[] = [
                'id'          => $cl->id,
                'ref'         => $cl->ref,
                'entry_date'  => $cl->entry_date,
                'type'        => $cl->type,
                'description' => $cl->description,
                'debit'       => $cl->debit,
                'credit'      => $cl->credit,
                'branch'      => $cl->branch,
                'user'        => $cl->user,
                'supplier'    => $cl->supplier,
                'balance'     => $running,   // ✅ cumulative balance that doesn't reset across pages
            ];

            // step to the next (older) row
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



        $id = request('supplier_id');

        if ($request->from && $request->to) {
            $from = date(request('from'));
            $to = date(request('to'));

            $result =  CreditLedger::where(['supplier_id' => $id] + Parent::branch_array())->whereBetween('entry_date', [$from, $to])->get();


            return response()->json($result);
        }
        $result =  CreditLedger::where(['supplier_id' => $id])->get();
        return response()->json($result);
    }

    public function fetchStatementExcel()
    {
        $data = $this->queryPaginatedSupplierStmt();
        return response()->json($data);
    }
    public function generateStatment(Request $request)
    {

        $data = $this->queryPaginatedSupplierStmt();

        $folderPath = public_path('pdf');

        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $path = public_path() . '/pdf/' . 'supplier_statement' . '.pdf';

        $pdf = PDF::loadView('pdf.supplier_statement', compact('data'));
        $pdf->save($path);
        return response()->download($path);
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
                    $cust_data = Supplier::where('company_name', $importData[1])->first();
                    if ($cust_data) {
                        if (!$this->checkIfRecordExist($cust_data->id)) {
                            //product not in db

                            $insertData = array(


                                'balance' => $importData[2],
                                'supplier_id' => $cust_data->id,

                                'entry_date' => date("Y-m-d"),
                                'description' => 'Opening Balance',
                                'credit' => $importData[2],
                                'type' => 'B',
                                'ref' => 'Balance',
                            );
                            CreditLedger::create($insertData + Parent::user_branch_id());
                        }
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
        $res = CreditLedger::where(['supplier_id' => $id])->first();
        return $res ? true : false;
    }

    public function getExcell()
    {
        $result =  CreditLedger::with(['supplier' => function ($q) {
            $q->orderBy('company_name');
        }])->whereRaw('id IN (select MAX(id) FROM credit_ledgers GROUP BY supplier_id)')
            ->get();
        return response()->json($result);
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        CreditLedger::find($id)->delete();
        return true;
    }


    function datePeriodReport($id, $days)
    {

        $res = CreditLedger::where(['supplier_id' => $id])->where('entry_date', '<=', Carbon::now()->subDays($days))->get();
        $current_total = 0;

        foreach ($res as  $value) {

            $current_total -= $value->debit;
            $current_total += $value->credit;
        }
        return $current_total;
    }
    function fetchPaginatedSuppliers($paginate = true)
    {
        $main_query = Supplier::when(request('search', '') != '', function ($query) {
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
            ->when(request('supplier_id', '') != '', function ($query) {
                $query->where('id', request('supplier_id'));
            })
            ->orderBy('id', 'DESC');

        return $paginate && request('page') > 0
            ? $main_query->paginate(30)
            : $main_query->get();
    }

    function agingQueryData()
    {
        $current_total = 0;
        $paginatedData = $this->fetchPaginatedSuppliers();
        foreach ($paginatedData as  $value) {




            $data[] = [
                'current' => $this->datePeriodReport($value->id, 0),
                'thirty' => $this->datePeriodReport($value->id, 30),
                'sixty' => $this->datePeriodReport($value->id, 60),
                'ninety' => $this->datePeriodReport($value->id, 90),
                'one_twenty' => $this->datePeriodReport($value->id, 120),
                'one_fifty' => $this->datePeriodReport($value->id, 150),
                'one_eighty' => $this->datePeriodReport($value->id, 180),
                'supplier' => $value


            ];
        }
        return $data;
    }
    public function getAgingReport()
    {
        $currentPage = (int) request('page', 1);
        $perPage = 30;

        // Fetch all suppliers
        $allSuppliers = $this->fetchPaginatedSuppliers(false); // Return full list

        // Build aging data for all suppliers
        $data = [];
        foreach ($allSuppliers as $supplier) {
            $data[] = [
                'current'      => $this->datePeriodReport($supplier->id, 0),
                'thirty'       => $this->datePeriodReport($supplier->id, 30),
                'sixty'        => $this->datePeriodReport($supplier->id, 60),
                'ninety'       => $this->datePeriodReport($supplier->id, 90),
                'one_twenty'   => $this->datePeriodReport($supplier->id, 120),
                'one_fifty'    => $this->datePeriodReport($supplier->id, 150),
                'one_eighty'   => $this->datePeriodReport($supplier->id, 180),
                'supplier'     => $supplier,
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

        // Manual pagination
        $total = count($data);
        $items = array_slice($data, ($currentPage - 1) * $perPage, $perPage);

        return response()->json(new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => url()->current()]
        ));
    }


    public function exportAgingReport()
    {
        $data = $this->agingQueryData();

        return response()->json($data);
    }
    function addBalances(Request $request)
    {
        $this->validate($request, [
            'dr_balance' => 'required|gte:0',
            'cr_balance' => 'required|gte:0',
            'supplier_id' => 'required',
            'description' => "required"
        ]);
        DB::transaction(function () use ($request) {




            CreditLedger::create([
                'debit' => $request->dr_balance,
                'credit' => $request->cr_balance,
                'supplier_id' => $request->supplier_id,

                'entry_date' => $request->entry_date,
                'description' => $request->description,

                'type' => 'B',
                'ref' => 'Balance',
            ] + Parent::user_branch_id());
        }, 5);
        return true;
    }
}
