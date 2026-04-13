<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['nullable', 'string', 'max:50', "unique:users,username,{$request->user()->id}"],
            'phone' => ['nullable', 'string', 'max:20', "unique:users,phone,{$request->user()->id}"],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        $request->user()->update($validated);

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
