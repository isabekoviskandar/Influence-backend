<?php

namespace App\Services;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function profile()
    {
        $user = Auth::user();

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request)
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user = Auth::user();
        $user->update($data);

        return new UserResource($user);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);

        $user = Auth::user();

        if (! password_verify($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = bcrypt($data['new_password']);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }
}
