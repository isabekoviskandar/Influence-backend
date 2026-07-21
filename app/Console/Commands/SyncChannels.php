<?php

namespace App\Console\Commands;

use App\Jobs\SyncChannelStats;
use App\Models\Channel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('channels:sync')]
#[Description('Dispatches background sync jobs for all active Telegram channels.')]
class SyncChannels extends Command
{
    /**
     * Execute the console command.
     *
     * This command is scheduled to run every hour via withoutOverlapping().
     * All active channels are dispatched unconditionally on each run —
     * the scheduler's own frequency is the rate-limit, not per-plan intervals.
     */
    public function handle(): void
    {
        $channels = Channel::where('is_active', true)->get();

        if ($channels->isEmpty()) {
            $this->info('No active channels found to sync.');

            return;
        }

        $dispatched = 0;

        foreach ($channels as $channel) {
            SyncChannelStats::dispatch($channel)->onQueue('sync');
            $dispatched++;
        }

        $this->info("Dispatching sync jobs for {$dispatched} / {$channels->count()} channels...");
        $this->info('All sync jobs dispatched successfully.');
    }
}
