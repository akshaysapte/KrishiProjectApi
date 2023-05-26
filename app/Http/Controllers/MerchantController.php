<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AuthController;
use App\Models\Merchant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{
    public function createMerchant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'business_name' => 'required',
            'address' => 'required',
            'phone_no' => 'required|digits:10',
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

            $merchant=new Merchant();
            $merchant->name=$request->name;
            $merchant->business_name=$request->business_name;
            $merchant->address=$request->address;
            $merchant->phone_no=$request->phone_no;
            $merchant->save();

            return  response()->json(array(
                'message' => 'व्यापारी यशस्वीपणे तयार झाला'
            ), 200);


        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }
    }

    public function merchantList(Request $request)
    {


        $query = Merchant::query();

        if ($request->start_date!=null && $request->end_date!=null){
            $start_datetime = Carbon::parse($request->start_date)->startOfDay();
            $end_datetime = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('created_at', [$start_datetime, $end_datetime]);
        }
        elseif ($request->start_date!=null) {
            $start_datetime = Carbon::parse($request->start_date)->startOfDay();

            $query->where('created_at', '>=', $start_datetime);
        }
        elseif ($request->end_date!=null) {
            $end_datetime = Carbon::parse($request->end_date)->endOfDay();

            $query->where('created_at', '<=', $end_datetime);
        }


        $merchants = $query->paginate(10);



        // $merchants=Merchant::paginate(10);


        if ($merchants->isEmpty()) {
            $merchants_array = [];
        }

        $myObj = new \stdClass();


        foreach ($merchants as $key => $merchant) {

            $myObj->merchant_id = $merchant->id;
            $myObj->name = $merchant->name;
            $myObj->address = $merchant->address;
            $myObj->business_name = $merchant->business_name;
            $myObj->phone_no = $merchant->phone_no;


            $merchants_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $merchants_array,
            'total' => $merchants->total(),
            'currentPage' => $merchants->currentPage(),
            'perPage' => $merchants->perPage(),
            'nextPageUrl' => $merchants->nextPageUrl(),
            'previousPageUrl' => $merchants->previousPageUrl(),
            'lastPage' => $merchants->lastPage()

        ), 200);
    }

    public function merchantDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $merchant=Merchant::find($request->merchant_id);
        if(!$merchant)
        {
            return  response()->json(array(
                'message' => 'Merchant  Not Found'
            ), 404);
        }

        $myObj = new \stdClass();
        $myObj->merchant_id = $merchant->id;
        $myObj->name = $merchant->name;
        $myObj->address = $merchant->address;
        $myObj->business_name = $merchant->business_name;
        $myObj->phone_no = $merchant->phone_no;
        return  response()->json(array(
            'data' => $myObj,
        ), 200);


    }

    public function merchantUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'name' => 'required',
            'business_name' => 'required',
            'address' => 'required',
            'phone_no' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $merchant=Merchant::find($request->merchant_id);
        if(!$merchant)
        {
            return  response()->json(array(
                'message' => 'Merchant  Not Found'
            ), 404);
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

            $merchant->name=$request->name;
            $merchant->business_name=$request->business_name;
            $merchant->address=$request->address;
            $merchant->phone_no=$request->phone_no;
            $merchant->save();

            return  response()->json(array(
                'message' => 'व्यापारी यशस्वीपणे अत्याधुनिक झाला'
            ), 200);


        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }


    }

    public function allMerchants(Request $request)
    {
        $merchants=Merchant::where('is_block',0)->get();


        if ($merchants->isEmpty()) {
            $merchants_array = [];
        }

        $myObj = new \stdClass();

        foreach ($merchants as $key => $value) {
            $myObj->merchant_id=$value->id;
            $myObj->name=$value->name;


            $merchants_array[] = $myObj;
            $myObj = new \stdClass();


        }


        return  response()->json(array(
            'data' => $merchants_array,
        ), 200);



    }

}
