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
        $channels = Channel::where('is_active', true)->with('user')->get();

        if ($channels->isEmpty()) {
            $this->info('No active channels found to sync.');

            return;
        }

        $dispatched = 0;

        foreach ($channels as $channel) {
            $user = $channel->user;

            // If the channel has no user, or it was never synced, sync it immediately
            if (! $user || ! $channel->last_synced_at) {
                SyncChannelStats::dispatch($channel)->onQueue('sync');
                $dispatched++;

                continue;
            }

            // Sync based on plan interval
            $hoursSinceSync = now()->diffInHours($channel->last_synced_at);
            if ($hoursSinceSync >= $user->sync_interval_hours) {
                SyncChannelStats::dispatch($channel)->onQueue('sync');
                $dispatched++;
            }
        }

        $this->info("Dispatching sync jobs for {$dispatched} / {$channels->count()} channels...");
        $this->info('All sync jobs dispatched successfully.');
    }
}
