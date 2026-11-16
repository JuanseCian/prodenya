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
        Schema::table('google_oauth_tokens', function (Blueprint $table): void {
            $table->string('history_id')->nullable()->after('authorized_at');
            $table->timestamp('last_synced_at')->nullable()->after('history_id');
            $table->unsignedInteger('last_sync_count')->default(0)->after('last_synced_at');
            $table->text('last_sync_error')->nullable()->after('last_sync_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_oauth_tokens', function (Blueprint $table): void {
            $table->dropColumn([
                'history_id',
                'last_synced_at',
                'last_sync_count',
                'last_sync_error',
            ]);
        });
    }
};
