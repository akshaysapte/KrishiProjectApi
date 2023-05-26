<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AuthController;
use App\Models\Farmer;
use App\Models\Invoice;
use App\Models\InvoiceCost;
use App\Models\InvoiceInformation;
use App\Models\Merchant;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class PurchaseOrderController extends Controller
{
    public function purchaseOrderCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'address' => 'required',
            'phone_no' => 'required|digits:10',
            'weight' => 'required',
            'note' => 'required',
            'purchase_date' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }
        $token_data = json_decode($is_token->content(), true);
        $login_data = $token_data['data'];

        try {

            $purchaseOrder=new PurchaseOrder();
            $purchaseOrder->name=$request->name;
            $purchaseOrder->type=$request->type;
            $purchaseOrder->address=$request->address;
            $purchaseOrder->weight=$request->weight;
            $purchaseOrder->note=$request->note;
            $purchaseOrder->phone_no=$request->phone_no;
            $purchaseOrder->purchase_date=$request->purchase_date;

            $purchaseOrder->save();

            return  response()->json(array(
                'message' => 'नवीन खरेदी ऑर्डर यशस्वीपणे तयार झाली'
            ), 200);


        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }

    }

    public function purchaseOrderList(Request $request)
    {

        $current_date_time=Carbon::now()->format('Y-m-d');

        $purchaseOrders=PurchaseOrder::whereDate('show_purchase_date','<=', $current_date_time)->paginate(10);


        if ($purchaseOrders->isEmpty()) {
            $purchase_order_array = [];
        }

        $myObj = new \stdClass();


        foreach ($purchaseOrders as $key => $purchaseOrder) {


            $myObj->purchase_order_id = $purchaseOrder->id;
            $myObj->name = $purchaseOrder->getFarmerDetail->name;
            $myObj->address = $purchaseOrder->getFarmerDetail->address;
            $myObj->varierty = $purchaseOrder->getFarmerOrderDetaill->getVarietyDetail->name;
            $myObj->phone_no = $purchaseOrder->getFarmerDetail->phone_no;
            $myObj->purchase_date = $purchaseOrder->purchase_date;
            $myObj->note = $purchaseOrder->note;
            $myObj->weight = $purchaseOrder->weight;





            $purchase_order_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $purchase_order_array,
            'total' => $purchaseOrders->total(),
            'currentPage' => $purchaseOrders->currentPage(),
            'perPage' => $purchaseOrders->perPage(),
            'nextPageUrl' => $purchaseOrders->nextPageUrl(),
            'previousPageUrl' => $purchaseOrders->previousPageUrl(),
            'lastPage' => $purchaseOrders->lastPage()

        ), 200);
    }

    public function purchaseOrderDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $purchaseOrder=PurchaseOrder::find($request->purchase_order_id);
        if(!$purchaseOrder)
        {
            return  response()->json(array(
                'message' => 'purchase Order  Not Found'
            ), 404);
        }

        $myObj = new \stdClass();
        $myObj->purchase_order_id = $purchaseOrder->id;
        $myObj->name = $purchaseOrder->getFarmerDetail->name;
        $myObj->address = $purchaseOrder->getFarmerDetail->address;
        $myObj->varierty = $purchaseOrder->getFarmerOrderDetaill->getVarietyDetail->name;
        $myObj->phone_no = $purchaseOrder->getFarmerDetail->phone_no;
        $myObj->purchase_date = $purchaseOrder->purchase_date;
        $myObj->note = $purchaseOrder->note;
        $myObj->weight = $purchaseOrder->weight;


        return  response()->json(array(
            'data' => $myObj,
        ), 200);
    }


    public function purchaseInvoiceCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'name' => 'required',
            'date' => 'required',
            // 'address' => 'required',
            'farmer_id' => 'required',

            'receipt_no' => 'required',
            'vehicle_no' => 'required',
            'notes' => 'required',
            'invoiceInformation' => 'required|array',
            'total' => 'required',
            'worker_charge' => 'required',
            'other_charge' => 'required',
            'overall_total' => 'required',


        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }


        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $farmer=Farmer::find($request->farmer_id);
        if(!$farmer)
        {
            return  response()->json(array(
                'message' => 'Farmer  Not Found'
            ), 404);
        }

        try {

            DB::beginTransaction();

            $invoice=new Invoice();
            // $invoice->name=$request->name;
            $invoice->date=$request->date;
            $invoice->farmer_merchant_id=$request->farmer_id;
            // $invoice->address=$request->address;
            $invoice->receipt_no=$request->receipt_no;
            $invoice->vehicle_no=$request->vehicle_no;
            $invoice->notes=$request->notes;
            $invoice->type=2;
            $invoice->save();




            foreach ($request->invoiceInformation as $key => $value) {

                $invoice_information=new InvoiceInformation();
                $invoice_information->invoice_id=$invoice->id;
                $invoice_information->fruit_id=$value['fruit_id'];
                $invoice_information->weight=$value['weight'];
                $invoice_information->cutting_weight=$value['cutting_weight'];
                $invoice_information->actual_weight=$value['actual_weight'];
                $invoice_information->rate=$value['rate'];
                $invoice_information->total=$value['total'];
                $invoice_information->save();
            }


            $invoice_cost=new InvoiceCost();
            $invoice_cost->invoice_id=$invoice->id;
            $invoice_cost->total=$request->total;
            $invoice_cost->worker_charge=$request->worker_charge;
            $invoice_cost->other_charge=$request->other_charge;
            $invoice_cost->overall_total=$request->overall_total;

            $invoice_cost->save();


            DB::commit();

            return  response()->json(array(
                'message' => 'खरेदी इनव्हॉइस यशस्वीपणे तयार झाला'
            ), 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }


    }

    public function vehicleInvoiceUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_invoice_id' => 'required',
            // 'name' => 'required',
            'date' => 'required',
            // 'address' => 'required',
            'merchant_id' => 'required',

            'receipt_no' => 'required',
            'vehicle_no' => 'required',
            'notes' => 'required',
            'vehicle_charge' => 'required',
            'overload_charge' => 'required',
            'other_charge' => 'required',
            'advance' => 'required',
            'overall_total' => 'required',
            'driver_name' => 'required',
            'driver_mobile_no' => 'required',




        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }


        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $invoice=Invoice::where('id',$request->vehicle_invoice_id)->where('type',3)->first();
        if(!$invoice)
        {
            return  response()->json(array(
                'message' => 'Invoice  Not Found'
            ), 404);
        }

        $merchant=Merchant::find($request->merchant_id);
        if(!$merchant)
        {
            return  response()->json(array(
                'message' => 'Merchant  Not Found'
            ), 404);
        }


        try {

            DB::beginTransaction();

            $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->delete();


            // $invoice->name=$request->name;
            $invoice->date=$request->date;
            // $invoice->address=$request->address;
            $invoice->farmer_merchant_id=$request->merchant_id;
            $invoice->receipt_no=$request->receipt_no;
            $invoice->vehicle_no=$request->vehicle_no;
            $invoice->notes=$request->notes;
            $invoice->driver_name=$request->driver_name;
            $invoice->driver_mobile_no=$request->driver_mobile_no;

            $invoice->type=3;
            $invoice->save();




            $invoice_cost=new InvoiceCost();
            $invoice_cost->invoice_id=$invoice->id;
            $invoice_cost->vehicle_charge=$request->vehicle_charge;
            $invoice_cost->overload_charge=$request->overload_charge;
            $invoice_cost->advance=$request->advance;
            $invoice_cost->overall_total=$request->overall_total;
            $invoice_cost->other_charge=$request->other_charge;
            $invoice_cost->save();


            DB::commit();

            return  response()->json(array(
                'message' => 'Vehicle Invoice Updated'
            ), 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }


    }


    public function vehicleInvoiceDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_invoice_id' => 'required',

        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }


        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $invoice=Invoice::where('id',$request->vehicle_invoice_id)->where('type',3)->first();
        if(!$invoice)
        {
            return  response()->json(array(
                'message' => 'Invoice  Not Found'
            ), 404);
        }

        $myObj=new stdClass();


        $myObj->vehicle_invoice_id=$invoice->id;
        // $myObj->name=$invoice->name;
        $myObj->name=$invoice->getMerchantDetail->name;
        $myObj->merchant_id=$invoice->farmer_merchant_id;
        $myObj->date=$invoice->date;
        $myObj->address=$invoice->getMerchantDetail->address;
        $myObj->phone_no=$invoice->getMerchantDetail->phone_no;

        $myObj->receipt_no=$invoice->receipt_no;
        $myObj->vehicle_no=$invoice->vehicle_no;
        $myObj->notes=$invoice->notes;

        $myObj->driver_name=$invoice->driver_name;
        $myObj->driver_mobile_no=$invoice->driver_mobile_no;


        $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->first();

        $myObj->invoice_cost_id=$invoiceCost->id;
        $myObj->vehicle_charge=$invoiceCost->vehicle_charge;
            $myObj->overload_charge=$invoiceCost->overload_charge;
            $myObj->advance=$invoiceCost->advance;
            $myObj->overall_total=$invoiceCost->overall_total;
            $myObj->other_charge=$invoiceCost->other_charge;



        return  response()->json(array(
            'data' => $myObj,
        ), 200);




    }

    public function purchaseInvoiceDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_invoice_id' => 'required',

        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }


        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }


        try {
            $invoice=Invoice::where('id',$request->purchase_invoice_id)->where('type',2)->first();
            if(!$invoice)
            {
                return  response()->json(array(
                    'message' => 'Invoice  Not Found'
                ), 404);
            }

            $invoice->delete();

            $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->delete();





            return  response()->json(array(
                'message' => 'Invoice deleted Succefully'
            ), 200);

        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }
    }

    public function vehicleInvoiceDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_invoice_id' => 'required',

        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }


        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }


        try {
            $invoice=Invoice::where('id',$request->vehicle_invoice_id)->where('type',3)->first();
            if(!$invoice)
            {
                return  response()->json(array(
                    'message' => 'Invoice  Not Found'
                ), 404);
            }

            $invoice->delete();

            $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->delete();





            return  response()->json(array(
                'message' => 'Invoice deleted Succefully'
            ), 200);

        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }
    }

    public function vehicleInvoiceCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'name' => 'required',
            'merchant_id' => 'required',
            'date' => 'required',
            // 'address' => 'required',
            'receipt_no' => 'required',
            'vehicle_no' => 'required',
            'notes' => 'required',
            'vehicle_charge' => 'required',
            'overload_charge' => 'required',
            'other_charge' => 'required',
            'advance' => 'required',
            'overall_total' => 'required',
            'driver_name'=>'required',
            'driver_mobile_no'=>'required',


        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }


        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $merchant=Merchant::find($request->merchant_id);
        if(!$merchant)
        {
            return  response()->json(array(
                'message' => 'Merchant  Not Found'
            ), 404);
        }


        try {

            DB::beginTransaction();

            $invoice=new Invoice();
            // $invoice->name=$request->name;
            $invoice->farmer_merchant_id=$request->merchant_id;
            $invoice->date=$request->date;
            // $invoice->address=$request->address;
            $invoice->receipt_no=$request->receipt_no;
            $invoice->vehicle_no=$request->vehicle_no;
            $invoice->notes=$request->notes;
            $invoice->driver_name=$request->driver_name;
            $invoice->driver_mobile_no=$request->driver_mobile_no;

            $invoice->type=3;
            $invoice->save();




            $invoice_cost=new InvoiceCost();
            $invoice_cost->invoice_id=$invoice->id;
            $invoice_cost->vehicle_charge=$request->vehicle_charge;
            $invoice_cost->overload_charge=$request->overload_charge;
            $invoice_cost->advance=$request->advance;
            $invoice_cost->overall_total=$request->overall_total;
            $invoice_cost->other_charge=$request->other_charge;
            $invoice_cost->save();


            DB::commit();

            return  response()->json(array(
                'message' => 'वाहतुक इनव्हॉइस यशस्वीपणे तयार झाला'
            ), 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }


    }


    public function purchaseInvoiceUpdate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'purchase_invoice_id' => 'required',
            // 'name' => 'required',
            'date' => 'required',
            // 'address' => 'required',
            'farmer_id' => 'required',
            'receipt_no' => 'required',
            'vehicle_no' => 'required',
            'notes' => 'required',
            'invoiceInformation' => 'required|array',
            'total' => 'required',
            'worker_charge' => 'required',
            'other_charge' => 'required',
            'overall_total' => 'required',


        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }


        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $invoice=Invoice::where('id',$request->purchase_invoice_id)->where('type',2)->first();
        if(!$invoice)
        {
            return  response()->json(array(
                'message' => 'Invoice  Not Found'
            ), 404);
        }

        $farmer=Farmer::find($request->farmer_id);
        if(!$farmer)
        {
            return  response()->json(array(
                'message' => 'Farmer  Not Found'
            ), 404);
        }


        try {

            DB::beginTransaction();

            $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->delete();

            $invoiceInformation=InvoiceInformation::where('invoice_id',$invoice->id)->delete();


            // $invoice->name=$request->name;
            $invoice->date=$request->date;
            $invoice->farmer_merchant_id=$request->farmer_id;

            // $invoice->address=$request->address;
            $invoice->receipt_no=$request->receipt_no;
            $invoice->vehicle_no=$request->vehicle_no;
            $invoice->notes=$request->notes;
            $invoice->type=2;
            $invoice->save();




            foreach ($request->invoiceInformation as $key => $value) {

                $invoice_information=new InvoiceInformation();
                $invoice_information->invoice_id=$invoice->id;
                $invoice_information->fruit_id=$value['fruit_id'];
                $invoice_information->weight=$value['weight'];
                $invoice_information->cutting_weight=$value['cutting_weight'];
                $invoice_information->actual_weight=$value['actual_weight'];
                $invoice_information->rate=$value['rate'];
                $invoice_information->total=$value['total'];
                $invoice_information->save();
            }


            $invoice_cost=new InvoiceCost();
            $invoice_cost->invoice_id=$invoice->id;
            $invoice_cost->total=$request->total;
            $invoice_cost->worker_charge=$request->worker_charge;
            $invoice_cost->other_charge=$request->other_charge;
            $invoice_cost->overall_total=$request->overall_total;

            $invoice_cost->save();


            DB::commit();

            return  response()->json(array(
                'message' => 'Purchase Invoice Updated'
            ), 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }

    }



    public function purchaseInvoiceDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_invoice_id' => 'required',

        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }


        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $invoice=Invoice::where('id',$request->purchase_invoice_id)->where('type',2)->first();
        if(!$invoice)
        {
            return  response()->json(array(
                'message' => 'Invoice  Not Found'
            ), 404);
        }

        $myObj=new stdClass();

        $myObj->purchase_invoice_id=$invoice->id;
        $myObj->name=$invoice->getFarmerDetail->name;
        $myObj->date=$invoice->date;
        $myObj->farmer_id=$invoice->farmer_merchant_id;
        $myObj->phone_no=$invoice->getFarmerDetail->phone_no;

        $myObj->address=$invoice->getFarmerDetail->address;
        $myObj->receipt_no=$invoice->receipt_no;
        $myObj->vehicle_no=$invoice->vehicle_no;
        $myObj->notes=$invoice->notes;


        $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->first();

        $myObj->invoice_cost_id=$invoiceCost->id;
        $myObj->total=$invoiceCost->total;
        $myObj->worker_charge=$invoiceCost->worker_charge;
        $myObj->other_charge=$invoiceCost->other_charge;
        $myObj->overall_total=$invoiceCost->overall_total;

        $invoiceInformation=InvoiceInformation::where('invoice_id',$invoice->id)->get();
        if($invoiceInformation->isEmpty())
        {
            $invoice_information_array = [];
        }

            $invoiceObj = new \stdClass();

        foreach ($invoiceInformation as $key => $value) {
            $invoiceObj->invoice_information_id=$value->id;
            $invoiceObj->fruit_id=$value->fruit_id;
            // $invoiceObj->fruit_id=$value->fruit_id;
            $invoiceObj->fruit_name=$value->getFruitDetail->name;

            $invoiceObj->weight=$value->weight;
            $invoiceObj->cutting_weight=$value->cutting_weight;
            $invoiceObj->actual_weight=$value->actual_weight;
            $invoiceObj->rate=$value->rate;
            $invoiceObj->total=$value->total;
            $invoice_information_array[] = $invoiceObj;
            $invoiceObj = new \stdClass();

        }

        $myObj->invoice_information_array=$invoice_information_array;


        return  response()->json(array(
            'data' => $myObj,
        ), 200);




    }

    public function purchaseInvoiceList(Request $request)
    {
        $is_token = AuthController::check_admin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }


        // $invoices=Invoice::where('type',2)->paginate(10);


        $query = Invoice::query();

        if ($request->date!=null){


            $query->where('date', $request->date);
        }

        $query->where('type',2);
        $invoices = $query->paginate(10);



        if ($invoices->isEmpty()) {
            $invoice_array = [];
        }

        $myObj = new \stdClass();


        foreach ($invoices as $key => $value) {

            $myObj->purchase_invoice_id = $value->id;
            $myObj->name = $value->getFarmerDetail->name;
            $myObj->date = $value->date;
            $myObj->receipt_no = $value->receipt_no;
            $myObj->vehicle_no = $value->vehicle_no;
            $myObj->address = $value->getFarmerDetail->address;


            $invoice_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $invoice_array,
            'total' => $invoices->total(),
            'currentPage' => $invoices->currentPage(),
            'perPage' => $invoices->perPage(),
            'nextPageUrl' => $invoices->nextPageUrl(),
            'previousPageUrl' => $invoices->previousPageUrl(),
            'lastPage' => $invoices->lastPage()

        ), 200);
    }

    public function vehicleInvoiceList(Request $request)
    {
        $is_token = AuthController::check_admin_token($request->header('token'));

            if ($is_token != null) {
                if ($is_token->status() != 200) {
                    $errors = json_decode($is_token->content(), true);
                    return  response()->json(array(
                        'message' => 'Invalid Token',
                    ), 408);
                }
            }


            // $invoices=Invoice::where('type',3)->paginate(10);


            $query = Invoice::query();

        if ($request->date!=null){


            $query->where('date', $request->date);
        }

        $query->where('type',3);
        $invoices = $query->paginate(10);



            if ($invoices->isEmpty()) {
                $invoice_array = [];
            }

            $myObj = new \stdClass();


            foreach ($invoices as $key => $value) {

                $myObj->vehicle_invoice_id = $value->id;
                $myObj->name = $value->getMerchantDetail->name;
                $myObj->date = $value->date;
                $myObj->receipt_no = $value->receipt_no;
                $myObj->vehicle_no = $value->vehicle_no;
                $myObj->address = $value->getMerchantDetail->address;

                $myObj->driver_name=$value->driver_name;
                $myObj->driver_mobile_no=$value->driver_mobile_no;

                $invoice_array[] = $myObj;
                $myObj = new \stdClass();

            }

            return  response()->json(array(
                'data' => $invoice_array,
                'total' => $invoices->total(),
                'currentPage' => $invoices->currentPage(),
                'perPage' => $invoices->perPage(),
                'nextPageUrl' => $invoices->nextPageUrl(),
                'previousPageUrl' => $invoices->previousPageUrl(),
                'lastPage' => $invoices->lastPage()

            ), 200);
    }
}
