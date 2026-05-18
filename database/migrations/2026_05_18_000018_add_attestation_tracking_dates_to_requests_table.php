<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table): void {
            $table->timestamp('attestation_printed_at')->nullable()->after('rh_processed_at');
            $table->timestamp('attestation_recovered_at')->nullable()->after('attestation_printed_at');
            $table->timestamp('attestation_archived_at')->nullable()->after('attestation_recovered_at');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table): void {
            $table->dropColumn([
                'attestation_printed_at',
                'attestation_recovered_at',
                'attestation_archived_at',
            ]);
        });
    }
};
