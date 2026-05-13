<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('internship_intern') || ! Schema::hasTable('internships')) {
            return;
        }

        $rows = DB::table('internships')
            ->select('id', 'intern_id')
            ->whereNotNull('intern_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('internship_intern')->updateOrInsert(
                ['internship_id' => $row->id, 'intern_id' => $row->intern_id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('internship_intern')) {
            return;
        }

        DB::table('internship_intern')->truncate();
    }
};
