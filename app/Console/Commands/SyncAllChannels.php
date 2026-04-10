<?php

namespace App\Console\Commands;

use App\Jobs\SyncChannelStats;
use App\Models\Channel;
use Illuminate\Console\Command;

class SyncAllChannels extends Command
{
    protected $signature = 'channels:sync';

    protected $description = 'Dispatch SyncChannelStats for all active channels';

    public function handle(): int
    {
        $channels = Channel::where('is_active', true)->get();

        $this->info("Dispatching sync for {$channels->count()} active channels...");

        foreach ($channels as $channel) {
            SyncChannelStats::dispatch($channel)
                ->onQueue('sync')
                ->delay(now()->addSeconds(rand(1, 30))); // spread the load
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}

// ─── Register in routes/console.php ─────────────────────────────────────────
//
// Schedule::command('channels:sync')->everySixHours();
//
// Or more granular — sync Pro/Agency users more frequently:
//
// Schedule::command('channels:sync')->everyTwoHours()->when(fn() => true);
