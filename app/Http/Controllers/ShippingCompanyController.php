<?php

namespace App\Http\Controllers;

use App\Models\ShippingCompany;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShippingCompanyController extends BaseController
{
    // -------------- Get All Shipping Companies -----------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', ShippingCompany::class);
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        $companies = ShippingCompany::where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%");
                });
            });

        $totalRows = $companies->count();
        if ($perPage == '-1') {
            $perPage = $totalRows;
        }

        $companies = $companies->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'companies' => $companies,
            'totalRows' => $totalRows,
        ]);
    }

    // -------------- STORE NEW Shipping Company -----------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', ShippingCompany::class);

        request()->validate([
            'name' => 'required',
        ]);

        ShippingCompany::create([
            'name' => $request['name'],
            'is_active' => true,
        ]);

        return response()->json(['success' => true]);
    }

    // -------------- UPDATE Shipping Company -----------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', ShippingCompany::class);

        request()->validate([
            'name' => 'required',
        ]);

        ShippingCompany::whereId($id)->update([
            'name' => $request['name'],
        ]);

        return response()->json(['success' => true]);
    }

    // -------------- REMOVE Shipping Company -----------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', ShippingCompany::class);

        ShippingCompany::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);

        return response()->json(['success' => true]);
    }
}
