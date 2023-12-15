<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
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

class PurchaseOrderController extends Controller
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
        $columns = array(0 => 'po_number', 1 => 'export_order', 2 => 'delivery_date', 3 => 'code');
        $param = array(0 => 'like', 1 => '=', 2 => '=', 3 => 'like');
        $data = array();

        // $products = PurchaseOrder::with('clients')->where('deleted_at', '=', null)->get();

        $products = PurchaseOrder::join('clients', 'purchase_orders.customer_id', '=', 'clients.id')
                ->select('purchase_orders.*', 'clients.name')
                ->where('purchase_orders.deleted_at', '=', null)
                ->get();


        // //Multiple Filter
        // $Filtred = $helpers->filter($products, $columns, $param, $request)
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
        // $products = $Filtred->offset($offSet)
        //     ->limit($perPage)
        //     ->orderBy($order, $dir)
        //     ->get();

        foreach ($products as $product) {
            $item['id'] = $product->id;
            $item['code'] = $product->code;
            $item['cust_name'] = $product->name;
            $item['po_number'] = $product->po_number;
            $item['export_order'] = $product->export_order;
            $item['order_date'] = $product->order_date;
            $item['delivery_date'] = $product->delivery_date;
            $item['status'] = $product->status=='planning'? "Planning":'';
            // $item['category'] = $product['category']->name;
            // $item['brand'] = $product['brand'] ? $product['brand']->name : 'N/D';

            $product_variant_data = PurchaseOrderDetails::where('purchase_order_id', $product->id)
                ->where('deleted_at', '=', null)
                ->get();

                $item['carton_size'] = '';
                $item['material'] = '';
                $item['ply'] = '';
                $item['quantity'] = '';
                $item['unit_price'] = '';
            foreach ($product_variant_data as $product_variant) {

                $item['carton_size'] .= $product_variant->carton_size;
                $item['carton_size'] .= '<br>';
                $item['material'] .= $product_variant->material;
                $item['material'] .= '<br>';
                $item['ply'] .= $product_variant->ply;
                $item['ply'] .= '<br>';
                $item['quantity'] .= $product_variant->quantity;
                $item['quantity'] .= '<br>';
                $item['unit_price'] .= $product_variant->unit_price;
                $item['unit_price'] .= '<br>';
            }

            // // $product_warehouse_total_qty = product_warehouse::where('product_id', $product->id)
            // //     ->where('deleted_at', '=', null)
            // //     ->sum('qte');

            // // $item['quantity'] = $product_warehouse_total_qty . ' ' . $product['unit']->ShortName;
            $data[] = $item;
        }
        // print_r($item);
        // die();

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        // $categories = Category::where('deleted_at', null)->get(['id', 'name']);
        // $brands = Brand::where('deleted_at', null)->get(['id', 'name']);

        return response()->json([
            'warehouses' => $warehouses,
            // 'categories' => $categories,
            // 'brands' => $brands,
            'products' => $data,
            // 'totalRows' => $totalRows,
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

        return response()->json([
            'stripe_key' => $stripe_key,
            'clients' => $clients,
            'warehouses' => $warehouses,
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
        $this->authorizeForUser($request->user('api'), 'create', Purchase::class);

        request()->validate([
            'client_id' => 'required',
        ]);

        \DB::transaction(function () use ($request) {
            $purchaseOrder = new PurchaseOrder;

            // $purchaseOrder->date = $request->order_date;
            $purchaseOrder->order_number = $this->getNumberOrder();
            $purchaseOrder->customer_id = $request->client_id;
            $purchaseOrder->export_order = $request->export_order;
            $purchaseOrder->Type_barcode = $request->Type_barcode;
            $purchaseOrder->po_number = $request->po_number;
            $purchaseOrder->status = 'planning';
            $purchaseOrder->order_date = $request->order_date;
            $purchaseOrder->delivery_date = $request->delivery_date;
            $purchaseOrder->notes = $request->notes;
            $purchaseOrder->user_id = Auth::user()->id;
            $purchaseOrder->save();

            $data = json_decode($request->variants, true);
            foreach ($data as $value) {
                $orderDetails[] = [
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_code' => $value['text'],
                    'carton_size' => $value['text'],
                    'material' => $value['material']['value'],
                    'ply' => $value['ply']['value'],
                    'quantity' => $value['quantity'],
                    'unit_price' => $value['price'],
                    'paper_type' => $value['paperType']['value'],
                    'status' => 'Planning',
                ];
            }
            PurchaseOrderDetails::insert($orderDetails);
        }, 10);

        return response()->json(['success' => true, 'message' => 'Purchase Order Created !!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', PurchaseOrder::class);

        \DB::transaction(function () use ($id) {

            $Product = PurchaseOrder::findOrFail($id);
            $Product->deleted_at = Carbon::now();
            $Product->save();


            product_warehouse::where('product_id', $id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            PurchaseOrderDetails::where('product_id', $id)->update([
                'deleted_at' => Carbon::now(),
            ]);

        }, 10);

        return response()->json(['success' => true]);
    }


    //--------------  Show Product Details ---------------\\

    public function Get_PO_Details(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'view', Purchase::class);

        $Product = PurchaseOrder::join('clients', 'purchase_orders.customer_id', '=', 'clients.id')
            ->select('purchase_orders.*', 'clients.name')
            ->where('purchase_orders.deleted_at', '=', null)
            ->findOrFail($id);
        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }


            $item['id'] = $Product->id;
            $item['code'] = $Product->code;
            $item['cust_name'] = $Product->name;
            $item['po_number'] = $Product->po_number;
            $item['export_order'] = $Product->export_order;
            $item['order_date'] = $Product->order_date;
            $item['delivery_date'] = $Product->delivery_date;
            $item['status'] = $Product->status=='planning'? "Planning":'';
            $item['Type_barcode'] = $Product->Type_barcode;



            $product_variant_data = PurchaseOrderDetails::where('purchase_order_id', $Product->id)
                ->where('deleted_at', '=', null)
                ->get();

            foreach ($product_variant_data as $product_variant) {

                $ProductVariant['carton_size'] = $product_variant->carton_size;
                $ProductVariant['material'] = $product_variant->material;
                $ProductVariant['ply'] = $product_variant->ply;
                $ProductVariant['quantity'] = $product_variant->quantity;
                $ProductVariant['paper_type'] = $product_variant->paper_type;
                $ProductVariant['unit_price'] = $product_variant->unit_price;

                $item['products_variants_data'][] = $ProductVariant;

                // foreach ($warehouses as $warehouse) {
                //     $product_warehouse = DB::table('product_warehouse')
                //         ->where('product_id', $id)
                //         ->where('deleted_at', '=', null)
                //         ->where('warehouse_id', $warehouse->id)
                //         ->where('product_variant_id', $variant->id)
                //         ->select(DB::raw('SUM(product_warehouse.qte) AS sum'))
                //         ->first();

                //     $war_var['mag'] = $warehouse->name;
                //     $war_var['variant'] = $variant->name;
                //     $war_var['qte'] = $product_warehouse->sum;
                //     $item['CountQTY_variants'][] = $war_var;
                // }

            }

        foreach ($warehouses as $warehouse) {
            $product_warehouse_data = DB::table('product_warehouse')
                ->where('deleted_at', '=', null)
                ->where('product_id', $id)
                ->where('warehouse_id', $warehouse->id)
                ->select(DB::raw('SUM(product_warehouse.qte) AS sum'))
                ->first();

            $war['mag'] = $warehouse->name;
            $war['qte'] = $product_warehouse_data->sum;
            $item['CountQTY'][] = $war;
        }

        // if ($Product->image != '') {
        //     foreach (explode(',', $Product->image) as $img) {
        //         $item['images'][] = $img;
        //     }
        // }

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
}
