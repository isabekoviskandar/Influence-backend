<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $botUsername = config('services.telegram.bot_username');

        return Inertia::render('Dashboard/Settings', [
            'bot_username' => $botUsername,
            'telegram_link' => $user->telegram_chat_id
                ? null
                : $this->generateLink($user->id, $botUsername),
            'has_password' => ! empty($user->password),
        ]);
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $hasPassword = ! empty($user->password);

        $rules = $hasPassword
            ? ['current_password' => ['required', 'string'], 'new_password' => ['required', 'string', 'min:8', 'confirmed']]
            : ['new_password' => ['required', 'string', 'min:8', 'confirmed']];

        $validated = $request->validate($rules);

        if ($hasPassword) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => ['nullable', 'string', 'max:50', "unique:users,username,{$user->id}"],
            'email' => ['nullable', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'phone' => ['nullable', 'string', 'max:20', "unique:users,phone,{$user->id}"],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Handle Password
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function refreshTelegramLink(Request $request)
    {
        $botUsername = config('services.telegram.bot_username');
        $link = $this->generateLink($request->user()->id, $botUsername);

        return response()->json(['url' => $link, 'expires_in' => 900]);
    }

    private function generateLink(int $userId, string $botUsername): string
    {
        $token = Str::random(32);
        Cache::put("tg_link_token:{$token}", $userId, now()->addMinutes(15));

        return "https://t.me/{$botUsername}?start={$token}";
    }
}
