<?php

namespace App\Services;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class AuthService
{
    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'], // Will be hashed by User model's cast
        ]);

        return new UserResource($user);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! auth()->attempt($data)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        } else {
            $user = auth()->user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
            ]);
        }
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function sendOtp() {}

    public function confirmOtp() {}
}
