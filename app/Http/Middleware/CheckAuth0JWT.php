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
            

            $auth0_api = new Authentication(
			    $laravelConfig['domain'],
			    $laravelConfig['client_id'],
			    $laravelConfig['client_secret'],
			);
			
			$auth0_user = $auth0_api->userinfo($accessToken);
			
			print_r($auth0_user); die();			
			$user = \App\User::where('provider_id', $auth0_user->sub);
			
			if (!$user) {
				$user = new \App\User();
		        $user->email = $auth0_user->email;
		        $user->name = $auth0_user->name;
		        $user->first_name = $auth0_user->given_name;
		        $user->last_name = $auth0_user->family_name;
		        $user->avatar = $auth0_user->picture;
		        $user->provider = "auth0";
		        $user->provider_id = $auth0_user->sub;
		        
		        $user->save();
			}

	        // lets log the user in so it is accessible
	        \Auth::login($user);
            
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}