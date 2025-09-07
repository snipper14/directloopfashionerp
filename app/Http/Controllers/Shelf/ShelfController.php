<?php

namespace App\Http\Controllers\Shelf;

use App\Stock\Stock;
use App\Location\Shelf;
use App\Stock\Branchshelf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;

class ShelfController extends BaseController
{
    //
    function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255|unique_custom:shelves,name,branch_id,' . Parent::branch_id(),
            'description' => 'nullable',

        ], [
            'name.unique_custom' => "Name already created"
        ]);

        Shelf::create($validatedData + Parent::user_branch_id());
        return true;
    }
    function fetch()
    {
        $query = Shelf::when(request('search', '') != '', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('description', 'LIKE', '%' . request('search') . '%');
            });
        });
        if (request('sort_shelf') != '') {
            $query->orderBy('name', request('sort_shelf'));
        }
        $data =  $query->get();
        if (request("page") > 0) {
            $data = $query->paginate(30);
        }
        return response()->json($data);
    }

    function update(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255|edit_unique_custom:shelves,name,branch_id,' . Parent::branch_id() . ',id,' . $request->id,
            'id' => 'required',
            'description' => 'nullable',

        ], [
            'id.required' => 'ID required.',
            'name.edit_unique_custom' => "Name already created"
        ]);

        Shelf::where(["id" => $request->id])->update($validatedData);
        return true;
    }
    function destroy(Request $request)
    {
        $id = $request->route('id');
        Shelf::where(["id" => $id])->delete();
        return true;
    }
    function allocate(Request $request)
    {
        $request->validate([

            'stock_id' => 'required',
            'shelf_id' => 'required',

        ], [
            'stock_id.required' => 'Stock required.',
            'shelf_id.required' => "Shelf Required"
        ]);
        Branchshelf::updateOrCreate(
            ['stock_id' => $request->stock_id],

            ['shelf_id' => $request->shelf_id, 'branch_id' => Parent::branch_id()]
        );
        $res =  Branchshelf::with(['shelf', 'stock'])->latest()->paginate(20);
        return $res;
    }
    function fetchAllocation()
    {
        $query = Branchshelf::with(['shelf', 'stock', 'branch'])
            ->when(request('search') != '', function ($q) {
            $q->whereHas('stock', function ($query) {
                $query->where('name', 'LIKE', '%' . request('search') . '%');
            })
            ->orWhereHas('shelf', function ($query) {
                $query->where('name', 'LIKE', '%' . request('search') . '%');
            });
            });

        if (request('sort_shelf')) {
            $query->join('shelves', 'branchshelves.shelf_id', '=', 'shelves.id')
              ->orderBy('shelves.name', request('sort_shelf'))
              ->select('branchshelves.*');
        } elseif (request('sort_stock')) {
            $query->join('stocks', 'branchshelves.stock_id', '=', 'stocks.id')
              ->orderBy('stocks.name', request('sort_stock'))
              ->select('branchshelves.*');
        } else {
            $query->latest();
        }

        $res = $query->get();
        if (request('page') > 0) {
            $res = $query->paginate(30);
        }
        return $res;
    }
    function destroyAllocation(Request $request)
    {
        $id = $request->route('id');
        Branchshelf::where(["id" => $id])->delete();
        return true;
    }


    public function importShelf(Request $request)
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

        $valid_extension = array("csv", 'xls');

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


                    //product found in db



                    $res = Stock::where(['name' => $importData[0]])->first();
                    if ($res) {
                        if($importData[1]){
                        $shelf = Shelf::updateOrCreate(['name' => $importData[1]], Parent::user_branch_id());
                        $updateData = array(

                            "stock_id" => $res->id,
                            "shelf_id" => $shelf->id,
                            'branch_id'=>Parent::branch_id()

                        );

                        Branchshelf::updateOrCreate(
                            ["stock_id" => $res->id],
                            $updateData
                        );
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
}
