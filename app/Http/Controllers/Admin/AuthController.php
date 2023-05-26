<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function test(Request $request)
    {
        return 'success';
    }
    public function adminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password'=>'required',
          ]);

          if ($validator->fails())
          {
            return  response()->json(array(
              'message' => $validator->errors()->all()
            ), 400);
          }

        $admin=User::where('email',$request->email)->where('password',$request->password)->whereIn('role_id',[1,2])->first();

        if($admin)
        {

            $token=Str::random(40);
            $admin->token=$token;
            $admin->save();
            return  response()->json(array(
                'message' =>'Admin Login Succefully',
                'token' => $token,
                'name'=>$admin->name,
                'user_id'=>$admin->id,
                'role_id' => $admin->role_id,
                ), 200);
        }
        else
        {
            return  response()->json(array(
                'message' =>'Invalid credentials',
                ), 404);
        }


    }
    public static function check_admin_token($token)
    {

        if($token==null || $token=='')
        {
            return  response()->json(array(
                'message' => 'Invalid Token'
            ), 408);
        }
        $token = User::where('token', $token)->whereIn('role_id',[1,2])->first();
        if (!$token) {
            return  response()->json(array(
                'message' => 'Invalid Token'
            ), 408);
        } else {
            return  response()->json(array(
                'data' => $token
            ), 200);
        }
    }

    public static function check_admin_subadmin_token($token)
    {

        if($token==null || $token=='')
        {
            return  response()->json(array(
                'message' => 'Invalid Token'
            ), 408);
        }
        $token = User::where('token', $token)->whereIn('role_id',[1,2])->first();
        if (!$token) {
            return  response()->json(array(
                'message' => 'Invalid Token'
            ), 408);
        } else {
            return  response()->json(array(
                'data' => $token
            ), 200);
        }
    }



    public function createFarmer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'village' => 'required',
            'taluka' => 'required',
            'district' => 'required',
            'phone_no' => 'required',
            'type' => 'required',
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

        try {


        $farmer=new Farmer();
        $farmer->name=$request->name;
        $farmer->address=$request->address;
        $farmer->village=$request->village;
        $farmer->taluka=$request->taluka;
        $farmer->district=$request->district;
        $farmer->phone_no=$request->phone_no;
        $farmer->type=$request->type;
        $farmer->planted_area=$request->planted_area;
        $farmer->tree_count=$request->tree_count;
        $farmer->weight=$request->weight;
        $farmer->plantation_date=$request->plantation_date;
        $farmer->cutting_date=$request->cutting_date;
        $farmer->cutting_ratio=$request->cutting_ratio;
        $farmer->cutting_month=$request->cutting_month;
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

    public function subAdminCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'phone_no' => 'required',
            'address' => 'required',
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


        $user=User::where('email',$request->email)->first();
        if($user)
        {
            return  response()->json(array(
                'message' => 'This email id is already exist'
            ), 409);
        }

        $token=Str::random(40);
        $user=new User();
        $user->name=$request->name;
        $user->email=$request->email;
        $user->token=$token;

        $user->role_id=2;
        $user->password=$request->password;
        $user->address=$request->address;
        $user->phone_no=$request->phone_no;
        $result=$user->save();

        if($result)
        {
            return  response()->json(array(
                'message' => 'Subadmin Added Succefully'
            ), 200);

        }
        else
        {
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }



    }

    public function subAdminDetail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sub_admin_id' => 'required',

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


        $subAdmin=User::where('id',$request->sub_admin_id)->where('role_id',2)->first();
        if(!$subAdmin)
        {
            return  response()->json(array(
                'message' => 'Sub admin  Not Found'
            ), 404);
        }


        $myObj = new \stdClass();




            $myObj->sub_admin_id = $subAdmin->id;
            $myObj->name = $subAdmin->name;
            $myObj->address = $subAdmin->address;
            $myObj->phone_no = $subAdmin->phone_no;
            $myObj->email = $subAdmin->email;
            $myObj->is_block = $subAdmin->is_block;
            $myObj->is_block_description = "0-no,1-yes";



        return  response()->json(array(
            'data' => $myObj,
        ), 200);
    }

    public function subAdminDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sub_admin_id' => 'required',

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


        $subAdmin=User::where('id',$request->sub_admin_id)->where('role_id',2)->first();
        if(!$subAdmin)
        {
            return  response()->json(array(
                'message' => 'Sub admin  Not Found'
            ), 404);
        }

        $result=$subAdmin->delete();
        if($result)
        {
            return  response()->json(array(
                'message' => 'Subadmin Deleted Succefully'
            ), 200);

        }
        else
        {
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }
    }
    public function subAdminUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sub_admin_id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'phone_no' => 'required',
            'address' => 'required',
            'is_block'=>'required',

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



        $subAdmin=User::where('id',$request->sub_admin_id)->where('role_id',2)->first();
        if(!$subAdmin)
        {
            return  response()->json(array(
                'message' => 'Sub admin  Not Found'
            ), 404);
        }


        $checkEmail=User::where('email',$request->email)->where('id','!=',$subAdmin->id)->first();
        if($checkEmail)
        {
            return  response()->json(array(
                'message' => 'Email id already exist'
            ), 409);
        }



        $subAdmin->name=$request->name;
        $subAdmin->email=$request->email;
        $subAdmin->address=$request->address;
        $subAdmin->phone_no=$request->phone_no;
        $subAdmin->is_block=$request->is_block;

        $result=$subAdmin->save();

        if($result)
        {
            return  response()->json(array(
                'message' => 'Subadmin updated Succefully'
            ), 200);

        }
        else
        {
            return response(json_encode([
                'message' => 'Something went wrong'
            ]), 500);
        }


    }
    public function subAdminList(Request $request)
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

        $subAdmins=User::where('role_id',2)->paginate(10);

        if ($subAdmins->isEmpty()) {
            $subAdmin_array = [];
        }

        $myObj = new \stdClass();


        foreach ($subAdmins as $key => $subAdmin) {

            $myObj->sub_admin_id = $subAdmin->id;
            $myObj->name = $subAdmin->name;
            $myObj->address = $subAdmin->address;
            $myObj->phone_no = $subAdmin->phone_no;
            $myObj->email = $subAdmin->email;
            $myObj->is_block_description = "0-no,1-yes";




            $subAdmin_array[] = $myObj;
            $myObj = new \stdClass();

        }

        return  response()->json(array(
            'data' => $subAdmin_array,
            'total' => $subAdmins->total(),
            'currentPage' => $subAdmins->currentPage(),
            'perPage' => $subAdmins->perPage(),
            'nextPageUrl' => $subAdmins->nextPageUrl(),
            'previousPageUrl' => $subAdmins->previousPageUrl(),
            'lastPage' => $subAdmins->lastPage()
        ), 200);
    }
}


