<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'full_name' => 'Admin Principal',
                'email' => 'admin@internships.local',
                'role' => 'Administrateur',
            ],
            [
                'full_name' => 'Responsable Competence',
                'email' => 'responsable@internships.local',
                'role' => 'Responsable de competence',
            ],
            [
                'full_name' => 'Encadrant Principal',
                'email' => 'encadrant@internships.local',
                'role' => 'Encadrant',
            ],
            [
                'full_name' => 'Stagiaire Demo',
                'email' => 'stagiaire@internships.local',
                'role' => 'Stagiaire',
            ],
        ];

        foreach ($accounts as $account) {
            $roleId = Role::query()->where('name', $account['role'])->value('id');

            if ($roleId === null) {
                continue;
            }

            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'full_name' => $account['full_name'],
                    'password_hash' => Hash::make('password123'),
                    'role_id' => $roleId,
                    'is_active' => true,
                ]
            );
        }
    }
}
