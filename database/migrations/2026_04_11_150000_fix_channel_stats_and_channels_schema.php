<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ─── Fix channel_stats table ────────────────────────────────────────
        // The original migration only had id + timestamps.
        // SyncChannelStats job writes these columns, so they must exist.
        Schema::table('channel_stats', function (Blueprint $table) {
            if (! Schema::hasColumn('channel_stats', 'channel_id')) {
                $table->foreignId('channel_id')->after('id')->constrained()->onDelete('cascade');
            }
            if (! Schema::hasColumn('channel_stats', 'member_count')) {
                $table->unsignedInteger('member_count')->nullable()->after('channel_id');
            }
            if (! Schema::hasColumn('channel_stats', 'avg_views')) {
                $table->unsignedInteger('avg_views')->nullable()->after('member_count');
            }
            if (! Schema::hasColumn('channel_stats', 'post_count')) {
                $table->unsignedInteger('post_count')->nullable()->after('avg_views');
            }
            if (! Schema::hasColumn('channel_stats', 'engagement_rate')) {
                $table->decimal('engagement_rate', 8, 2)->default(0)->after('post_count');
            }
            if (! Schema::hasColumn('channel_stats', 'growth_percent')) {
                $table->decimal('growth_percent', 8, 2)->default(0)->after('engagement_rate');
            }
            if (! Schema::hasColumn('channel_stats', 'potential_score')) {
                $table->unsignedTinyInteger('potential_score')->default(0)->after('growth_percent');
            }
            if (! Schema::hasColumn('channel_stats', 'recorded_at')) {
                $table->timestamp('recorded_at')->nullable()->after('potential_score');
            }

            // Index for growth calculation query (finding stats from 7 days ago)
            $table->index(['channel_id', 'recorded_at']);
        });

        // ─── Fix channels table ─────────────────────────────────────────────
        // SyncChannelStats + ChannelMemberService write these columns.
        Schema::table('channels', function (Blueprint $table) {
            // Make user_id nullable — channels can exist before being linked to a user
            if (Schema::hasColumn('channels', 'user_id')) {
                $table->foreignId('user_id')->nullable()->change();
            }

            if (! Schema::hasColumn('channels', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('member_count');
            }
            if (! Schema::hasColumn('channels', 'avg_views')) {
                $table->unsignedInteger('avg_views')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('channels', 'engagement_rate')) {
                $table->decimal('engagement_rate', 8, 2)->nullable()->after('avg_views');
            }
            if (! Schema::hasColumn('channels', 'potential_score')) {
                $table->unsignedTinyInteger('potential_score')->nullable()->after('engagement_rate');
            }
            if (! Schema::hasColumn('channels', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('potential_score');
            }
            if (! Schema::hasColumn('channels', 'added_at')) {
                $table->timestamp('added_at')->nullable()->after('last_synced_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channel_stats', function (Blueprint $table) {
            $table->dropIndex(['channel_id', 'recorded_at']);
            $table->dropForeign(['channel_id']);
            $table->dropColumn([
                'channel_id', 'member_count', 'avg_views', 'post_count',
                'engagement_rate', 'growth_percent', 'potential_score', 'recorded_at',
            ]);
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn([
                'is_active', 'avg_views', 'engagement_rate',
                'potential_score', 'last_synced_at', 'added_at',
            ]);
        });
    }
};
