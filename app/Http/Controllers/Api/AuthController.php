<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {   
        $rules = ['name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',];

        $validator = Validator::make($request->all(), $rules);
        p($request->all());
        if ($validator->fails()) {
            $errors = $validator->errors();
            $data['validation_errors'] = $errors;
            $status = 422;
            $msg = "Validation error";
        }
        else{
            $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
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
        p($request->all());
        return response()->json(['user' => $user], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}

