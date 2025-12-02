<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenController extends Controller
{
    public function validate_token(): JsonResponse
    {
        try {
            // Coba dapatkan user dari token
            $user = JWTAuth::parseToken()->authenticate();

            if ($user) {
                return response()->json([
                    'valid' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        // tambahkan field lain yang diperlukan
                    ],
                    'message' => 'Token is valid'
                ], 200);
            }

            return response()->json([
                'valid' => false,
                'message' => 'User not found'
            ], 404);

        } catch (JWTException $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Token is invalid',
                'error' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
