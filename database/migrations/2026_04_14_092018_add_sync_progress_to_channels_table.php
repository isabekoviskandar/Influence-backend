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
            $table->string('sync_status')->default('idle')->after('is_active');
            $table->integer('sync_current')->nullable()->after('sync_status');
            $table->integer('sync_total')->nullable()->after('sync_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'sync_current', 'sync_total']);
        });
    }
};
