<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE requests MODIFY type ENUM('prolongation', 'attestation', 'absence', 'autre') NOT NULL");

        Schema::table('requests', function (Blueprint $table) {
            $table->string('motif_absence', 255)->nullable()->after('type');
            $table->timestamp('absence_generated_at')->nullable()->after('processed_by');
        });
    }

    public function down(): void
    {
        DB::table('requests')
            ->where('type', 'absence')
            ->update(['type' => 'autre']);

        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['motif_absence', 'absence_generated_at']);
        });

        DB::statement("ALTER TABLE requests MODIFY type ENUM('prolongation', 'attestation', 'autre') NOT NULL");
    }
};
