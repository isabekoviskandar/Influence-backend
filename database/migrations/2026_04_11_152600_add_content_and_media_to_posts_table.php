<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add content & media columns to posts table.
     * Allows storing full post text, captions, and media metadata.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Post text content
            if (! Schema::hasColumn('posts', 'text')) {
                $table->text('text')->nullable()->after('telegram_post_id');
            }

            // Caption for media posts (photos/videos can have captions)
            if (! Schema::hasColumn('posts', 'caption')) {
                $table->text('caption')->nullable()->after('text');
            }

            // Media type: photo, video, document, animation, audio, voice, sticker
            if (! Schema::hasColumn('posts', 'media_type')) {
                $table->string('media_type', 20)->nullable()->after('caption');
            }

            // Telegram file_id — used to re-download or reference the file
            if (! Schema::hasColumn('posts', 'media_file_id')) {
                $table->string('media_file_id', 255)->nullable()->after('media_type');
            }

            // Local storage path after downloading
            if (! Schema::hasColumn('posts', 'media_path')) {
                $table->string('media_path', 500)->nullable()->after('media_file_id');
            }

            // File size in bytes
            if (! Schema::hasColumn('posts', 'media_size')) {
                $table->unsignedInteger('media_size')->nullable()->after('media_path');
            }

            // MIME type (image/jpeg, video/mp4, etc.)
            if (! Schema::hasColumn('posts', 'media_mime_type')) {
                $table->string('media_mime_type', 100)->nullable()->after('media_size');
            }

            // Original post timestamp from Telegram
            if (! Schema::hasColumn('posts', 'posted_at')) {
                $table->timestamp('posted_at')->nullable()->after('reactions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'text', 'caption', 'media_type', 'media_file_id',
                'media_path', 'media_size', 'media_mime_type', 'posted_at',
            ]);
        });
    }
};
