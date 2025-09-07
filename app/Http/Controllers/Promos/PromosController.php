<?php

namespace App\Http\Controllers\Promos;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Promos\PromosRequest;
use App\Promo\Promo;
use Illuminate\Http\Request;

class PromosController extends BaseController
{
    public function update(Request $request)
    {
        $id = $request->id;
        // Validate the request
        // $request->validate([
        //     'lower_limit' => 'numeric|required|gt:0|lt:upper_limit',
        //     'upper_limit' => 'numeric|required|gt:lower_limit',
        //     'discount' => 'numeric|required|gt:0',
        //     'status' => 'required|in:active,inactive',
        // ]);

        try {
            // Find the promo by ID
            $promo = Promo::findOrFail($id);

            // Update the promo fields
            if ($request->has('lower_limit')) {
                $promo->lower_limit = $request->input('lower_limit');
            }

            if ($request->has('upper_limit')) {
                $promo->upper_limit = $request->input('upper_limit');
            }

            if ($request->has('discount')) {
                $promo->discount = $request->input('discount');
            }

            if ($request->has('status')) {
                $promo->status = $request->input('status');
            }

            // Save the updated promo
            $promo->save();

            return response()->json([
                'message' => 'Promo updated successfully',
                'promo' => $promo,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update promo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    function create(PromosRequest $request)
    {
        Promo::create($request->validated() + Parent::user_branch_id());
        return true;
    }
    function updatse(PromosRequest $request)
    {
        Promo::where('id', $request->id)->update($request->validated());
        return true;
    }

    function destroy(Request $request)
    {

        Promo::find($request->id)->delete();
        return true;
    }
    function fetch()
    {
        $res = Promo::with('user')->latest()->get();
        return response()->json($res);
    }
}
