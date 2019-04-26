<?php
// app/Http/Middleware/CheckJWT.php
// ...
namespace App\Http\Middleware;

use Auth0\SDK\JWTVerifier;
use Auth0\Login\Auth0User;
use Auth0\Login\Auth0JWTUser;
use Auth0\Login\Repository\Auth0UserRepository;

use Auth0\SDK\API\Authentication;

use Closure;

class CheckAuth0JWT {

    public function handle($request, Closure $next) {
        $accessToken = $request->bearerToken();
        if (empty($accessToken)) {
            return response()->json(['message' => 'Bearer token missing'], 401);
        }

        $laravelConfig = config('laravel-auth0');
        $jwtConfig = [
            'authorized_iss' => $laravelConfig['authorized_issuers'],
            'valid_audiences' => [$laravelConfig['api_identifier']],
            'supported_algs' => $laravelConfig['supported_algs'],
        ];
        
        try {
            $jwtVerifier = new JWTVerifier($jwtConfig);
            $decodedToken = $jwtVerifier->verifyAndDecode($accessToken);
            
            //check if we already know about this user from the token
            $user = \App\User::where('provider_id', $decodedToken->sub)->first();
            
            if ($user) {
	            print_r("known user"); die();
            }
            
            $is_facebook = false;
            
            if (!$user) {
	            //this a new user, so get their info from auth0
		            $auth0_api = new Authentication(
				    $laravelConfig['domain'],
				    $laravelConfig['client_id'],
				    $laravelConfig['client_secret'],
				);
				
				$auth0_user = (object)$auth0_api->userinfo($accessToken);
				
				if (strpos($auth0_user->sub, "facebook") !== false) {
					//this is a facebook login
					$is_facebook = true;
				}
            }

			
			
			if (!$user && $is_facebook) {
				//check if we have them by their facebook id
				$facebook_id = explode("|", $auth0_user->sub);
				$facebook_id = $facebook_id[1];
				
				$user = \App\User::where('provider_id', $facebook_id)->first();
				
				
				if ($user) {
					$user->provider = "auth0";
					$user->provider_id = $auth0_user->sub;
					$user->save();
				}
				
			}

			if (!$user) {
				$user = new \App\User();
		        
		        if (isset($auth0_user->name) && strpos($auth0_user->sub, "sms") === false) {
			        $user->name = $auth0_user->name;
		        }
		        
		        if (isset($auth0_user->given_name)) {
			        $user->first_name = $auth0_user->given_name;
		        }
		        
		        if (isset($auth0_user->family_name)) {
			        $user->last_name = $auth0_user->family_name;
		        }
		        
		        if (isset($auth0_user->picture)) {
			        $user->avatar = $auth0_user->picture;
		        }
		        
		        $user->provider = "auth0";
		        $user->provider_id = $auth0_user->sub;
		        
		        $user->save();
			}


	        // lets log the user in so it is accessible
	        \Auth::login($user);
            
        } catch (\Exception $e) {
	        error_log($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}