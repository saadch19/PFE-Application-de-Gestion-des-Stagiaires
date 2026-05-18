<?php

namespace Database\Seeders;

use App\Models\Intern;
use App\Models\Internship;
use App\Models\InternshipRequest;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Models\Absence;
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

        $extraEncadrant = User::query()->updateOrCreate(
            ['email' => 'encadrant.qualite@internships.local'],
            [
                'full_name' => 'Encadrant Qualite',
                'password_hash' => Hash::make('password123'),
                'role_id' => $encadrantRoleId,
                'is_active' => true,
            ]
        );

        $extraResponsable = User::query()->updateOrCreate(
            ['email' => 'responsable.digital@internships.local'],
            [
                'full_name' => 'Responsable Digital',
                'password_hash' => Hash::make('password123'),
                'role_id' => $responsableRoleId,
                'is_active' => true,
            ]
        );

        $testCases = [
            [
                'full_name' => 'Nadia Active',
                'email' => 'nadia.active@internships.local',
                'cin' => 'IJ567890',
                'school' => 'ENSA Casablanca',
                'specialty' => 'Genie Logiciel',
                'phone' => '0611111111',
                'start_date' => Carbon::now()->subDays(12),
                'end_date' => Carbon::now()->addDays(45),
                'internship' => [
                    'title' => 'Stage actif - application RH',
                    'status' => 'en_cours',
                    'supervisor' => $extraEncadrant,
                    'responsible' => $extraResponsable,
                ],
                'tasks' => [
                    ['title' => 'Maquette du module RH', 'due_date' => Carbon::now()->addDays(5), 'status' => 'termine'],
                    ['title' => 'Integration du formulaire', 'due_date' => Carbon::now()->addDays(12), 'status' => 'en_cours'],
                ],
                'absences' => [
                    ['date_absence' => Carbon::now()->subDays(3), 'reason' => 'Rendez-vous administratif', 'justified' => true],
                ],
            ],
            [
                'full_name' => 'Omar En Attente',
                'email' => 'omar.attente@internships.local',
                'cin' => 'KL123789',
                'school' => 'Faculte des Sciences Rabat',
                'specialty' => 'Reseaux',
                'phone' => '0622222222',
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(67),
                'internship' => null,
                'tasks' => [],
                'absences' => [],
            ],
            [
                'full_name' => 'Sara Termine',
                'email' => 'sara.termine@internships.local',
                'cin' => 'MN456123',
                'school' => 'EST Fes',
                'specialty' => 'Systemes Informatiques',
                'phone' => '0633333333',
                'start_date' => Carbon::now()->subDays(70),
                'end_date' => Carbon::now()->subDays(5),
                'internship' => [
                    'title' => 'Stage termine - support IT',
                    'status' => 'termine',
                    'supervisor' => $encadrant,
                    'responsible' => $responsable,
                ],
                'tasks' => [
                    ['title' => 'Inventaire du parc informatique', 'due_date' => Carbon::now()->subDays(20), 'status' => 'termine'],
                    ['title' => 'Rapport final support IT', 'due_date' => Carbon::now()->subDays(8), 'status' => 'termine'],
                ],
                'absences' => [
                    ['date_absence' => Carbon::now()->subDays(30), 'reason' => 'Maladie justifiee', 'justified' => true],
                    ['date_absence' => Carbon::now()->subDays(18), 'reason' => 'Transport', 'justified' => false],
                ],
            ],
            [
                'full_name' => 'Yassine Alertes',
                'email' => 'yassine.alertes@internships.local',
                'cin' => 'OP789456',
                'school' => 'Universite Hassan II',
                'specialty' => 'Business Intelligence',
                'phone' => '0644444444',
                'start_date' => Carbon::now()->subDays(25),
                'end_date' => Carbon::now()->addDays(35),
                'internship' => [
                    'title' => 'Stage alertes - tableaux de bord',
                    'status' => 'en_cours',
                    'supervisor' => $extraEncadrant,
                    'responsible' => $responsable,
                ],
                'tasks' => [
                    ['title' => 'Nettoyage des donnees BI', 'due_date' => Carbon::now()->subDays(7), 'status' => 'a_faire'],
                    ['title' => 'Dashboard Power BI', 'due_date' => Carbon::now()->subDays(2), 'status' => 'en_cours'],
                    ['title' => 'Synthese hebdomadaire', 'due_date' => Carbon::now()->addDays(6), 'status' => 'a_faire'],
                ],
                'absences' => [
                    ['date_absence' => Carbon::now()->subDays(20), 'reason' => 'Absence non justifiee', 'justified' => false],
                    ['date_absence' => Carbon::now()->subDays(15), 'reason' => 'Retard transport', 'justified' => false],
                    ['date_absence' => Carbon::now()->subDays(10), 'reason' => 'Maladie', 'justified' => true],
                    ['date_absence' => Carbon::now()->subDays(4), 'reason' => 'Absence non justifiee', 'justified' => false],
                ],
            ],
        ];

        foreach ($testCases as $case) {
            $user = User::query()->updateOrCreate(
                ['email' => $case['email']],
                [
                    'full_name' => $case['full_name'],
                    'password_hash' => Hash::make('password123'),
                    'role_id' => $stagiaireRoleId,
                    'is_active' => true,
                ]
            );

            $intern = Intern::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'cin' => $case['cin'],
                    'school' => $case['school'],
                    'specialty' => $case['specialty'],
                    'phone' => $case['phone'],
                    'start_date' => $case['start_date'],
                    'end_date' => $case['end_date'],
                    'is_archived' => false,
                ]
            );

            if ($case['internship'] !== null) {
                $internship = Internship::query()->updateOrCreate(
                    ['title' => $case['internship']['title']],
                    [
                        'description' => 'Donnees de test pour verifier les differents cas de la plateforme.',
                        'department' => 'Informatique',
                        'start_date' => $case['start_date'],
                        'end_date' => $case['end_date'],
                        'status' => $case['internship']['status'],
                        'supervisor_id' => $case['internship']['supervisor']->id,
                        'responsible_id' => $case['internship']['responsible']->id,
                    ]
                );

                $internship->interns()->syncWithoutDetaching([$intern->id]);

                foreach ($case['tasks'] as $taskData) {
                    Task::query()->updateOrCreate(
                        [
                            'internship_id' => $internship->id,
                            'title' => $taskData['title'],
                        ],
                        [
                            'assigned_by' => $case['internship']['supervisor']->id,
                            'assigned_to' => $user->id,
                            'details' => 'Tache de test pour verifier les scores, delais et alertes.',
                            'due_date' => $taskData['due_date'],
                            'status' => $taskData['status'],
                        ]
                    );
                }
            }

            foreach ($case['absences'] as $absenceData) {
                Absence::query()->updateOrCreate(
                    [
                        'intern_id' => $intern->id,
                        'date_absence' => $absenceData['date_absence']->toDateString(),
                    ],
                    [
                        'reason' => $absenceData['reason'],
                        'justified' => $absenceData['justified'],
                        'recorded_by' => $extraResponsable->id,
                    ]
                );
            }

            InternshipRequest::query()->updateOrCreate(
                [
                    'intern_id' => $intern->id,
                    'type' => 'attestation',
                    'message' => 'Demande de test pour verifier le dashboard.',
                ],
                [
                    'status' => 'en_attente',
                    'processed_by' => null,
                ]
            );
        }
    }
}
