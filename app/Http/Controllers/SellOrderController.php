<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AuthController;
use App\Models\Invoice;
use App\Models\InvoiceCost;
use App\Models\InvoiceInformation;
use App\Models\Merchant;
use App\Models\Payment;
use App\Models\SellOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class SellOrderController extends Controller
{
    public function sellOrderCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'type' => 'required',
            'weight' => 'required',
            'note' => 'required',
            'sell_date' => 'required',



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


        $merchant=Merchant::find($request->merchant_id);
        if(!$merchant)
        {
            return  response()->json(array(
                'message' => 'Merchant  Not Found'
            ), 404);
        }


        try {

            $sellOrder=new SellOrder();
            $sellOrder->merchant_id=$request->merchant_id;
            $sellOrder->type=$request->type;
            $sellOrder->weight=$request->weight;
            $sellOrder->note=$request->note;
            $sellOrder->sell_date=$request->sell_date;

            $sellOrder->save();

            return  response()->json(array(
                'message' => 'नवीन विक्री ऑर्डर यशस्वीपणे तयार झाली'
            ), 200);


        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }

    }

    public function sellOrderList(Request $request)
    {
        $query = SellOrder::query();

        if ($request->date!=null){


            $query->where('sell_date', $request->date);
        }


        $sellOrders = $query->paginate(10);




        // $sellOrders=SellOrder::where('created_at',$datetime)->paginate(10);

        if ($sellOrders->isEmpty()) {
            $sell_order_array = [];
        }

        $myObj = new \stdClass();


        foreach ($sellOrders as $key => $sellOrder) {

            $myObj->sell_order_id = $sellOrder->id;
            $myObj->merchant_id = $sellOrder->merchant_id;
            $myObj->merchant_name = $sellOrder->getMerchantDetail->name;
            $myObj->merchant_address = $sellOrder->getMerchantDetail->address;
            $myObj->type = $sellOrder->type;
            $myObj->merchant_phone_no = $sellOrder->getMerchantDetail->phone_no;
            $myObj->sell_date = $sellOrder->sell_date;
            $myObj->note = $sellOrder->note;
            $myObj->weight = $sellOrder->weight;


            $sell_order_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $sell_order_array,
            'total' => $sellOrders->total(),
            'currentPage' => $sellOrders->currentPage(),
            'perPage' => $sellOrders->perPage(),
            'nextPageUrl' => $sellOrders->nextPageUrl(),
            'previousPageUrl' => $sellOrders->previousPageUrl(),
            'lastPage' => $sellOrders->lastPage()

        ), 200);
    }

    public function sellOrderDelete(Request $request)
    {
            $validator = Validator::make($request->all(), [
            'sell_order_id' => 'required',
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


        $sellOrder=SellOrder::find($request->sell_order_id);
        if(!$sellOrder)
        {
            return  response()->json(array(
                'message' => 'sell Order  Not Found'
            ), 404);
        }


        $sellOrder->delete();

        return  response()->json(array(
            'message' => 'Invoice deleted Succefully'
        ), 200);


    }

    public function sellOrderUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sell_order_id' => 'required',
            'merchant_id' => 'required',
            'type' => 'required',
            'weight' => 'required',
            'note' => 'required',
            'sell_date' => 'required',
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


        $merchant=Merchant::find($request->merchant_id);
        if(!$merchant)
        {
            return  response()->json(array(
                'message' => 'Merchant  Not Found'
            ), 404);
        }

        $sellOrder=SellOrder::find($request->sell_order_id);
        if(!$sellOrder)
        {
            return  response()->json(array(
                'message' => 'sell Order  Not Found'
            ), 404);
        }

        try {


            $sellOrder->merchant_id=$request->merchant_id;
            $sellOrder->type=$request->type;
            $sellOrder->weight=$request->weight;
            $sellOrder->note=$request->note;
            $sellOrder->sell_date=$request->sell_date;

            $sellOrder->save();

            return  response()->json(array(
                'message' => 'Selll Order Updated Sccefully'
            ), 200);


        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }



    }
    public function sellOrderDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sell_order_id' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $sellOrder=SellOrder::find($request->sell_order_id);
        if(!$sellOrder)
        {
            return  response()->json(array(
                'message' => 'sell Order  Not Found'
            ), 404);
        }

        $merchant=Merchant::find($sellOrder->merchant_id);
        if(!$merchant)
        {
            return  response()->json(array(
                'message' => 'Merchant  Not Found'
            ), 404);
        }

        $myObj = new \stdClass();
        $myObj->sell_order_id = $sellOrder->id;
        $myObj->merchant_id = $sellOrder->merchant_id;
        $myObj->merchant_name = $sellOrder->getMerchantDetail->name;
        $myObj->merchant_address = $sellOrder->getMerchantDetail->address;
        $myObj->type = $sellOrder->type;
        $myObj->merchant_phone_no = $sellOrder->getMerchantDetail->phone_no;
        $myObj->sell_date = $sellOrder->sell_date;
        $myObj->note = $sellOrder->note;
        $myObj->weight = $sellOrder->weight;


        return  response()->json(array(
            'data' => $myObj,
        ), 200);
    }

    public function sellInvoiceCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'name' => 'required',
            'date' => 'required',
            'merchant_id' => 'required',
            // 'address' => 'required',
            'receipt_no' => 'required',
            'vehicle_no' => 'required',
            'notes' => 'required',
            'invoiceInformation' => 'required|array',
            'total' => 'required',
            'overall_total' => 'required',
            'vehicle_charge' => 'required',
            'other_charge' => 'required',
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
            $invoice->date=$request->date;
            $invoice->farmer_merchant_id=$request->merchant_id;
            // $invoice->address=$request->address;
            $invoice->receipt_no=$request->receipt_no;
            $invoice->vehicle_no=$request->vehicle_no;
            $invoice->notes=$request->notes;
            $invoice->type=1;
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
            $invoice_cost->vehicle_charge=$request->vehicle_charge;
            $invoice_cost->other_charge=$request->other_charge;
            $invoice_cost->overall_total=$request->overall_total;


            $invoice_cost->save();


            DB::commit();

            return  response()->json(array(
                'message' => 'विक्री इनव्हॉइस यशस्वीपणे तयार झाला'
            ), 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }

    }

    public function sellInvoiceDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sell_invoice_id' => 'required',

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
            $invoice=Invoice::where('id',$request->sell_invoice_id)->where('type',1)->first();
            if(!$invoice)
            {
                return  response()->json(array(
                    'message' => 'Invoice  Not Found'
                ), 404);
            }

            $invoice->delete();

            $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->delete();

            $invoiceInformation=InvoiceInformation::where('invoice_id',$invoice->id)->delete();




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

    public function sellInvoiceUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sell_invoice_id' => 'required',
            // 'name' => 'required',
            'date' => 'required',
            // 'address' => 'required',
            'merchant_id' => 'required',
            'receipt_no' => 'required',
            'vehicle_no' => 'required',
            'notes' => 'required',
            'invoiceInformation' => 'required|array',
            'total' => 'required',
            'overall_total' => 'required',
            'vehicle_charge' => 'required',
            'other_charge' => 'required',

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


        $invoice=Invoice::where('id',$request->sell_invoice_id)->where('type',1)->first();
        if(!$invoice)
        {
            return  response()->json(array(
                'message' => 'Invoice  Not Found'
            ), 404);
        }

        try {

            DB::beginTransaction();


            $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->delete();

            $invoiceInformation=InvoiceInformation::where('invoice_id',$invoice->id)->delete();


            $merchant=Merchant::find($request->merchant_id);
            if(!$merchant)
            {
                return  response()->json(array(
                    'message' => 'Merchant  Not Found'
                ), 404);
            }

            $invoice->farmer_merchant_id=$request->merchant_id;
            // $invoice->name=$request->name;
            $invoice->date=$request->date;
            // $invoice->address=$request->address;
            $invoice->receipt_no=$request->receipt_no;
            $invoice->vehicle_no=$request->vehicle_no;
            $invoice->notes=$request->notes;
            $invoice->type=1;
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
            $invoice_cost->vehicle_charge=$request->vehicle_charge;
            $invoice_cost->other_charge=$request->other_charge;
            $invoice_cost->overall_total=$request->overall_total;


            $invoice_cost->save();


            DB::commit();

            return  response()->json(array(
                'message' => 'Sell Invoice updated Succefully'
            ), 200);


        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }





    }

    public function sellInvoiceDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sell_invoice_id' => 'required',

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

        $invoice=Invoice::where('id',$request->sell_invoice_id)->where('type',1)->first();
        if(!$invoice)
        {
            return  response()->json(array(
                'message' => 'Invoice  Not Found'
            ), 404);
        }

        $myObj=new stdClass();

        $myObj->sell_invoice_id=$invoice->id;
        $myObj->name=$invoice->getMerchantDetail->name;
        $myObj->merchant_id=$invoice->farmer_merchant_id;
        $myObj->date=$invoice->date;
        $myObj->address=$invoice->getMerchantDetail->address;
        $myObj->phone_no=$invoice->getMerchantDetail->phone_no;

        $myObj->receipt_no=$invoice->receipt_no;
        $myObj->vehicle_no=$invoice->vehicle_no;
        $myObj->notes=$invoice->notes;


        $invoiceCost=InvoiceCost::where('invoice_id',$invoice->id)->first();

        $myObj->invoice_cost_id=$invoiceCost->id;
        $myObj->total=$invoiceCost->total;
        $myObj->vehicle_charge=$invoiceCost->vehicle_charge;
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


    public function sellInvoiceList(Request $request)
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


        // $invoices=Invoice::where('type',1)->paginate(10);



        $query = Invoice::query();

        if ($request->date!=null){


            $query->where('date', $request->date);
        }

        $query->where('type',1);
        $invoices = $query->paginate(10);



        if ($invoices->isEmpty()) {
            $invoice_array = [];
        }

        $myObj = new \stdClass();


        foreach ($invoices as $key => $value) {

            $myObj->sell_invoice_id = $value->id;
            $myObj->name=$value->getMerchantDetail->name;
            $myObj->date = $value->date;
            $myObj->receipt_no = $value->receipt_no;
            $myObj->vehicle_no = $value->vehicle_no;
            $myObj->address = $value->getMerchantDetail->address;



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


    public function paymentCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'date' => 'required',
            'payment_mode' => 'required',
            'payment_type' => 'required',
            'notes' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $is_token = AuthController::check_admin_subadmin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }




        $payment=new Payment();
        $payment->name=$request->name;
        $payment->date=$request->date;
        $payment->notes=$request->notes;
        $payment->payment_mode=$request->payment_mode;
        $payment->payment_type=$request->payment_type;

        $result=$payment->save();
        if($result)
        {
            return  response()->json(array(
                'message' => 'paument Added Succefully'
            ), 200);

        }
        else
        {
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }

    }

    public function paymentList(Request $request)
    {
        $is_token = AuthController::check_admin_subadmin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }


        $query = Payment::query();

        if ($request->date!=null){


            $query->where('date', $request->date);
        }


        $payment = $query->paginate(10);



        // $payment=Payment::paginate(10);

        if ($payment->isEmpty()) {
            $payment_array = [];
        }

        $myObj = new \stdClass();


        foreach ($payment as $key => $value) {
            $myObj->payment_id=$value->id;

            $myObj->name=$value->name;
            $myObj->date=$value->date;
            $myObj->notes=$value->notes;
            $myObj->payment_mode=$value->payment_mode;
            $myObj->payment_mode_description='1-debit.2-credit';

            $myObj->payment_type=$value->payment_type;
            $myObj->payment_type_description='1-cash,2-banking';


            $payment_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $payment_array,
            'total' => $payment->total(),
            'currentPage' => $payment->currentPage(),
            'perPage' => $payment->perPage(),
            'nextPageUrl' => $payment->nextPageUrl(),
            'previousPageUrl' => $payment->previousPageUrl(),
            'lastPage' => $payment->lastPage()

        ), 200);
    }

    public function paymentDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $is_token = AuthController::check_admin_subadmin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $paymnet=Payment::find($request->payment_id);

        if (!$paymnet) {
            return  response()->json(array(
                'message' => 'paymnet Not Found'
            ), 404);
        }

        $myObj = new \stdClass();


        $myObj->name=$paymnet->name;
        $myObj->date=$paymnet->date;
        $myObj->notes=$paymnet->notes;
        $myObj->payment_mode=$paymnet->payment_mode;
        $myObj->payment_mode_description='1-debit.2-credit';

        $myObj->payment_type=$paymnet->payment_type;
        $myObj->payment_type_description='1-cash,2-banking';

        return  response()->json(array(
            'data' => $myObj,
        ), 200);


    }

    public function paymentDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $is_token = AuthController::check_admin_subadmin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $paymnet=Payment::find($request->payment_id);

        if (!$paymnet) {
            return  response()->json(array(
                'message' => 'payment Not Found'
            ), 404);
        }


        $paymnet->delete();

        return  response()->json(array(
            'message' => 'Payment deleted Succefully'
        ), 200);


    }
    public function paymentUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required',
            'name' => 'required',
            'date' => 'required',
            'payment_mode' => 'required',
            'payment_type' => 'required',
            'notes' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $is_token = AuthController::check_admin_subadmin_token($request->header('token'));

        if ($is_token != null) {
            if ($is_token->status() != 200) {
                $errors = json_decode($is_token->content(), true);
                return  response()->json(array(
                    'message' => 'Invalid Token',
                ), 408);
            }
        }

        $payment=Payment::find($request->payment_id);

        if (!$payment) {
            return  response()->json(array(
                'message' => 'Variety Not Found'
            ), 404);
        }

        $payment->name=$request->name;
        $payment->date=$request->date;
        $payment->notes=$request->notes;
        $payment->payment_mode=$request->payment_mode;
        $payment->payment_type=$request->payment_type;
        $result=$payment->save();
        if($result)
        {
            return  response()->json(array(
                'message' => 'payment Updated Succefully'
            ), 200);

        }
        else
        {
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }





    }
}
