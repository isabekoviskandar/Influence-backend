<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function profile()
    {
        return $this->userService->profile();
    }

    public function update(UpdateUserRequest $updateUserRequest)
    {
        return $this->userService->update($updateUserRequest);
    }
}
