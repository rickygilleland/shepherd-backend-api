<?php
// app/Http/Middleware/CheckJWT.php
// ...
namespace App\Http\Middleware;

use Auth0\SDK\JWTVerifier;
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
            'valid_audiences' => $laravelConfig['api_identifier'],
            'supported_algs' => $laravelConfig['supported_algs'],
        ];
        
        try {
            $jwtVerifier = new JWTVerifier($jwtConfig);
            $decodedToken = $jwtVerifier->verifyAndDecode($accessToken);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}