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
        Schema::table('channels', function (Blueprint $table) {
            $table->unsignedInteger('avg_views_recent')->nullable()->after('avg_views');
        });

        Schema::table('channel_stats', function (Blueprint $table) {
            $table->unsignedInteger('avg_views_recent')->nullable()->after('avg_views');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index(['channel_id', 'posted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('avg_views_recent');
        });

        Schema::table('channel_stats', function (Blueprint $table) {
            $table->dropColumn('avg_views_recent');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['channel_id', 'posted_at']);
        });
    }
};
