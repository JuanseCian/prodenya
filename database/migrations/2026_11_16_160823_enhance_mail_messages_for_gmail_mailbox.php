<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mail_messages', function (Blueprint $table): void {
            $table->string('provider', 30)->default('google')->after('message_id')->index();
            $table->string('provider_message_id')->nullable()->after('provider')->index();
            $table->string('folder', 30)->default('inbox')->after('provider_message_id')->index();
            $table->string('preview', 500)->nullable()->after('body_html');
            $table->json('labels')->nullable()->after('attachments');
            $table->boolean('is_starred')->default(false)->after('is_read')->index();
            $table->boolean('has_attachments')->default(false)->after('is_starred')->index();
        });

        DB::table('mail_messages')
            ->where('direction', 'outgoing')
            ->update(['provider' => 'hostinger']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_messages', function (Blueprint $table): void {
            $table->dropColumn([
                'provider',
                'provider_message_id',
                'folder',
                'preview',
                'labels',
                'is_starred',
                'has_attachments',
            ]);
        });
    }
};
