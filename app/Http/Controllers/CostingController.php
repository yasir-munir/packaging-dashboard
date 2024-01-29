<?php

namespace App\Http\Controllers;

use App\Models\Costing;
use Twilio\Rest\Client as Client_Twilio;
use GuzzleHttp\Client as Client_guzzle;
use App\Models\SMSMessage;
use Infobip\Api\SendSmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;
use Illuminate\Support\Str;
use App\Models\EmailMessage;
use App\Mail\CustomEmail;
use App\Mail\SaleMail;
use App\Models\Client;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CostingDetail;
use App\Models\Grams;
use App\Models\ReelSize;
use App\Models\Shade;
use App\Models\Unit;
use App\Models\PaymentSale;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\product_warehouse;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\sms_gateway;
use App\Models\Role;
use App\Models\SaleReturn;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Setting;
use App\Models\PosSetting;
use App\Models\User;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Stripe;
use App\Models\PaymentWithCreditCard;
use App\Models\Purchase;
use App\Models\PurchaseOrderDetails;
use DB;
use PDF;
use ArPHP\I18N\Arabic;
use \Nwidart\Modules\Facades\Module;

class CostingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(request $request)
    {
        //
        $this->authorizeForUser($request->user('api'), 'view', Product::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        // Filter fields With Params to retrieve
        $columns = array(0 => 'box_size', 1 => 'box_type', 2 => 'date', 3 => 'shade');
        $param = array(0 => 'like', 1 => '=', 2 => '=', 3 => 'like');
        $data = array();


        $costings = Costing::join('clients', 'costings.client_id', '=', 'clients.id')
                // ->join('costing_details', 'costing_details.costing_id', '=', 'costings.id')
                ->select('costings.*', 'clients.name')
                ->where('costings.deleted_at', '=', null)
                ->get();


        // TODO: Multiple Filter
        // $Filtred = $helpers->filter($costings, $columns, $param, $request)
        //     // Search With Multiple Param
        //     ->where(function ($query) use ($request) {
        //         return $query->when($request->filled('search'), function ($query) use ($request) {
        //             return $query->where('purchase_order.po_number', 'LIKE', "%{$request->search}%")
        //                 ->orWhere('purchase_order.code', 'LIKE', "%{$request->search}%")
        //                 ->orWhere(function ($query) use ($request) {
        //                     return $query->whereHas('purchase_order.po_number', function ($q) use ($request) {
        //                         $q->where('po_number', 'LIKE', "%{$request->search}%");
        //                     });
        //                 })
        //                 ->orWhere(function ($query) use ($request) {
        //                     return $query->whereHas('purchase_order.export_order', function ($q) use ($request) {
        //                         $q->where('export_order', 'LIKE', "%{$request->search}%");
        //                     });
        //                 });
        //         });
        //     });
        // $totalRows = $Filtred->count();
        // if ($perPage == "-1") {
        //     $perPage = $totalRows;
        // }
        // $costings = $Filtred->offset($offSet)
        //     ->limit($perPage)
        //     ->orderBy($order, $dir)
        //     ->get();

        foreach ($costings as $cost) {
            $item['id'] = $cost->id;
            $item['box_size'] = $cost->box_size;
            $item['cust_name'] = $cost->name;
            $item['box_type'] = $cost->box_type;
            $item['ply'] = $cost->ply;
            $item['order_date'] = $cost->date;
            $item['final_box_price'] = $cost->final_box_price;
            $item['is_active'] = $cost->is_active? "Active":'Inactive';

            $categoty_name = Category::where('id', $cost->box_type)->where('deleted_at', null)->get(['name']);
            $item['box_type_name'] = $categoty_name[0]['name'];

            $data[] = $item;
        }

        return response()->json([
            'costings' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $this->authorizeForUser($request->user('api'), 'create', Sale::class);

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);
        $stripe_key = config('app.STRIPE_KEY');
        $categories = Category::where('deleted_at', null)->get(['id', 'name']);
        $brands = Brand::where('deleted_at', null)->get(['id', 'name']);
        $units = Unit::where('deleted_at', null)->where('base_unit', null)->get();
        $reelsize = ReelSize::where('deleted_at', null)->get(['id', 'name']);
        $grams = Grams::where('deleted_at', null)->get(['id', 'name']);
        $shades = Shade::where('deleted_at', null)->get(['id', 'name']);
        $brands = Brand::where('deleted_at', null)->get(['id', 'name']);
        $papers = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                    ->where('products.deleted_at', null)
                    ->where('products.listing_type', 'is_reel')
                    ->where('product_variants.deleted_at', null)
                    ->where('products.is_active', 1)
                    ->get();

        return response()->json([
            'stripe_key' => $stripe_key,
            'clients' => $clients,
            'warehouses' => $warehouses,
            'categories' => $categories,
            'types' => $brands,
            'units' => $units,
            'reelsize' => $reelsize,
            'grams' => $grams,
            'shades' => $shades,
            'papers' => $papers,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->authorizeForUser($request->user('api'), 'create', Sale::class);

        request()->validate([
            'client_id' => 'required',
        ]);

        \DB::transaction(function () use ($request) {
            $costing = new Costing;

            $costing->date = $request->order_date;
            $costing->client_id = $request->client_id;
            $costing->box_size = $request->box_size;
            $costing->measurement = $request->measurement;
            $costing->quantity = $request->quantity;
            $costing->box_type = $request->category_id;
            $costing->shade = $request->shade;
            $costing->paper_type = $request->type;
            $costing->ply = $request->ply;
            $costing->length_cm = $request->box_length_cm;
            $costing->width_cm = $request->box_width_cm;
            $costing->height_cm = $request->box_height_cm;
            $costing->length_inch = $request->box_length_inch;
            $costing->width_inch = $request->box_width_inch;
            $costing->height_inch = $request->box_height_inch;
            $costing->sheet_length = $request->sheet_length;
            $costing->sheet_width = $request->sheet_width;
            $costing->sheet_count = $request->sheet_count;
            $costing->roll_one_side = $request->roll_one_side;
            $costing->roll_two_side = $request->roll_two_side;
            $costing->total_craft = $request->total_craft;
            $costing->total_folding_nali = $request->total_folding_nali;
            $costing->total_folding = $request->total_folding;
            $costing->total_craft_q = $request->total_craft_q;
            $costing->total_folding_nali_q = $request->total_folding_nali_q;
            $costing->total_folding_q = $request->total_folding_q;
            $costing->total_grams = $request->total_grams;
            $costing->total_bs = $request->total_bs;
            $costing->total_weight = $request->total_weight;
            $costing->carrogation_cost = $request->carrogation_cost;
            $costing->waste = $request->waste;
            $costing->total_cost = $request->total_cost;
            $costing->conversion_per_kg = $request->conversion_per_kg;
            $costing->printing = $request->printing;
            $costing->lamination = $request->lamination;
            $costing->profit = $request->profit;
            $costing->transport = $request->transport;
            $costing->final_box_price = $request->final_box_price;
            $costing->is_active = $request->active;
            $costing->created_at = Carbon::now();
            $costing->user_id = Auth::user()->id;
            $costing->save();

            $data = json_decode($request->variants, true);
            foreach ($data as $value) {
                $costingDetail[] = [
                    'costing_id' => $costing->id,
                    'date' => $request->order_date,
                    'ply_no' => $value['ply_no'],
                    'paper_type' => $value['layer'],
                    'paper_id' => $value['paper'],
                    'paper_bf' => $value['bf'],
                    'paper_rate' => $value['rate'],
                    'paper_grams' => $value['gram'],
                    'paper_flute_factor' => $value['flute_factor'],
                    'paper_weight' => $value['weight'],
                    'paper_approx' => $value['approx'],
                    'paper_cost' => $value['cost'],
                    'created_at' => Carbon::now()
                ];
            }
            CostingDetail::insert($costingDetail);
        }, 10);

        return response()->json(['success' => true, 'message' => 'Costing Created !!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Costing  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function show(Costing $purchaseOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Costing  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        //
        $this->authorizeForUser($request->user('api'), 'create', Sale::class);

        $costing = Costing::join('clients', 'costings.client_id', '=', 'clients.id')
            ->select('costings.*', 'clients.name')
            ->where('costings.deleted_at', '=', null)
            ->findOrFail($id);

            $item['id'] = $costing->id;
            $item['box_size'] = $costing->box_size;
            $item['client_id'] = $costing->client_id;
            $item['cust_name'] = $costing->name;
            $item['category_id'] = $costing->box_type;
            $category = Category::where('id', $costing->box_type)->select(['name'])
                        ->where('deleted_at', '=', null)
                        ->first();
            $item['box_type_name'] = $category->name;
            $item['ply'] = $costing->ply;
            $item['order_date'] = $costing->date;
            $item['final_box_price'] = $costing->final_box_price;
            $item['active'] = $costing->is_active? true:false;
            $item['measurement'] = $costing->measurement;
            $item['quantity'] = $costing->quantity;
            $item['shade'] = $costing->shade;
            $shade = Shade::where('id', $costing->shade)->select(['name'])
                        ->where('deleted_at', '=', null)
                        ->first();
            $item['shade_name'] = $shade->name;
            $item['type'] = $costing->paper_type;
            $brand = Brand::where('id', $costing->paper_type)->select(['name'])
                        ->where('deleted_at', '=', null)
                        ->first();
            $item['paper_type_name'] = $brand->name;
            $item['box_length_cm'] = $costing->length_cm;
            $item['box_width_cm'] = $costing->width_cm;
            $item['box_height_cm'] = $costing->height_cm;
            $item['box_length_inch'] = $costing->length_inch;
            $item['box_width_inch'] = $costing->width_inch;
            $item['box_height_inch'] = $costing->height_inch;
            $item['sheet_length'] = $costing->sheet_length;
            $item['sheet_width'] = $costing->sheet_width;
            $item['sheet_count'] = $costing->sheet_count;
            $item['roll_one_side'] = $costing->roll_one_side;
            $item['roll_two_side'] = $costing->roll_two_side;
            $item['total_craft'] = $costing->total_craft;
            $item['total_folding_nali'] = $costing->total_folding_nali;
            $item['total_folding'] = $costing->total_folding;
            $item['total_craft_q'] = $costing->total_craft_q;
            $item['total_folding_nali_q'] = $costing->total_folding_nali_q;
            $item['total_folding_q'] = $costing->total_folding_q;
            $item['total_grams'] = $costing->total_grams;
            $item['total_bs'] = $costing->total_bs;
            $item['total_weight'] = $costing->total_weight;
            $item['carrogation_cost'] = $costing->carrogation_cost;
            $item['waste'] = $costing->waste;
            $item['total_cost'] = $costing->total_cost;
            $item['conversion_per_kg'] = $costing->conversion_per_kg;
            $item['printing'] = $costing->printing;
            $item['lamination'] = $costing->lamination;
            $item['profit'] = $costing->profit;
            $item['transport'] = $costing->transport;
            $item['carrogation_cost_percent'] = 5;
            $item['waste_percent'] = 1;
            $item['raw_material_cost_percent'] = 0; // Total Cost
            $item['conversion_per_kg_percent'] = 10;
            $item['printing_percent'] = 2;
            $item['lamination_percent'] = 5;
            $item['transport_percent'] = 2;
            $item['profit_percent'] = 10;

            $costing_data = CostingDetail::where('costing_id', $costing->id)
                ->where('deleted_at', '=', null)
                ->get();

            foreach ($costing_data as $cost) {

                $costPly['id'] = $cost->id;
                $costPly['ply_no'] = $cost->ply_no;
                $costPly['layer'] = $cost->paper_type;
                $costPly['paper'] = $cost->paper_id; // fetch paper details
                $paper_product = ProductVariant::where('id', $cost->paper_id)->select(['name'])
                        ->where('deleted_at', '=', null)
                        ->first();
                $costPly['paper_name'] = $paper_product['name']; // fetch paper details
                $costPly['bf'] = $cost->paper_bf;
                $costPly['rate'] = $cost->paper_rate;
                $costPly['gram'] = $cost->paper_grams;
                $costPly['flute_factor'] = $cost->paper_flute_factor;
                $costPly['weight'] = $cost->paper_weight;
                $costPly['approx'] = $cost->paper_approx;
                $costPly['cost'] = $cost->paper_cost;

                $item['costing_details'][] = $costPly;

            }


        $data[] = $item;

        $stripe_key = config('app.STRIPE_KEY');
        $units = Unit::where('deleted_at', null)->where('base_unit', null)->get();
        $reelsize = ReelSize::where('deleted_at', null)->get(['id', 'name']);
        $grams = Grams::where('deleted_at', null)->get(['id', 'name']);
        $shades = Shade::where('deleted_at', null)->get(['id', 'name']);
        $brands = Brand::where('deleted_at', null)->get(['id', 'name']);
        $papers = Product::join('product_variants', 'products.id', 'product_variants.product_id')
                    ->where('products.deleted_at', null)
                    ->where('products.listing_type', 'is_reel')
                    ->where('product_variants.deleted_at', null)
                    ->where('products.is_active', 1)
                    ->get();


        $clients = Client::where('deleted_at', '=', null)->get(['id', 'name']);
        $categories = Category::where('deleted_at', null)->get(['id', 'name']);

        return response()->json([
            'costing' => $data[0],
            'stripe_key' => $stripe_key,
            'clients' => $clients,
            'categories' => $categories,
            'types' => $brands,
            'units' => $units,
            'reelsize' => $reelsize,
            'grams' => $grams,
            'shades' => $shades,
            'papers' => $papers,
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Costing  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        request()->validate([
            'client_id' => 'required',
        ]);

        \DB::transaction(function () use ($request, $id) {

            $costing = Costing::where('id', $id)
                    ->where('deleted_at', '=', null)
                    ->first();

            $costing->date = $request['order_date'];
            $costing->client_id = $request['client_id'];
            $costing->box_size = $request['box_size'];
            $costing->measurement = $request['measurement'];
            $costing->quantity = $request['quantity'];
            $costing->box_type = $request['category_id'];
            $costing->shade = $request['shade'];
            $costing->paper_type = $request['type'];
            $costing->ply = $request['ply'];
            $costing->length_cm = $request['box_length_cm'];
            $costing->width_cm = $request['box_width_cm'];
            $costing->height_cm = $request['box_height_cm'];
            $costing->length_inch = $request['box_length_inch'];
            $costing->width_inch = $request['box_width_inch'];
            $costing->height_inch = $request['box_height_inch'];
            $costing->sheet_length = $request['sheet_length'];
            $costing->sheet_width = $request['sheet_width'];
            $costing->sheet_count = $request['sheet_count'];
            $costing->roll_one_side = $request['roll_one_side'];
            $costing->roll_two_side = $request['roll_two_side'];
            $costing->total_craft = $request['total_craft'];
            $costing->total_folding_nali = $request['total_folding_nali'];
            $costing->total_folding = $request['total_folding'];
            $costing->total_craft_q = $request['total_craft_q'];
            $costing->total_folding_nali_q = $request['total_folding_nali_q'];
            $costing->total_folding_q = $request['total_folding_q'];
            $costing->total_grams = $request['total_grams'];
            $costing->total_bs = $request['total_bs'];
            $costing->total_weight = $request['total_weight'];
            $costing->carrogation_cost = $request['carrogation_cost'];
            $costing->waste = $request['waste'];
            $costing->total_cost = $request['total_cost'];
            $costing->conversion_per_kg = $request['conversion_per_kg'];
            $costing->printing = $request['printing'];
            $costing->lamination = $request['lamination'];
            $costing->profit = $request['profit'];
            $costing->transport = $request['transport'];
            $costing->final_box_price = $request['final_box_price'];
            $costing->is_active = $request['active']=='true'?1:0;
            $costing->updated_at = Carbon::now();
            $costing->user_id = Auth::user()->id;
            $costing->save();


            // Store Variants Product
            $costingDetails = CostingDetail::where('costing_id', $costing->id)
                    ->where('deleted_at', '=', null)
                    ->get();

            if ($costingDetails->isNotEmpty()) {
                $new_variants_id = [];
                $var = 'id';

                foreach ($request['variants'] as $new_id) {
                    if (array_key_exists($var, $new_id)) {
                        $new_variants_id[] = $new_id['id'];
                    } else {
                        $new_variants_id[] = 0;
                    }
                }

                foreach ($costingDetails as $key => $value) {
                    $old_variants_id[] = $value->id;

                    // Delete Variant
                    if (!in_array($old_variants_id[$key], $new_variants_id)) {
                        $costingPly = CostingDetail::findOrFail($value->id);
                        $costingPly->deleted_at = Carbon::now();
                        $costingPly->save();
                    }
                }
                foreach ($request['variants'] as $key => $variant) {
                    if (array_key_exists($var, $variant)) {

                        $ProductVariantDT = new CostingDetail;
                        //-- Field Required
                        $ProductVariantDT->costing_id = $costing->id;
                        $ProductVariantDT->date = $request->order_date;
                        $ProductVariantDT->ply_no = $variant['ply_no'];
                        $ProductVariantDT->paper_type = $variant['layer'];
                        $ProductVariantDT->paper_id = $variant['paper'];
                        $ProductVariantDT->paper_bf = $variant['bf'];
                        $ProductVariantDT->paper_rate = $variant['rate'];
                        $ProductVariantDT->paper_grams = $variant['gram'];
                        $ProductVariantDT->paper_flute_factor = $variant['flute_factor'];
                        $ProductVariantDT->paper_weight = $variant['weight'];
                        $ProductVariantDT->paper_approx = $variant['approx'];
                        $ProductVariantDT->paper_cost = $variant['cost'];
                        $ProductVariantDT->updated_at = Carbon::now();

                        $ProductVariantUP['costing_id'] = $costing->id;
                        $ProductVariantUP['date'] = $request->order_date;
                        $ProductVariantUP['ply_no'] = $variant['ply_no'];
                        $ProductVariantUP['paper_type'] = $variant['layer'];
                        $ProductVariantUP['paper_id'] = $variant['paper'];
                        $ProductVariantUP['paper_bf'] = $variant['bf'];
                        $ProductVariantUP['paper_rate'] = $variant['rate'];
                        $ProductVariantUP['paper_grams'] = $variant['gram'];
                        $ProductVariantUP['paper_flute_factor'] = $variant['flute_factor'];
                        $ProductVariantUP['paper_weight'] = $variant['weight'];
                        $ProductVariantUP['paper_approx'] = $variant['approx'];
                        $ProductVariantUP['paper_cost'] = $variant['cost'];
                        $ProductVariantUP['updated_at'] = Carbon::now();
                    } else {
                        $ProductVariantDT = new CostingDetail;

                        //-- Field Required
                        $ProductVariantDT->costing_id = $id;
                        $ProductVariantDT->date = $request->order_date;
                        $ProductVariantDT->ply_no = $variant['ply_no'];
                        $ProductVariantDT->paper_type = $variant['layer'];
                        $ProductVariantDT->paper_id = $variant['paper'];
                        $ProductVariantDT->paper_bf = $variant['bf'];
                        $ProductVariantDT->paper_rate = $variant['rate'];
                        $ProductVariantDT->paper_grams = $variant['gram'];
                        $ProductVariantDT->paper_flute_factor = $variant['flute_factor'];
                        $ProductVariantDT->paper_weight = $variant['weight'];
                        $ProductVariantDT->paper_approx = $variant['approx'];
                        $ProductVariantDT->paper_cost = $variant['cost'];

                        $ProductVariantUP['costing_id'] = $id;
                        $ProductVariantUP['date'] = $request->order_date;
                        $ProductVariantUP['ply_no'] = $variant['ply_no'];
                        $ProductVariantUP['paper_type'] = $variant['layer'];
                        $ProductVariantUP['paper_id'] = $variant['paper'];
                        $ProductVariantUP['paper_bf'] = $variant['bf'];
                        $ProductVariantUP['paper_rate'] = $variant['rate'];
                        $ProductVariantUP['paper_grams'] = $variant['gram'];
                        $ProductVariantUP['paper_flute_factor'] = $variant['flute_factor'];
                        $ProductVariantUP['paper_weight'] = $variant['weight'];
                        $ProductVariantUP['paper_approx'] = $variant['approx'];
                        $ProductVariantUP['paper_cost'] = $variant['cost'];

                    }

                    if (!in_array($new_variants_id[$key], $old_variants_id)) {
                        $ProductVariantDT->save();
                    } else {
                        CostingDetail::where('id', $variant['id'])->update($ProductVariantUP);
                    }
                }
            }

        }, 10);

        return response()->json(['success' => true, 'message' => 'Costing Updated !!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Costing  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Costing::class);

        \DB::transaction(function () use ($id) {

            $Product = Costing::findOrFail($id);
            $Product->deleted_at = Carbon::now();
            $Product->save();

            CostingDetail::where('costing_id', $id)->update([
                'deleted_at' => Carbon::now(),
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }


    //--------------  Show Costing Details ---------------\\

    public function get_costing_detail(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'view', Purchase::class);

        $costing = Costing::join('clients', 'costings.client_id', '=', 'clients.id')
            ->select('costings.*', 'clients.name')
            ->where('costings.deleted_at', '=', null)
            ->findOrFail($id);

            $item['id'] = $costing->id;
            $item['box_size'] = $costing->box_size;
            $item['cust_name'] = $costing->name;
            $item['box_type'] = $costing->box_type;
            $category = Category::where('id', $costing->box_type)->select(['name'])
                        ->where('deleted_at', '=', null)
                        ->first();
            $item['box_type_name'] = $category->name;
            $item['ply'] = $costing->ply;
            $item['order_date'] = $costing->date;
            $item['final_box_price'] = $costing->final_box_price;
            $item['is_active'] = $costing->is_active? "Active":'Inactive';
            $item['measurement'] = $costing->measurement;
            $item['quantity'] = $costing->quantity;
            $item['shade'] = $costing->shade;
            $shade = Shade::where('id', $costing->shade)->select(['name'])
                        ->where('deleted_at', '=', null)
                        ->first();
            $item['shade_name'] = $shade->name;
            $item['paper_type'] = $costing->paper_type;
            $brand = Brand::where('id', $costing->paper_type)->select(['name'])
                        ->where('deleted_at', '=', null)
                        ->first();
            $item['paper_type_name'] = $brand->name;
            $item['length_cm'] = $costing->length_cm;
            $item['width_cm'] = $costing->width_cm;
            $item['height_cm'] = $costing->height_cm;
            $item['length_inch'] = $costing->length_inch;
            $item['width_inch'] = $costing->width_inch;
            $item['height_inch'] = $costing->height_inch;
            $item['sheet_length'] = $costing->sheet_length;
            $item['sheet_width'] = $costing->sheet_width;
            $item['sheet_count'] = $costing->sheet_count;
            $item['roll_one_side'] = $costing->roll_one_side;
            $item['roll_two_side'] = $costing->roll_two_side;
            $item['total_craft'] = $costing->total_craft;
            $item['total_folding_nali'] = $costing->total_folding_nali;
            $item['total_folding'] = $costing->total_folding;
            $item['total_craft_q'] = $costing->total_craft_q;
            $item['total_folding_nali_q'] = $costing->total_folding_nali_q;
            $item['total_folding_q'] = $costing->total_folding_q;
            $item['total_grams'] = $costing->total_grams;
            $item['total_bs'] = $costing->total_bs;
            $item['total_weight'] = $costing->total_weight;
            $item['carrogation_cost'] = $costing->carrogation_cost;
            $item['waste'] = $costing->waste;
            $item['total_cost'] = $costing->total_cost;
            $item['conversion_per_kg'] = $costing->conversion_per_kg;
            $item['printing'] = $costing->printing;
            $item['lamination'] = $costing->lamination;
            $item['profit'] = $costing->profit;
            $item['transport'] = $costing->transport;

            $costing_data = CostingDetail::where('costing_id', $costing->id)
                ->where('deleted_at', '=', null)
                ->get();

            foreach ($costing_data as $cost) {

                $costPly['ply_no'] = $cost->ply_no;
                $costPly['paper_type'] = $cost->paper_type;
                $costPly['layer_paper_id'] = $cost->paper_id; // fetch paper details
                $paper_product = ProductVariant::where('id', $cost->paper_id)->select(['name'])
                        ->where('deleted_at', '=', null)
                        ->first();
                $costPly['paper_name'] = $paper_product['name']; // fetch paper details
                $costPly['paper_bf'] = $cost->paper_bf;
                $costPly['paper_rate'] = $cost->paper_rate;
                $costPly['paper_grams'] = $cost->paper_grams;
                $costPly['paper_flute_factor'] = $cost->paper_flute_factor;
                $costPly['paper_weight'] = $cost->paper_weight;
                $costPly['paper_approx'] = $cost->paper_approx;
                $costPly['paper_cost'] = $cost->paper_cost;

                $item['costing_details'][] = $costPly;

            }


        $data[] = $item;

        return response()->json($data[0]);

    }

    public function getNumberOrder()
    {

        $last = DB::table('purchase_orders')->latest('id')->first();

        if ($last) {
            $item = $last->order_number;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            $code = $nwMsg[0] . '_' . $inMsg;
        } else {
            $code = 'PO_1111';
        }
        return $code;

    }

    //-------------- Get Units SubBase ------------------\\

    public function Get_Box_Size_By_Cust_Id(request $request)
    {
        $costing = Costing::where(function ($query) use ($request) {
            return $query->when($request->filled('id'), function ($query) use ($request) {
                return $query->where('client_id', $request->id);
            });
        })
        ->join('categories', 'costings.box_type', '=', 'categories.id')
        ->select('costings.*', 'categories.name')
        ->get();

        return response()->json($costing);
    }


}
