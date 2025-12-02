<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class RefreshTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            // Coba verifikasi token
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            try {
                // Token expired → refresh token
                $newToken = JWTAuth::refresh();

                // Set token baru di header response
                return $this->setAuthorizationHeader($next($request), $newToken);
            } catch (JWTException $e) {
                auth()->logout();
                return response()->json(['message' => 'Token expired, please login again'], 401);
            }
        } catch (TokenInvalidException $e) {
            auth()->logout();
            return response()->json(['message' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            auth()->logout();
            return response()->json(['message' => 'Token not found'], 401);
        }

        // Token masih valid → lanjutkan request
        return $next($request);
    }

    private function setAuthorizationHeader($response, $token)
    {
        return $response->header('Authorization', 'Bearer ' . $token);
    }
}
