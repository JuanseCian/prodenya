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
        Schema::table('mail_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('mailbox_uid')->nullable()->unique()->after('message_id');
            $table->timestamp('synced_at')->nullable()->after('received_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_messages', function (Blueprint $table) {
            $table->dropUnique('mail_messages_mailbox_uid_unique');
            $table->dropColumn(['mailbox_uid', 'synced_at']);
        });
    }
};
