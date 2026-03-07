<?php

namespace App\Http\Controllers;

use App\Models\ShippingMethod;
use App\Models\ShippingCompany;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShippingMethodController extends BaseController
{
    // -------------- Get All Shipping Methods -----------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShippingMethod::class);
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        $methods = ShippingMethod::where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
            return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%");
                }
                );
            });

        $totalRows = $methods->count();
        if ($perPage == '-1') {
            $perPage = $totalRows;
        }

        $methods = $methods->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'methods' => $methods,
            'totalRows' => $totalRows,
        ]);
    }

    // -------------- STORE NEW Shipping Method -----------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', ShippingMethod::class);

        request()->validate([
            'name' => 'required',
        ]);

        ShippingMethod::create([
            'name' => $request['name'],
            'is_active' => true,
        ]);

        return response()->json(['success' => true]);
    }

    // -------------- UPDATE Shipping Method -----------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', ShippingMethod::class);

        request()->validate([
            'name' => 'required',
        ]);

        // Prevent modification of critical store pickup (optional, but good practice if user manually tries)
        // Usually we prevent deletion primarily.

        ShippingMethod::whereId($id)->update([
            'name' => $request['name'],
        ]);

        return response()->json(['success' => true]);
    }

    // -------------- REMOVE Shipping Method -----------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', ShippingMethod::class);

        $method = ShippingMethod::findOrFail($id);

        if ($method->name === 'Store Pickup') {
            return response()->json(['success' => false, 'message' => 'Store Pickup cannot be deleted.']);
        }

        ShippingMethod::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true]);
    }

    // -------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', ShippingMethod::class);

        $selectedIds = $request->selectedIds;
        foreach ($selectedIds as $method_id) {
            $method = ShippingMethod::findOrFail($method_id);
            if ($method->name !== 'Store Pickup') {
                ShippingMethod::whereId($method_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
