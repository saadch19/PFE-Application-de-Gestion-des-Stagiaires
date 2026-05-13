<?php

namespace Database\Seeders;

use App\Models\Intern;
use App\Models\Internship;
use App\Models\InternshipRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class InternsAndTasksSeeder extends Seeder
{
    public function run(): void
    {
        $stagiaireRoleId = Role::query()->where('name', 'Stagiaire')->value('id');
        $encadrantRoleId = Role::query()->where('name', 'Encadrant')->value('id');
        $responsableRoleId = Role::query()->where('name', 'Responsable de competence')->value('id');

        // Get existing encadrant and responsable users
        $encadrant = User::query()->where('email', 'encadrant@internships.local')->first();
        $responsable = User::query()->where('email', 'responsable@internships.local')->first();

        // Create 4 stagiaire users and their intern records
        $internsData = [
            [
                'full_name' => 'Alice Dupont',
                'email' => 'alice.dupont@internships.local',
                'cin' => 'AB123456',
                'school' => 'Université de Paris',
                'specialty' => 'Informatique',
                'phone' => '0123456789',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(60),
            ],
            [
                'full_name' => 'Bob Martin',
                'email' => 'bob.martin@internships.local',
                'cin' => 'CD789012',
                'school' => 'École Polytechnique',
                'specialty' => 'Développement Web',
                'phone' => '0987654321',
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->addDays(75),
            ],
            [
                'full_name' => 'Claire Bernard',
                'email' => 'claire.bernard@internships.local',
                'cin' => 'EF345678',
                'school' => 'Université de Lyon',
                'specialty' => 'Data Science',
                'phone' => '0567890123',
                'start_date' => Carbon::now()->subDays(7),
                'end_date' => Carbon::now()->addDays(83),
            ],
            [
                'full_name' => 'David Petit',
                'email' => 'david.petit@internships.local',
                'cin' => 'GH901234',
                'school' => 'Université de Marseille',
                'specialty' => 'Cybersécurité',
                'phone' => '0345678901',
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => Carbon::now()->addDays(70),
            ],
        ];

        foreach ($internsData as $data) {
            // Create user
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'full_name' => $data['full_name'],
                    'password_hash' => Hash::make('password123'),
                    'role_id' => $stagiaireRoleId,
                    'is_active' => true,
                ]
            );

            // Create intern
            $intern = Intern::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'cin' => $data['cin'],
                    'school' => $data['school'],
                    'specialty' => $data['specialty'],
                    'phone' => $data['phone'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'is_archived' => false,
                ]
            );

            // Create internship for each intern
            $internship = Internship::query()->updateOrCreate(
                ['title' => 'Stage en ' . $data['specialty']],
                [
                    'description' => 'Stage pratique en ' . $data['specialty'] . ' au sein de l\'entreprise.',
                    'department' => 'Informatique',
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'status' => 'en_cours',
                    'supervisor_id' => $encadrant->id,
                    'responsible_id' => $responsable->id,
                ]
            );

            $internship->interns()->syncWithoutDetaching([$intern->id]);

            // Create different tasks for each intern
            $tasksData = [
                [
                    'title' => 'Analyse des exigences',
                    'details' => 'Analyser les exigences du projet et rédiger un cahier des charges.',
                    'due_date' => Carbon::now()->addDays(10),
                    'status' => 'a_faire',
                ],
                [
                    'title' => 'Développement de la fonctionnalité principale',
                    'details' => 'Implémenter la fonctionnalité principale selon les spécifications.',
                    'due_date' => Carbon::now()->addDays(25),
                    'status' => 'a_faire',
                ],
                [
                    'title' => 'Tests unitaires',
                    'details' => 'Écrire et exécuter des tests unitaires pour valider le code.',
                    'due_date' => Carbon::now()->addDays(35),
                    'status' => 'a_faire',
                ],
                [
                    'title' => 'Documentation',
                    'details' => 'Rédiger la documentation technique et utilisateur.',
                    'due_date' => Carbon::now()->addDays(45),
                    'status' => 'a_faire',
                ],
            ];

            foreach ($tasksData as $taskData) {
                Task::query()->updateOrCreate(
                    [
                        'internship_id' => $internship->id,
                        'title' => $taskData['title'],
                    ],
                    [
                        'assigned_by' => $encadrant->id,
                        'assigned_to' => $user->id,
                        'details' => $taskData['details'],
                        'due_date' => $taskData['due_date'],
                        'status' => $taskData['status'],
                    ]
                );
            }

            // Create different requests for each intern
            $requestsData = [
                [
                    'type' => 'prolongation',
                    'message' => 'Je souhaite prolonger mon stage de 2 semaines supplémentaires pour finaliser le projet.',
                    'status' => 'en_attente',
                ],
                [
                    'type' => 'attestation',
                    'message' => 'Demande d\'attestation de stage pour mes dossiers administratifs.',
                    'status' => 'en_attente',
                ],
                [
                    'type' => 'autre',
                    'message' => 'Demande de matériel supplémentaire : un deuxième écran pour améliorer la productivité.',
                    'status' => 'en_attente',
                ],
            ];

            foreach ($requestsData as $requestData) {
                InternshipRequest::query()->create([
                    'intern_id' => $intern->id,
                    'type' => $requestData['type'],
                    'message' => $requestData['message'],
                    'status' => $requestData['status'],
                    'processed_by' => null,
                ]);
            }
        }
    }
}