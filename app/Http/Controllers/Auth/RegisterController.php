<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    /**
     * Register User
     */
    public function register(Request $request): JsonResponse
    {
        // Validasi input
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'password' => 'required|min:6',
        ]);

        // Create user
        $user = User::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
        ]);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil mendaftar',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ], 201);
    }
}
