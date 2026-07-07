<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginPostRequest;
use App\Http\Requests\RegisterPostRequest;
use App\Models\User;
use App\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterPostRequest $request): JsonResponse
    {
        // Check if the email already exists
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Email already exists'], 409);
        }
        // make sure the role is valid
        if (!in_array($request->role, [Role::Admin, Role::User], true)) {
            return response()->json(['message' => 'Invalid role'], 400);
        }

        // not check a user role, only admin can create admin user.
        // it will be improved later
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role === 'admin' ? Role::Admin : Role::User,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'An error occurred during registration', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(LoginPostRequest $request): JsonResponse
    {
        try {
            if (!auth()->attempt($request->only('email', 'password'))) {
                return response()->json(['message' => 'Invalid login credentials'], 401);
            }

            $token = auth()->user()->createToken('auth_token')->plainTextToken;

            return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'An error occurred during login', 'error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            if (!$request->user()) {
                return response()->json(['message' => 'User not authenticated'], 401);
            }

            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during logout', 'error' => $e->getMessage()], 500);
        }

    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
