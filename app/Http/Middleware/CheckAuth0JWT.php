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
            
            print_r($laravelConfig); die();
            
            $auth0_api = new Authentication(
			    $laravelConfig['domain'],
			    $laravelConfig['client_id'],
			    $laravelConfig['client_secret'],
			);
			
			$user = $auth0_api->userinfo($accessToken);
            
            print_r($user); die();
            
            if (!$user) {
	            return \Response::make('Unauthorized', 401);
	        }
	        if (time() > $user->exp) {
	            return new JsonResponse([
	                'message'   =>  'Token expired'
	            ], 401);
	        }

	        // lets log the user in so it is accessible
	        \Auth::login($user);
            
            print_r($decodedToken); die();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}