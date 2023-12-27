<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {   
        $rules = [
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'user_type' => 'required|string|in:customer,admin|max:255'];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $data['validation_errors'] = $errors;
            $status = 422;
            $msg = "Validation error";
        }
        else{
            $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'user_type' => $request->user_type,
            ]);
            $user->toArray();
            
            $token = $user->createToken('MyApp')->accessToken;
            $user['token'] = $token;
            $data = $user;
            $status = 200;
            $msg = "user created succesfully";
        }        

        return $this->response($data,$status,$msg);
    }

    public function login(Request $request)
    {   
        $rules = ['email' => 'required|string|email',
                'password' => 'required|string',];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            $data['validation_errors'] = $errors;
            $status = 422;
            $msg = "Validation error";
        }
        else{
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $token = Auth::user()->createToken('MyApp')->accessToken;
                $data = $token;
                $status = 200;
                $msg = "user fetched succesfully";
            } else {
                $data = [];
                $status = 401;
                $msg = "User Unauthorized";
            }
        }
        return $this->response($data,$status,$msg);
    }

    public function user(Request $request)
    {
        $user_id  = $request->token_id;
        $user = User::where('id',$user_id)->first();
        if($user){
            $data = $user;
            $msg = "User Fetched Sucessfully";
            $status = 200;
        } 
        else{
            $data = [];
            $msg = "User could not be Fetched";
            $status = 401;
        }
        return $this->response($data,$status,$msg);
    }

    public function logout(Request $request)
    {
        $auth_id  = $request->id_auth;
        $suceess = DB::table('oauth_access_tokens')
                    ->where('id', $auth_id)
                    ->update(['revoked' => 1]);
        if($suceess){
            $data = [];
            $msg = "Successfully logged out";
            $status = 200;
        } 
        else{
            $data = [];
            $msg = "Failed to logged out";
            $status = 401;
        }

        return $this->response($data,$status,$msg);
    }
}

