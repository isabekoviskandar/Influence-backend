<?php

namespace App\Http\Controllers\Auth\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MagicLoginController extends Controller
{
    public function handle(Request $request, string $token)
    {
        $userId = Cache::get("dashboard_login_token:{$token}");

        if (! $userId) {
            return redirect()->route('login')->with('error', 'This login link is invalid or has expired.');
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        Auth::login($user);

        Cache::forget("dashboard_login_token:{$token}");

        return redirect()->intended(route('dashboard'));
    }
}
