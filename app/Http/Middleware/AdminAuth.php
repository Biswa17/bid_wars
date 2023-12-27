<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DB;
use App\Models\User;


class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try{
            $token = $request->bearerToken();
            $token_parts = explode('.', $token);        
            $token_header = $token_parts[1];
            $token_header_json = base64_decode($token_header);   
            $token_header_array = json_decode($token_header_json, true);   
                
            $user_token = $token_header_array['jti'] ?? '';        
            $user_id = DB::table('oauth_access_tokens')
                        ->where('id', $user_token)
                        ->where('revoked', 0)
                        ->pluck('user_id')
                        ->first();
            if($user_id){
                $user = User::find($user_id);
                if($user->user_type != 'admin'){
                    $data = ["INVALID_TOKEN"];
                    $status = 401;
                    $message = 'User not a admin';
                    $response = array('status'=>'failed','status_code'=>$status,'message'=>$message,'response'=>array('errors'=>$data));
                    return response()->json($response,200);
                }
                else{
                    $request->merge(['token_id' => $user_id, 'id_auth' => $user_token]);
                    return $next($request);
                }
                
            }
            else{
                $data = ["INVALID_TOKEN"];
                $status = 401;
                $message = 'Invalid Token';
                $response = array('status'=>'failed','status_code'=>$status,'message'=>$message,'response'=>array('errors'=>$data));
                return response()->json($response,200);
            }
        }
        catch (\Exception $exception) {
            p($exception->getMessage());
            $data = ['INVALID_TOKEN'];
            $status = 401;
            $message = 'Invalid Input Type';
            $response = array('status'=>'failed','status_code'=>$status,'message'=>$message,'response'=>array('errors'=>$data));
            return response()->json($response,200);
            
        }
    }
}
