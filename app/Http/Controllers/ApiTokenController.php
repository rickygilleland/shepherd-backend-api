<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Socialite;
use Illuminate\Support\Facades\Log;

class ApiTokenController extends Controller
{
    /**
     * Update the authenticated user's API token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function token(Request $request)
    {
        
        
        
        if (!isset($request->token)) {
	        return ['error' => 'missing token'];
        }
        
        //get their profile info from their facebook token
        $user = Socialite::driver('facebook')->userFromToken($request->token);
        
        Log::alert(json_encode($user));
        
        return ['user' => $user->email];
        
        $user_token = Str::random(60);

        $request->user()->forceFill([
            'api_token' => hash('sha256', $user_token),
        ])->save();

        return ['token' => $user_token];
    }
}