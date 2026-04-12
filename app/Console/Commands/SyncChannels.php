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
     */
    public function handle(): void
    {
        $channels = Channel::where('is_active', true)->get();

        if ($channels->isEmpty()) {
            $this->info('No active channels found to sync.');

            return;
        }

        $this->info("Dispatching sync jobs for {$channels->count()} channels...");

        foreach ($channels as $channel) {
            SyncChannelStats::dispatch($channel)->onQueue('sync');
        }

        $this->info('All sync jobs dispatched successfully.');
    }
}
