<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AuthController;
use App\Models\District;
use App\Models\Farmer;
use App\Models\FarmerOrder;
use App\Models\Fruit;
use App\Models\PurchaseOrder;
use App\Models\Taluka;
use App\Models\Variety;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FarmerController extends Controller
{

    public function farmerOrderList(Request $request)
    {
        $current_date_time=Carbon::now()->format('Y-m-d');

        // dd($current_date_time);
            $purchase=FarmerOrder::paginate(10);

        // dd($farmers);
            if ($purchase->isEmpty()) {
                $farmers_array = [];
            }

            $myObj = new \stdClass();


            foreach ($purchase as $key => $value) {

                $myObj->farmer_order_id = $value->id;
                $myObj->farmer_id = $value->farmer_id;
                $myObj->variety = $value->getVarietyDetail->name;
                $myObj->planted_area = $value->planted_area;
                $myObj->tree_count = $value->tree_count;
                $myObj->weight = $value->weight;
                $myObj->plantation_date = $value->plantation_date;

                $myObj->cutting_date = $value->cutting_date;
                $myObj->cutting_ratio = $value->cutting_ratio;
                $myObj->cutting_month = $value->cutting_month;


                $myObj->cutting_end_month = $value->cutting_end_month;



                $myObj->name = $value->getFarmerDetail->name;
                $myObj->village = $value->getFarmerDetail->village;
                $myObj->phone_no = $value->getFarmerDetail->phone_no;








                $farmers_array[] = $myObj;
                $myObj = new \stdClass();

            }

            return  response()->json(array(
                'data' => $farmers_array,
                'total' => $purchase->total(),
                'currentPage' => $purchase->currentPage(),
                'perPage' => $purchase->perPage(),
                'nextPageUrl' => $purchase->nextPageUrl(),
                'previousPageUrl' => $purchase->previousPageUrl(),
                'lastPage' => $purchase->lastPage()

            ), 200);

    }


    public function createFarmerOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_planted' => 'required',
            'farmer_id' => 'required',
            'variety_id' => 'required',
            'planted_area' => 'required',
            'tree_count' => 'required',
            'weight' => 'required',
            'plantation_date' => 'required',
            'cutting_date' => 'required',
            'cutting_ratio' => 'required',
            'cutting_month' => 'required',
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


        $threeDaysBefore = Carbon::parse($request->cutting_date)->subDays(3)->format('Y-m-d');

        $cutting_end_month = Carbon::parse($request->cutting_date)->addMonths($request->cutting_month)->format('Y-m-d');



        try {

            DB::beginTransaction();

            $farmer = new FarmerOrder();
            $farmer->farmer_id = $request->farmer_id;
            $farmer->variety_id = $request->variety_id;
            $farmer->is_planted = $request->is_planted;
            $farmer->planted_area = $request->planted_area;
            $farmer->tree_count = $request->tree_count;
            $farmer->weight = $request->weight;
            $farmer->plantation_date = $request->plantation_date;
            $farmer->cutting_date = $request->cutting_date;
            $farmer->cutting_ratio = $request->cutting_ratio;
            $farmer->cutting_month = $request->cutting_month;

            $farmer->next_cutting_date = $request->cutting_date;
            $farmer->next_visible_date = $threeDaysBefore;
            $farmer->cutting_end_month = $cutting_end_month;

            $farmer->save();


            $datesArray = [];

            $currentDate = $request->cutting_date;

            while ($currentDate <= $cutting_end_month) {
                $datesArray[] = $currentDate;
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +'.$request->cutting_ratio.' days'));
            }


            foreach ($datesArray as $key => $value) {

                $threeDaysBefore = Carbon::parse($value)->subDays(3)->format('Y-m-d');

                $purchase=new PurchaseOrder();
                $purchase->farmer_id=$farmer->farmer_id;
                $purchase->farmer_order_id=$farmer->id;
                $purchase->note='test';
                $purchase->purchase_date=$value;
                $purchase->show_purchase_date=$threeDaysBefore;

                $purchase->save();

            }




            DB::commit();
            return  response()->json(array(
                'message' => 'Farmer Order Added Succefully'
            ), 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }


    }

    public function createFarmer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'village' => 'required',
            'taluka_id' => 'required',
            'district_id' => 'required',
            'phone_no' => 'required',
            // 'type' => 'required',
            // 'planted_area' => 'required',
            // 'tree_count' => 'required',
            // 'weight' => 'required',
            // 'plantation_date' => 'required',
            // 'cutting_date' => 'required',
            // 'cutting_ratio' => 'required',
            // 'cutting_month' => 'required',
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


            $farmer = new Farmer();
            $farmer->name = $request->name;
            $farmer->address = $request->address;
            $farmer->village = $request->village;
            $farmer->taluka_id = $request->taluka_id;
            $farmer->district_id = $request->district_id;
            $farmer->phone_no = $request->phone_no;

            $farmer->save();

            return  response()->json(array(
                'message' => 'शेतकरी यशस्वीपणे तयार झाला'
            ), 200);
        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }
    }

    public function FarmerList(Request $request)
    {

        $query = Farmer::query();

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


        $farmers = $query->paginate(10);

        // $farmers=Farmer::paginate(10);



        if ($farmers->isEmpty()) {
            $farmers_array = [];
        }

        $myObj = new \stdClass();


        foreach ($farmers as $key => $farmer) {

            $myObj->farmer_id = $farmer->id;
            $myObj->name = $farmer->name;
            $myObj->address = $farmer->address;
            $myObj->village = $farmer->village;
            $myObj->taluka = $farmer->getTalukaDetail->name;
            $myObj->district = $farmer->getDistrictDetail->name;
            $myObj->phone_no = $farmer->phone_no;




            $farmers_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $farmers_array,
            'total' => $farmers->total(),
            'currentPage' => $farmers->currentPage(),
            'perPage' => $farmers->perPage(),
            'nextPageUrl' => $farmers->nextPageUrl(),
            'previousPageUrl' => $farmers->previousPageUrl(),
            'lastPage' => $farmers->lastPage()

        ), 200);
    }

    public function FarmerDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farmer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $farmer=Farmer::find($request->farmer_id);
        if(!$farmer)
        {
            return  response()->json(array(
                'message' => 'Farmer  Not Found'
            ), 404);
        }

        $myObj = new \stdClass();
        $myObj->farmer_id = $farmer->id;
        $myObj->name = $farmer->name;
        $myObj->address = $farmer->address;
        $myObj->village = $farmer->village;
        $myObj->taluka_id = $farmer->taluka_id;
        $myObj->district_id = $farmer->district_id;

        $myObj->taluka = $farmer->getTalukaDetail->name;
        $myObj->district = $farmer->getDistrictDetail->name;
        $myObj->phone_no = $farmer->phone_no;


        return  response()->json(array(
            'data' => $myObj,
        ), 200);


    }

    public function farmerUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farmer_id' => 'required',
            'name' => 'required',
            'address' => 'required',
            'village' => 'required',
            'taluka_id' => 'required',
            'district_id' => 'required',
            'phone_no' => 'required',
            // 'type' => 'required',
            // 'planted_area' => 'required',
            // 'tree_count' => 'required',
            // 'weight' => 'required',
            // 'plantation_date' => 'required',
            // 'cutting_date' => 'required',
            // 'cutting_ratio' => 'required',
            // 'cutting_month' => 'required',
        ]);

        if ($validator->fails()) {
            return  response()->json(array(
                'message' => $validator->errors()->all()
            ), 400);
        }

        $farmer=Farmer::find($request->farmer_id);
        if(!$farmer)
        {
            return  response()->json(array(
                'message' => 'Farmer  Not Found'
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

            $farmer->name = $request->name;
            $farmer->address = $request->address;
            $farmer->village = $request->village;
            $farmer->taluka_id = $request->taluka_id;
            $farmer->district_id = $request->district_id;
            $farmer->phone_no = $request->phone_no;
            // $farmer->type = $request->type;
            // $farmer->planted_area = $request->planted_area;
            // $farmer->tree_count = $request->tree_count;
            // $farmer->weight = $request->weight;
            // $farmer->plantation_date = $request->plantation_date;
            // $farmer->cutting_date = $request->cutting_date;
            // $farmer->cutting_ratio = $request->cutting_ratio;
            // $farmer->cutting_month = $request->cutting_month;
            $farmer->save();

            return  response()->json(array(
                'message' => 'शेतकरी यशस्वीपणे अत्याधुनिक झाला'
            ), 200);



        } catch (\Exception $e) {
            return $e->getMessage();
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }
    }

    public function allFarmers(Request $request)
    {
        $farmers=Farmer::where('is_block',0)->get();


        if ($farmers->isEmpty()) {
            $farmers_array = [];
        }

        $myObj = new \stdClass();

        foreach ($farmers as $key => $value) {
            $myObj->farmer_id=$value->id;
            $myObj->name=$value->name;


            $farmers_array[] = $myObj;
            $myObj = new \stdClass();


        }


        return  response()->json(array(
            'data' => $farmers_array,
        ), 200);



    }

    public function varietyCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
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

        $variety=Variety::where('name',$request->name)->first();
        if($variety)
        {
            return  response()->json(array(
                'message' => 'This Variety Already Exist'
            ), 409);
        }


        $variety=new Variety();
        $variety->name=$request->name;
        $result=$variety->save();
        if($result)
        {
            return  response()->json(array(
                'message' => 'Variety Added Succefully'
            ), 200);

        }
        else
        {
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }

    }

    public function talukaDropdown(Request $request)
    {
        $talukas=Taluka::all();
        if ($talukas->isEmpty()) {
            $taluka_array = [];
        }


        $myObj = new \stdClass();


        foreach ($talukas as $key => $value) {

            $myObj->taluka_id = $value->id;
            $myObj->name = $value->name;


            $taluka_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $taluka_array,
        ), 200);

    }

    public function districtDropdown(Request $request)
    {
        $districts=District::all();
        if ($districts->isEmpty()) {
            $district_array = [];
        }


        $myObj = new \stdClass();


        foreach ($districts as $key => $value) {

            $myObj->district_id = $value->id;
            $myObj->name = $value->name;


            $district_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $district_array,
        ), 200);

    }

    public function fruitDropdown(Request $request)
    {
        $fruits=Fruit::all();
        if ($fruits->isEmpty()) {
            $fruit_array = [];
        }


        $myObj = new \stdClass();


        foreach ($fruits as $key => $value) {

            $myObj->fruit_id = $value->id;
            $myObj->name = $value->name;


            $fruit_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $fruit_array,
        ), 200);

    }

    public function varietyDropdown(Request $request)
    {



        $variety=Variety::where('is_block',0)->get();

        if ($variety->isEmpty()) {
            $variety_array = [];
        }

        $myObj = new \stdClass();


        foreach ($variety as $key => $value) {

            $myObj->variety_id = $value->id;
            $myObj->name = $value->name;


            $variety_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $variety_array,
        ), 200);
    }


    public function varietyList(Request $request)
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


        $variety=Variety::paginate(10);

        if ($variety->isEmpty()) {
            $variety_array = [];
        }

        $myObj = new \stdClass();


        foreach ($variety as $key => $value) {

            $myObj->variety_id = $value->id;
            $myObj->name = $value->name;
            $myObj->is_block = $value->is_block;
            $myObj->is_block_description = '1-yes,0-no';

            $variety_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $variety_array,
            'total' => $variety->total(),
            'currentPage' => $variety->currentPage(),
            'perPage' => $variety->perPage(),
            'nextPageUrl' => $variety->nextPageUrl(),
            'previousPageUrl' => $variety->previousPageUrl(),
            'lastPage' => $variety->lastPage()

        ), 200);
    }

    public function varietyDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variety_id' => 'required',
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

        $variety=Variety::find($request->variety_id);

        if (!$variety) {
            return  response()->json(array(
                'message' => 'Variety Not Found'
            ), 404);
        }

        $myObj = new \stdClass();



            $myObj->variety_id = $variety->id;
            $myObj->name = $variety->name;
            $myObj->is_block = $variety->is_block;
            $myObj->is_block_description = '1-yes,0-no';





        return  response()->json(array(
            'data' => $myObj,
        ), 200);


    }

    public function varietyUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variety_id' => 'required',
            'name' => 'required',
            'is_block' => 'required',
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

        $variety=Variety::find($request->variety_id);

        if (!$variety) {
            return  response()->json(array(
                'message' => 'Variety Not Found'
            ), 404);
        }

        $variety->name=$request->name;
        $variety->is_block=$request->is_block;
        $result=$variety->save();
        if($result)
        {
            return  response()->json(array(
                'message' => 'Variety Updated Succefully'
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
