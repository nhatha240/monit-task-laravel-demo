<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Role;
use App\Http\Requests\LoginPostRequest;
use App\Http\Requests\RegisterPostRequest;
use \Illuminate\Http\JsonResponse;
class AuthController extends Controller
{
    public function register(RegisterPostRequest $request):JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role === 'admin' ? Role::Admin : Role::User,
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function login(LoginPostRequest $request): JsonResponse
    {

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $token = auth()->user()->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }   
}
