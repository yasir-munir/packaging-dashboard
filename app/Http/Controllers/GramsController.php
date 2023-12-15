<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Grams;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GramsController extends BaseController
{

    //-------------- Get All Gramages ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Category::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();

        $categories = Grams::where('deleted_at', '=', null)

        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('code', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $categories->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $categories = $categories->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'categories' => $categories,
            'totalRows' => $totalRows,
        ]);
    }

    //-------------- Store New Grams ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Category::class);

        request()->validate([
            'name' => 'required',
            'code' => 'required',
        ]);

        Grams::create([
            'code' => $request['code'],
            'name' => $request['name'],
        ]);
        return response()->json(['success' => true]);
    }

     //------------ function show -----------\\

    public function show($id){
        //

    }

    //-------------- Update Grams ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Category::class);

        request()->validate([
            'name' => 'required',
            'code' => 'required',
        ]);

        Grams::whereId($id)->update([
            'code' => $request['code'],
            'name' => $request['name'],
        ]);
        return response()->json(['success' => true]);

    }

    //-------------- Remove Grams ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Category::class);

        Grams::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Category::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $grams_id) {
            Grams::whereId($grams_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

}
