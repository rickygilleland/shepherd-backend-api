<?php
// app/Http/Middleware/CheckJWT.php
// ...
use Auth0\SDK\JWTVerifier;

class CheckJWT {

    public function handle($request, Closure $next) {
	    
	    $accessToken = $request->bearerToken();        
        if (empty($accessToken)) {
	        return ['error' => 'missing token'];
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
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}