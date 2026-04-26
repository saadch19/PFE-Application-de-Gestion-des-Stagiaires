<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Administrateur',
            'Responsable de competence',
            'Encadrant',
            'Stagiaire',
        ];

        foreach ($roles as $role) {
            Role::query()->firstOrCreate(['name' => $role]);
        }
    }
}
