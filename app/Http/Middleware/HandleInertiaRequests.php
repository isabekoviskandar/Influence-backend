<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /** The root template that is loaded on the first page visit. */
    protected $rootView = 'app';

    /** Determine the current asset version. */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the shared props sent to every Inertia page.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                    'plan' => $request->user()->plan ?? 'free',
                    'telegram_username' => $request->user()->telegram_username,
                    'telegram_linked' => ! is_null($request->user()->telegram_chat_id),
                    'avatar' => $request->user()->avatar,
                    'active_channels_count' => $request->user()->channels()->where('is_active', true)->count(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
