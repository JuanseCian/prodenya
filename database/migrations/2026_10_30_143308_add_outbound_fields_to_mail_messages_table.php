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
            $table->string('direction', 10)->default('incoming')->after('mailbox_uid')->index();
            $table->string('in_reply_to')->nullable()->after('thread_id')->index();
            $table->timestamp('sent_at')->nullable()->after('received_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_messages', function (Blueprint $table) {
            $table->dropIndex(['direction']);
            $table->dropIndex(['in_reply_to']);
            $table->dropIndex(['sent_at']);
            $table->dropColumn(['direction', 'in_reply_to', 'sent_at']);
        });
    }
};
