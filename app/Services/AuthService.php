<?php

namespace App\Services;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Mail\SendOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
        ]);

        $this->issueOtp($user);

        return response()->json([
            'message' => 'Registration successful. OTP sent to your email.',
            'user' => new UserResource($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (! $user->is_confirmed) {
            $this->issueOtp($user);

            return response()->json([
                'message' => 'Please confirm your email before logging in. A new OTP has been sent.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        if ($user->is_confirmed) {
            return response()->json([
                'message' => 'Email is already confirmed.',
            ], 400);
        }

        $this->issueOtp($user);

        return response()->json([
            'message' => 'OTP sent to your email.',
        ]);
    }

    public function confirmOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        if ($user->is_confirmed) {
            return response()->json(['message' => 'Email is already confirmed.']);
        }

        if ((string) $user->otp !== $data['otp']) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (! $user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            $user->otp = null;
            $user->save();

            return response()->json(['message' => 'OTP has expired'], 400);
        }

        $user->otp = null;
        $user->otp_expires_at = null;
        $user->is_confirmed = true;
        $user->save();

        return response()->json(['message' => 'OTP confirmed successfully']);
    }

    private function issueOtp(User $user): void
    {
        $otp = random_int(100000, 999999);

        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::to($user->email)->queue(new SendOtp($user, $otp));
    }
}
