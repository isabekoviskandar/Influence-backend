<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Jobs\Bot\ProcessTelegramWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct() {}

    public function handle(string $secret, Request $request): Response
    {
        if ($secret !== config('services.telegram.webhook_secret')) {
            Log::channel('telegram')->warning('Invalid webhook secret provided');
            abort(403, 'Invalid secret');
        }

        $update = $request->all();

        Log::channel('telegram')->info('Webhook received', ['update' => $update]);

        // Dispatch the heavy lifting to the background queue
        // This ensures we return 200 OK to Telegram immediately to prevent retries
        ProcessTelegramWebhook::dispatch($update)->onQueue('default');

        return response('OK', 200);
    }
}
