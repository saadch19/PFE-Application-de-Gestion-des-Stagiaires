<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'rh_signature_path')) {
                $table->string('rh_signature_path')->nullable()->after('remember_token');
            }

            if (! Schema::hasColumn('users', 'company_stamp_path')) {
                $table->string('company_stamp_path')->nullable()->after('rh_signature_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_stamp_path')) {
                $table->dropColumn('company_stamp_path');
            }

            if (Schema::hasColumn('users', 'rh_signature_path')) {
                $table->dropColumn('rh_signature_path');
            }
        });
    }
};
