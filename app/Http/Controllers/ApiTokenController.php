<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Socialite;

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
        
        //validate that their token matches the email that was posted
        $user = Socialite::driver('facebook')->userFromToken($request->token);
        
        if ($user->email != $request->email) {
	        return ['error' => 'email/token mismtach'];
        }
        
        $user_token = Str::random(60);

        $request->user()->forceFill([
            'api_token' => hash('sha256', $user_token),
        ])->save();

        return ['token' => $user_token];
    }
}