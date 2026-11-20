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
        if (! Schema::hasColumn('mail_classification_events', 'categoria_anterior')) {
            Schema::table('mail_classification_events', function (Blueprint $table): void {
                $table->string('categoria_anterior', 40)->nullable()->after('mail_message_id');
            });
        }

        if (! Schema::hasColumn('mail_classification_events', 'estado_anterior')) {
            Schema::table('mail_classification_events', function (Blueprint $table): void {
                $table->string('estado_anterior', 40)->nullable()->after('categoria_anterior');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('mail_classification_events', 'categoria_anterior')
                ? 'categoria_anterior'
                : null,
            Schema::hasColumn('mail_classification_events', 'estado_anterior')
                ? 'estado_anterior'
                : null,
        ]));

        if ($columns !== []) {
            Schema::table('mail_classification_events', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
