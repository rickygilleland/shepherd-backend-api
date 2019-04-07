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
        $fb_user = Socialite::driver('facebook')->userFromToken($request->token);
        
        //try to find the user
        $user = \App\User::where('email' => $fb_user->email);
        
        if ($user) {
	        //return the token
	        $user_token = $user->api_token;
        } else {
	        //generate a token and a new user
	        $user = new \App\User();
	        $user->email = $fb_user->email
	        $user->name = $fb_user->name;
	        $user->avatar = $fb_user->avatar;
	        
	        $user_token = Str::random(60);
	        $user->api_token = hash('sha256', $user_token);
	        
	        $user->save();
	        
        }
        
        return ['token' => $user_token];
    }
}