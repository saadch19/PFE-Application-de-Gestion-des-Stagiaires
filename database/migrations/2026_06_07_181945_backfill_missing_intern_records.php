<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill missing `interns` records for users that have the "Stagiaire" role
 * but were created via /users/create without the intern-specific fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        $stagiaireRoleId = DB::table('roles')->where('name', 'Stagiaire')->value('id');

        if (! $stagiaireRoleId) {
            return;
        }

        $orphanUsers = DB::table('users')
            ->where('role_id', $stagiaireRoleId)
            ->whereNotIn('id', DB::table('interns')->pluck('user_id'))
            ->get(['id', 'created_at']);

        foreach ($orphanUsers as $user) {
            DB::table('interns')->insert([
                'user_id'     => $user->id,
                'cin'         => 'PENDING-' . $user->id,
                'school'      => 'Non renseigné',
                'specialty'   => 'Non renseigné',
                'phone'       => null,
                'start_date'  => $user->created_at,
                'end_date'    => now()->addMonths(6)->toDateString(),
                'is_archived' => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Not reversible — the records would need manual cleanup
    }
};
