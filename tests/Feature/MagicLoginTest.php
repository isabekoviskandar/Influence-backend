<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('allows a magic login link to replace an existing authenticated session', function () {
    $manualUser = User::factory()->create([
        'email' => 'manual@example.com',
        'telegram_chat_id' => null,
    ]);

    $telegramUser = User::factory()->create([
        'email' => 'telegram@example.com',
        'telegram_chat_id' => '123456789',
    ]);

    $token = Str::random(32);
    Cache::put("dashboard_login_token:{$token}", $telegramUser->id, now()->addMinutes(15));

    $this->actingAs($manualUser)
        ->get(route('magic-login', $token))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($telegramUser);
    expect(Cache::get("dashboard_login_token:{$token}"))->toBeNull();
});
