<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $service;

    public function __construct(AuthService $authService)
    {
        $this->service = $authService;
    }

    public function register(RegisterUserRequest $request)
    {
        return $this->service->register($request);
    }

    public function login(Request $request)
    {
        return $this->service->login($request);
    }

    public function logout()
    {
        return $this->service->logout();
    }

    public function sendOtp(Request $request)
    {
        return $this->service->sendOtp($request);
    }

    public function confirmOtp(Request $request)
    {
        return $this->service->confirmOtp($request);
    }
}
