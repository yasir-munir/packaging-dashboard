<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use App\utils\helpers;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class VariantController extends BaseController
{

    //-------------- Get All Variants ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Variant::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();

        $variantsp = Variant::where('deleted_at', '=', null)

        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('code', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $variantsp->count();
        $variantsp = $variantsp->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'variantsp' => $variantsp,
            'totalRows' => $totalRows,
        ]);
    }

    //-------------- Store New Variant ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Variant::class);

        request()->validate([
            'v_name' => 'required',
            'v_cost' => 'required',
            'v_price' => 'required',
        ]);

        Variant::create([
            'v_name' => $request['v_name'],
            'v_cost' => $request['v_cost'],
            'v_price' => $request['v_price'],
        ]);
        return response()->json(['success' => true]);
    }

     //------------ function show -----------\\

    public function show($id){
        //
    
    }

    //-------------- Update Variant ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Variant::class);

        request()->validate([
            'v_name' => 'required',
            'v_cost' => 'required',
            'v_price' => 'required',
        ]);

        Variant::whereId($id)->update([
            'v_name' => $request['v_name'],
            'v_cost' => $request['v_cost'],
            'v_price' => $request['v_price'],
        ]);
        return response()->json(['success' => true]);

    }

    //-------------- Remove Variant ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Variant::class);

        Variant::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Variant::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $category_id) {
            Variant::whereId($category_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

}
