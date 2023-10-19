<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\Sale;
use App\utils\helpers;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use ArPHP\I18N\Arabic;

class ShipmentController extends BaseController
{

    //----------- Get ALL Shipments-------\\

    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Shipment::class);

        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        $data = array();

        $shipments = Shipment::with('sale','sale.client','sale.warehouse')

        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('Ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('status', 'LIKE', "%{$request->search}%")
                        ->orWhere('delivered_to', 'LIKE', "%{$request->search}%")
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('sale', function ($q) use ($request) {
                                $q->where('Ref', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('sale.warehouse', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('sale.client', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });

                });
            });
        $totalRows = $shipments->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $shipments_data = $shipments->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($shipments_data as $shipment) {

            $item['id'] = $shipment['id'];
            $item['date'] = $shipment['date'];
            $item['shipment_ref'] = $shipment['Ref'];
            $item['status'] = $shipment['status'];
            $item['driver_name'] = $shipment['driver_name'];
            $item['vehical_number'] = $shipment['vehical_number'];
            $item['delivered_to'] = $shipment['delivered_to'];
            $item['shipping_address'] = $shipment['shipping_address'];
            $item['shipping_details'] = $shipment['shipping_details'];
            $item['sale_ref'] = $shipment['sale']['Ref'];
            $item['sale_id'] = $shipment['sale']['id'];
            $item['warehouse_name'] = $shipment['sale']['warehouse']->name;
            $item['customer_name'] = $shipment['sale']['client']->name;

            $data[] = $item;
        }

        return response()->json([
            'shipments' => $data,
            'totalRows' => $totalRows,
        ]);
    }



    //----------- Store new Shipment -------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Shipment::class);

        request()->validate([
            'status' => 'required',
        ]);

        \DB::transaction(function () use ($request) {
            $shipment = Shipment::firstOrNew([ 'Ref' => $request['Ref']]);

            $shipment->user_id = Auth::user()->id;
            $shipment->sale_id = $request['sale_id'];
            $shipment->driver_name = $request['driver_name'];
            $shipment->vehical_number = $request['vehical_number'];
            $shipment->delivered_to = $request['delivered_to'];
            $shipment->shipping_address = $request['shipping_address'];
            $shipment->shipping_details = $request['shipping_details'];
            $shipment->status = $request['status'];
            $shipment->save();

            $sale = Sale::findOrFail($request['sale_id']);
            $sale->update([
                'shipping_status' => $request['status'],
            ]);

        }, 10);

        return response()->json(['success' => true]);

    }

    public function show($id){

        $get_shipment = Shipment::where('sale_id', $id)->first();

        if($get_shipment){

            $shipment_data['Ref'] = $get_shipment->Ref;
            $shipment_data['sale_id'] = $get_shipment->sale_id;
            $shipment_data['driver_name'] = $get_shipment->driver_name;
            $shipment_data['vehical_number'] = $get_shipment->vehical_number;
            $shipment_data['delivered_to'] = $get_shipment->delivered_to;
            $shipment_data['shipping_address'] = $get_shipment->shipping_address;
            $shipment_data['status'] = $get_shipment->status;
            $shipment_data['shipping_details'] = $get_shipment->shipping_details;

        }else{

            $shipment_data['Ref'] = $this->getNumberOrder();
            $shipment_data['sale_id'] = $id;
            $shipment_data['driver_name'] = '';
            $shipment_data['vehical_number'] = '';
            $shipment_data['delivered_to'] = '';
            $shipment_data['shipping_address'] = '';
            $shipment_data['status'] = '';
            $shipment_data['shipping_details'] = '';
        }
        return response()->json([
            'shipment' => $shipment_data,
        ]);

    }


    //----------- Update Shipment-------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Shipment::class);

        request()->validate([
            'status' => 'required',
        ]);

        \DB::transaction(function () use ($request , $id) {

            Shipment::whereId($id)->update($request->all());

            $sale = Sale::findOrFail($request['sale_id']);
            $sale->update([
                'shipping_status' => $request['status'],
            ]);

        }, 10);
        return response()->json(['success' => true]);
    }

    //----------- delete Shipment-------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Shipment::class);

        \DB::transaction(function () use ($request , $id) {

            $shipment = Shipment::find($id);
            $shipment->delete();

            $sale = Sale::findOrFail($shipment->sale_id);
            $sale->update([
                'shipping_status' => $request['status'],
            ]);

        }, 10);

        return response()->json(['success' => true]);

    }


   //------------- Reference Number Order SALE -----------\\

   public function getNumberOrder()
   {

       $last = DB::table('shipments')->latest('id')->first();

       if ($last) {
           $item = $last->Ref;
           $nwMsg = explode("_", $item);
           $inMsg = $nwMsg[1] + 1;
           $code = $nwMsg[0] . '_' . $inMsg;
       } else {
           $code = 'SM_1111';
       }
       return $code;
   }

   //------------- SALE PDF -----------\\

   public function GatePass_PDF(Request $request, $id)
   {
       $details = array();
       $helpers = new helpers();
       $sale_data = Shipment::with('sale.details.product.unitSale')
           ->where('deleted_at', '=', null)
           ->findOrFail($id);

       // We have fetched the list of sales, their details, need to make it ready for the print.
       $sale['driver_name'] = $sale_data->driver_name;
       $sale['vehicle_number'] = $sale_data->vehical_number;
       $sale['ship_ref'] = $sale_data->Ref;
       $sale['status'] = $sale_data->status;
       $sale['po_number'] = $sale_data['sale']->po_number;
       $sale['client_name'] = $sale_data['sale']->client->name;
       $sale['client_phone'] = $sale_data['sale']->client->phone;
       $sale['client_adr'] = $sale_data['sale']->client->adresse;
       $sale['client_email'] = $sale_data['sale']->client->email;
       $sale['client_tax'] = $sale_data['sale']->client->tax_number;
       $sale['TaxNet'] = number_format($sale_data['sale']->TaxNet, 2, '.', '');
       $sale['discount'] = number_format($sale_data['sale']->discount, 2, '.', '');
       $sale['shipping'] = number_format($sale_data['sale']->shipping, 2, '.', '');
       $sale['statut'] = $sale_data['sale']->statut;
       $sale['Ref'] = $sale_data['sale']->Ref;
       $sale['date'] = $sale_data['sale']->date;
       $sale['GrandTotal'] = number_format($sale_data['sale']->GrandTotal, 2, '.', '');
       $sale['paid_amount'] = number_format($sale_data['sale']->paid_amount, 2, '.', '');
       $sale['due'] = number_format($sale['GrandTotal'] - $sale['paid_amount'], 2, '.', '');
       $sale['payment_status'] = $sale_data['sale']->payment_statut;

       $detail_id = 0;
       foreach ($sale_data['sale']['details'] as $detail) {
           //check if detail has sale_unit_id Or Null
           if($detail->sale_unit_id !== null){
               $unit = Unit::where('id', $detail->sale_unit_id)->first();
           }else{
               $product_unit_sale_id = Product::with('unitSale')
               ->where('id', $detail->product_id)
               ->first();
               if($product_unit_sale_id['unitSale']){
                   $unit = Unit::where('id', $product_unit_sale_id['unitSale']->id)->first();
               }{
                   $unit = NULL;
               }
           }
           if ($detail->product_variant_id) {
               $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                   ->where('id', $detail->product_variant_id)->first();
               $data['code'] = $productsVariants->code;
               $data['name'] = '['.$productsVariants->name . ']' . $detail['product']['name'];
           } else {
               $data['code'] = $detail['product']['code'];
               $data['name'] = $detail['product']['name'];
           }
               $data['detail_id'] = $detail_id += 1;
               $data['quantity'] = number_format($detail->quantity, 2, '.', '');
               $data['total'] = number_format($detail->total, 2, '.', '');
               $data['unitSale'] = $unit?$unit->ShortName:'';
               $data['price'] = number_format($detail->price, 2, '.', '');
           if ($detail->discount_method == '2') {
               $data['DiscountNet'] = number_format($detail->discount, 2, '.', '');
           } else {
               $data['DiscountNet'] = number_format($detail->price * $detail->discount / 100, 2, '.', '');
           }
           $tax_price = $detail->TaxNet * (($detail->price - $data['DiscountNet']) / 100);
           $data['Unit_price'] = number_format($detail->price, 2, '.', '');
           $data['discount'] = number_format($detail->discount, 2, '.', '');
           if ($detail->tax_method == '1') {
               $data['Net_price'] = $detail->price - $data['DiscountNet'];
               $data['taxe'] = number_format($tax_price, 2, '.', '');
           } else {
               $data['Net_price'] = ($detail->price - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
               $data['taxe'] = number_format($detail->price - $data['Net_price'] - $data['DiscountNet'], 2, '.', '');
           }
           $data['is_imei'] = $detail['product']['is_imei'];
           $data['imei_number'] = $detail->imei_number;
           $details[] = $data;
       }
       $settings = Setting::where('deleted_at', '=', null)->first();
       $symbol = $helpers->Get_Currency_Code();
       $Html = view('pdf.gatepass_pdf', [
           'symbol' => $symbol,
           'setting' => $settings,
           'sale' => $sale,
           'details' => $details,
       ])->render();
       $arabic = new Arabic();
       $p = $arabic->arIdentify($Html);
       for ($i = count($p)-1; $i >= 0; $i-=2) {
           $utf8ar = $arabic->utf8Glyphs(substr($Html, $p[$i-1], $p[$i] - $p[$i-1]));
           $Html = substr_replace($Html, $utf8ar, $p[$i-1], $p[$i] - $p[$i-1]);
       }
       $pdf = PDF::loadHTML($Html);
       return $pdf->download('GatePass.pdf');
   }
}
