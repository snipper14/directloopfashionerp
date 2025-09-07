<?php

namespace App\Http\Controllers\Employee;

use App\dept\Department;
use App\Employee\Employee;
use App\Employee\EmployeeType;
use App\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Employee\EmployeeRequest;

class EmployeeController extends BaseController
{

    public function create(EmployeeRequest $request)
    {

        $res = Employee::create($request->validated() + Parent::user_branch_id());
        return response()->json(["results" => $res]);
    }

    public function fetch()
    {
        DB::enableQueryLog();
        $main_query = Employee::when(request('department_id', '') != '', function ($query) {
            $query->where('department_id', request('department_id'));
        })->when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->whereRaw("concat(first_name, ' ', last_name) like '%" . request('search') . "%'")

                    ->orWhere('id_no', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('phone', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('email', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('payment_type', 'LIKE', '%' . request('search') . '%')
                    ->orWhereHas('department', function ($query) {
                        $query->where('department', 'LIKE', '%' . request('search') . '%');
                    })
                    ->orWhereHas('employee_type', function ($query) {
                        $query->where('name', 'LIKE', '%' . request('search') . '%');
                    });
            });
        })->with(['department' => function ($query) {
            $query->withoutGlobalScope(BranchScope::class);
        }, 'employee_type'])->when(request('sort_employee'), function ($q) {
            $q->orderBy('first_name', request('sort_employee'));
        })
            ->when(request('sort_salary'), function ($q) {
                $q->orderBy('salary', request('sort_salary'));
            })->orderBy('id', 'DESC');
        $user = $main_query->get();
        if (request('page') > 0) {
            $user = $main_query->paginate(16);
        }
        //    print_r(DB::getQueryLog());
        return response()->json(['results' => $user]);
    }



    public function getPieceWorkers()
    {
        $data = Employee::where('payment_type', 'piece_work')->with(['department' => function ($query) {
            $query->withoutGlobalScope(BranchScope::class);
        }, 'employee_type'])->orderBy('id', 'DESC')->get();
        return response()->json(['results' => $data]);
    }
    public function getCasuals()
    {
        $data = Employee::where('payment_type', 'casual')->with(['department' => function ($query) {
            $query->withoutGlobalScope(BranchScope::class);
        }, 'employee_type'])->orderBy('id', 'DESC')->get();
        return response()->json(['results' => $data]);
    }
    public function update(EmployeeRequest $request)
    {
        $data = Employee::where('id', $request->id)->update($request->validated());
        return response()->json(['results' => $data]);
    }

    public function getById(Request $request)
    {
        $id = $request->route('id');

        $data = Employee::find($id);
        return response()->json($data);
    }
    public function destroy(Request $request)
    {
        $id = $request->route('id');
        $id = Employee::where('id', $id);
        $id->delete();
        return response()->json(['results' => 'deleted']);
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
                $file->move($folderPath, $filename);

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



                    if ($this->checkIfEmployeeExists($importData[1], $importData[2])) {
                        //product not in db
                        $department = Department::withoutGlobalScope(BranchScope::class)->updateOrCreate(['department' => $importData[8]], [
                            'user_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id()

                        ]);
                        $employee_type = EmployeeType::updateOrCreate(['name' => $importData[9]], [
                            'user_id' => Parent::user_id(),
                            'branch_id' => Parent::branch_id()

                        ]);
                        $insertData = array(
                            "first_name" => $importData[1],
                            "last_name" => $importData[2],
                            'id_no' => $importData[3],
                            'dob' => $importData[4],
                            'phone' => $importData[5],
                            'email' => $importData[6],
                            'other' => $importData[7],
                            'department_id' => $department->id,
                            'employee_type_id' => $employee_type->id,
                            'salary' => $importData['10'],
                            'hrly_rate' => $importData['11'],
                            'kra_pin' => $importData['12'],
                            'job_no' => $importData['13'],
                            'start_contract' => $importData['14'],
                            'bank_branch' => $importData['15'],
                            'bank_name' => $importData['16'],
                            'account_name' => $importData['17'],
                            'account_no' => $importData['18'],
                            'payment_phone' => $importData['19'],
                            'payment_type' => $importData['20'],
                            'deduct_nssf' => $importData['21'],
                            'deduct_nhif' => $importData['22'],

                            'deduct_paye' => $importData['23'],
                            'deduct_housinglevy'=>$importData['24']
                        );
                        Employee::create($insertData + Parent::user_branch_id());
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
    function checkIfEmployeeExists($first_name, $last_name)
    {
        $res = Employee::where([
            'first_name' => $first_name,
            'last_name' => $last_name
        ])->first();

        return $res ? false : true;
    }
}
