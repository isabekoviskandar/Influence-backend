<?php

namespace App\Services;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;

class UserService
{
    protected ?User $user;

    public function __construct(Guard $auth)
    {
        $this->user = $auth->user();
    }

    public function profile()
    {
        return new UserResource($this->user);
    }

    public function update(UpdateUserRequest $request)
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $this->user->update($data);

        return new UserResource($this->user);
    }
}
