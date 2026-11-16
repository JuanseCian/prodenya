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
        Schema::table('mail_messages', function (Blueprint $table): void {
            $table->string('cc_email')->nullable()->after('to_email');
            $table->string('bcc_email')->nullable()->after('cc_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mail_messages', function (Blueprint $table): void {
            $table->dropColumn(['cc_email', 'bcc_email']);
        });
    }
};
