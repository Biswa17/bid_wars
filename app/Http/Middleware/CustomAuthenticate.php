<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate;
use DB;
use App\Models\User;


class CustomAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
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
                $request->merge(['token_id' => $user_id, 'id_auth' => $user_token]);
                return $next($request);
            }
            else{
                return response(['error' => ['code' => 'INVALID_TOKEN','description' => 'Invalid Token']], 401);
            }
        }
        catch (\Exception $exception) {
            return response(['error' => ['code' => 'INVALID_TOKEN','description' => 'Invalid Input Type']], 401);
        }
        
    }
}
