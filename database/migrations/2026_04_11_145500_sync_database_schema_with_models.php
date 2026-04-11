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
        // 1. Sync Users Table
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('users', 'telegram_chat_id')) {
                $table->string('telegram_chat_id')->nullable()->unique()->after('phone');
            }
            if (! Schema::hasColumn('users', 'telegram_username')) {
                $table->string('telegram_username')->nullable()->unique()->after('telegram_chat_id');
            }
            if (! Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('telegram_username');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('bio');
            }
            if (! Schema::hasColumn('users', 'plan')) {
                $table->string('plan')->nullable()->after('avatar');
            }
            if (! Schema::hasColumn('users', 'otp')) {
                $table->integer('otp')->nullable()->after('plan');
            }
        });

        // 2. Sync Posts Table
        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'channel_id')) {
                $table->foreignId('channel_id')->after('id')->constrained()->onDelete('cascade');
            }
            if (! Schema::hasColumn('posts', 'telegram_post_id')) {
                $table->string('telegram_post_id')->after('channel_id');
            }
            if (! Schema::hasColumn('posts', 'views')) {
                $table->integer('views')->default(0)->after('telegram_post_id');
            }
            if (! Schema::hasColumn('posts', 'forwards')) {
                $table->integer('forwards')->default(0)->after('views');
            }
            if (! Schema::hasColumn('posts', 'reactions')) {
                $table->integer('reactions')->default(0)->after('forwards');
            }

            // Composite unique index to avoid duplicate posts captured from webhooks
            $table->unique(['channel_id', 'telegram_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'phone', 'telegram_chat_id', 'telegram_username',
                'bio', 'avatar', 'plan', 'otp',
            ]);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique(['channel_id', 'telegram_post_id']);
            $table->dropForeign(['channel_id']);
            $table->dropColumn([
                'channel_id', 'telegram_post_id', 'views', 'forwards', 'reactions',
            ]);
        });
    }
};
