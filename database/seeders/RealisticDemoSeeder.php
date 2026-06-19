<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\DailyLog;
use App\Models\Intern;
use App\Models\Internship;
use App\Models\InternshipRequest;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RealisticDemoSeeder extends Seeder
{
    private string $passwordHash;

    // ── Moroccan realistic data pools ────────────────────────────────────

    private array $firstNamesMale = [
        'Youssef', 'Mohamed', 'Omar', 'Amine', 'Hamza', 'Mehdi',
        'Karim', 'Rachid', 'Soufiane', 'Ayoub', 'Zakaria', 'Ilyas',
        'Bilal', 'Othmane', 'Adil', 'Khalid', 'Nabil', 'Samir',
        'Hicham', 'Reda', 'Mouad', 'Taha', 'Badr', 'Fouad',
    ];

    private array $firstNamesFemale = [
        'Fatima Zahra', 'Salma', 'Imane', 'Hajar', 'Khadija', 'Meryem',
        'Sanaa', 'Loubna', 'Amina', 'Nadia', 'Houda', 'Zineb',
        'Ghita', 'Sara', 'Layla', 'Rim', 'Asmae', 'Dounia',
        'Wafa', 'Ikram', 'Hanane', 'Najat', 'Soukaina', 'Chaimae',
    ];

    private array $lastNames = [
        'El Amrani', 'Benjelloun', 'Bouazza', 'El Idrissi', 'Tahiri',
        'Benhaddou', 'El Fassi', 'Zouaki', 'Lahlou', 'El Mansouri',
        'Chraibi', 'Berrada', 'El Ouazzani', 'Tazi', 'Senhaji',
        'Benkirane', 'El Harti', 'Ait Brahim', 'Naciri', 'Kettani',
        'Alaoui', 'Sqalli', 'El Guerrouj', 'Daoudi', 'Jazouli',
        'Sahraoui', 'Filali', 'Sefrioui', 'Belkadi', 'Moussaoui',
    ];

    private array $schools = [
        'ENSIAS - Rabat',
        'INPT - Rabat',
        'EMI - Rabat',
        'FST Mohammedia',
        'ENSA Marrakech',
        'ENSA Kenitra',
        'Faculté des Sciences Ain Chock - Casablanca',
        'EST Salé',
        'ENCG Casablanca',
        'OFPPT ISTA Hay Riad - Rabat',
        'EMSI Casablanca',
        'Université Al Akhawayn - Ifrane',
        'ENSA Tétouan',
        'FST Fès',
        'ENSET Mohammedia',
        'Sup\'Management Casablanca',
        'UIR - Rabat',
    ];

    private array $specialties = [
        'Génie Logiciel',
        'Génie Informatique',
        'Développement Web Full Stack',
        'Data Science & Intelligence Artificielle',
        'Réseaux et Systèmes',
        'Cloud Computing & DevOps',
        'Cybersécurité',
        'Systèmes Embarqués',
        'Finance et Comptabilité',
        'Marketing Digital',
        'Génie Industriel',
        'Commerce International',
        'Ressources Humaines',
        'Design Graphique et Multimédia',
    ];

    private array $departments = [
        'Direction des Systèmes d\'Information',
        'Développement Logiciel',
        'Infrastructure IT',
        'Data & Analytics',
        'Ressources Humaines',
        'Finance et Comptabilité',
        'Marketing et Communication',
        'Qualité et Conformité',
        'Support Technique',
        'Innovation et R&D',
    ];

    private array $internshipTitles = [
        'Développement d\'une application web de gestion',
        'Migration vers une architecture microservices',
        'Mise en place d\'un pipeline CI/CD',
        'Conception d\'un tableau de bord analytique',
        'Automatisation des tests fonctionnels',
        'Développement d\'une API RESTful',
        'Intégration d\'un système de paiement en ligne',
        'Refonte de l\'interface utilisateur',
        'Mise en place d\'un système de monitoring',
        'Développement d\'un chatbot intelligent',
        'Analyse et visualisation de données',
        'Optimisation des performances applicatives',
        'Conception d\'une application mobile cross-platform',
        'Mise en place d\'une solution de stockage cloud',
        'Étude et déploiement d\'une solution ERP',
    ];

    private array $taskTitles = [
        'Analyser les besoins fonctionnels du projet',
        'Rédiger le cahier des charges technique',
        'Concevoir le schéma de base de données',
        'Développer le module d\'authentification',
        'Intégrer les maquettes UI/UX',
        'Développer l\'API de gestion des utilisateurs',
        'Implémenter les tests unitaires',
        'Configurer l\'environnement de déploiement',
        'Documenter le code source et l\'architecture',
        'Corriger les bugs remontés lors des tests',
        'Optimiser les requêtes SQL',
        'Mettre en place la gestion des rôles et permissions',
        'Développer le module de reporting',
        'Implémenter la fonctionnalité de recherche avancée',
        'Préparer la présentation finale du projet',
        'Réaliser les tests d\'intégration',
        'Configurer le serveur de production',
        'Développer le module de notification',
        'Créer les fixtures de test',
        'Implémenter l\'export PDF des rapports',
        'Mettre en place le système de cache',
        'Développer le module de gestion des fichiers',
        'Rédiger le rapport de stage',
        'Réaliser la revue de code avec l\'encadrant',
        'Mettre en place le versioning Git',
    ];

    private array $taskDetails = [
        'Analyser l\'existant et identifier les axes d\'amélioration. Documenter les flux métier principaux et les cas d\'utilisation.',
        'Rédiger un document technique détaillant l\'architecture, les technologies utilisées et les choix de conception.',
        'Concevoir un modèle de données relationnel conforme aux règles métier. Utiliser un outil de modélisation et produire un diagramme ER.',
        'Implémenter un système d\'authentification sécurisé avec gestion des sessions, hashage des mots de passe et protection CSRF.',
        'Transformer les maquettes fournies par le designer en composants HTML/CSS/JS réutilisables et responsive.',
        'Développer les endpoints RESTful pour le CRUD des entités principales, avec validation des données et gestion des erreurs.',
        'Écrire des tests unitaires couvrant au minimum 80% du code métier. Utiliser PHPUnit et les factories Laravel.',
        'Configurer les serveurs staging et production avec Docker, Nginx, et les variables d\'environnement.',
        'Rédiger la documentation technique (README, guide d\'installation, API docs) et les commentaires de code.',
        'Identifier et corriger les bugs critiques. Vérifier la compatibilité navigateurs et la gestion des cas limites.',
    ];

    private array $dailyNotes = [
        'Travaillé sur le développement du module principal. Bonne progression.',
        'Réunion avec l\'encadrant pour discuter de l\'architecture. Corrections apportées au schéma de base de données.',
        'Implémentation des endpoints API. Tests postman effectués avec succès.',
        'Résolution de bugs identifiés lors de la revue de code. Mise à jour de la documentation.',
        'Travail sur l\'interface utilisateur. Intégration des composants Bootstrap.',
        'Session de pair programming avec un collègue. Refactoring du module de validation.',
        'Formation sur Docker et les bonnes pratiques de déploiement.',
        'Tests d\'intégration et correction des erreurs de validation côté serveur.',
        'Préparation de la présentation de mi-parcours. Création des slides.',
        'Optimisation des requêtes et amélioration du temps de réponse de l\'API.',
        'Analyse des retours utilisateurs et planification des améliorations.',
        'Implémentation du système de cache Redis pour les données fréquemment utilisées.',
        'Travail sur la sécurité : validation des entrées, protection XSS et CSRF.',
        'Documentation des API avec Swagger/OpenAPI. Mise à jour du README.',
        'Réunion d\'avancement avec l\'équipe. Démonstration des fonctionnalités développées.',
        'Développement du module de notifications par email et dans l\'application.',
        'Revue de code et corrections. Respect des conventions de nommage PSR-12.',
        'Mise en place des migrations et des seeders pour l\'environnement de test.',
        'Travail sur le responsive design et les tests sur différents appareils.',
        'Configuration de l\'environnement CI/CD avec GitHub Actions.',
    ];

    private array $absenceReasons = [
        'Rendez-vous médical',
        'Maladie',
        'Raison familiale',
        'Problème de transport',
        'Examen universitaire',
        'Raison personnelle',
        'Convocation administrative',
        'Grève des transports',
    ];

    private array $messageSubjects = [
        'Suivi du projet de stage',
        'Demande de réunion',
        'Question technique',
        'Rapport hebdomadaire',
        'Planification de la semaine',
        'Retour sur la présentation',
        'Documentation à fournir',
        'Mise à jour du planning',
        'Demande d\'information',
        'Confirmation de réunion',
    ];

    private array $messageBodies = [
        'Bonjour, je voulais faire le point sur l\'avancement du projet. Pourriez-vous me faire un résumé de ce qui a été accompli cette semaine ?',
        'Salam, est-ce que nous pourrions organiser une réunion demain pour discuter des prochaines étapes du projet ? Je suis disponible à partir de 10h.',
        'Bonjour, j\'ai une question concernant l\'architecture du module de gestion. Pourriez-vous m\'éclairer sur le choix de la base de données ?',
        'Ci-joint le rapport hebdomadaire de cette semaine. Les principales avancées concernent le module d\'authentification et les tests unitaires.',
        'Voici la planification pour la semaine à venir. Merci de me confirmer que les objectifs sont réalistes et atteignables.',
        'Merci pour votre présentation d\'hier. Les retours de l\'équipe sont très positifs. Continuez comme ça !',
        'Pourriez-vous me fournir les documents suivants : attestation de stage, convention signée, et fiche d\'évaluation ?',
        'Suite aux changements dans le planning, voici la version mise à jour. Les délais ont été ajustés en conséquence.',
        'Bonjour, j\'aurais besoin de quelques informations sur les procédures internes. Qui contacter pour les questions RH ?',
        'La réunion de demain est confirmée à 14h30 dans la salle de conférence. Merci de préparer votre avancement.',
        'J\'ai terminé le développement du module de reporting. Merci de tester et me faire vos retours.',
        'Bonjour, le déploiement en staging a été effectué avec succès. Vous pouvez commencer les tests de validation.',
    ];

    private array $moroccanCities = [
        'Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger',
        'Meknès', 'Oujda', 'Kenitra', 'Agadir', 'Tétouan',
        'Salé', 'Mohammedia', 'El Jadida', 'Béni Mellal',
    ];

    public function run(): void
    {
        $this->passwordHash = Hash::make('password123');

        // Temporarily disable email/notification events while seeding
        Intern::withoutEvents(function () {
            $this->seedUsersAndInterns();
        });

        $this->command->info('✓ Users & Interns created');

        $this->seedInternships();
        $this->command->info('✓ Internships created');

        $this->seedTasks();
        $this->command->info('✓ Tasks created');

        $this->seedDailyLogs();
        $this->command->info('✓ Daily Logs created');

        $this->seedAbsences();
        $this->command->info('✓ Absences created');

        $this->seedRequests();
        $this->command->info('✓ Requests created');

        $this->seedMessages();
        $this->command->info('✓ Messages created');

        $this->seedWeeklyReports();
        $this->command->info('✓ Weekly Reports created');

        $this->seedNotifications();
        $this->command->info('✓ Notifications created');

        $this->seedPasswordResetRequests();
        $this->command->info('✓ Password Reset Requests created');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  USERS & INTERNS
    // ═══════════════════════════════════════════════════════════════════════

    private array $adminIds = [];
    private array $rhIds = [];
    private array $rcIds = [];
    private array $encadrantIds = [];
    private array $stagiaireUserIds = [];
    private array $internRecords = [];  // Intern model instances

    private function seedUsersAndInterns(): void
    {
        $roleAdmin = Role::where('name', 'Administrateur')->first()->id;
        $roleRH = Role::where('name', 'Responsable RH')->first()->id;
        $roleRC = Role::where('name', 'Responsable de competence')->first()->id;
        $roleEncadrant = Role::where('name', 'Encadrant')->first()->id;
        $roleStagiaire = Role::where('name', 'Stagiaire')->first()->id;

        // ── 2 Admins ─────────────────────────────────────────────────────
        $admins = [
            ['full_name' => 'Khalid El Amrani', 'email' => 'khalid.admin@test.com'],
            ['full_name' => 'Nadia Benjelloun', 'email' => 'nadia.admin@test.com'],
        ];
        foreach ($admins as $a) {
            $u = User::updateOrCreate(['email' => $a['email']], [
                'full_name' => $a['full_name'],
                'password_hash' => $this->passwordHash,
                'role_id' => $roleAdmin,
                'is_active' => true,
            ]);
            $this->adminIds[] = $u->id;
        }

        // ── 2 Responsables RH ────────────────────────────────────────────
        $rhs = [
            ['full_name' => 'Amina Tazi', 'email' => 'amina.rh@test.com'],
            ['full_name' => 'Rachid Senhaji', 'email' => 'rachid.rh@test.com'],
        ];
        foreach ($rhs as $r) {
            $u = User::updateOrCreate(['email' => $r['email']], [
                'full_name' => $r['full_name'],
                'password_hash' => $this->passwordHash,
                'role_id' => $roleRH,
                'is_active' => true,
            ]);
            $this->rhIds[] = $u->id;
        }

        // ── 3 Responsables de compétence ─────────────────────────────────
        $rcs = [
            ['full_name' => 'Hicham Lahlou', 'email' => 'hicham.rc@test.com'],
            ['full_name' => 'Sanaa El Fassi', 'email' => 'sanaa.rc@test.com'],
            ['full_name' => 'Mohamed Chraibi', 'email' => 'mohamed.rc@test.com'],
        ];
        foreach ($rcs as $rc) {
            $u = User::updateOrCreate(['email' => $rc['email']], [
                'full_name' => $rc['full_name'],
                'password_hash' => $this->passwordHash,
                'role_id' => $roleRC,
                'is_active' => true,
            ]);
            $this->rcIds[] = $u->id;
        }

        // ── 5 Encadrants ─────────────────────────────────────────────────
        $encadrants = [
            ['full_name' => 'Karim Berrada', 'email' => 'karim.enc@test.com'],
            ['full_name' => 'Loubna Naciri', 'email' => 'loubna.enc@test.com'],
            ['full_name' => 'Soufiane Kettani', 'email' => 'soufiane.enc@test.com'],
            ['full_name' => 'Houda Alaoui', 'email' => 'houda.enc@test.com'],
            ['full_name' => 'Fouad Daoudi', 'email' => 'fouad.enc@test.com'],
        ];
        foreach ($encadrants as $enc) {
            $u = User::updateOrCreate(['email' => $enc['email']], [
                'full_name' => $enc['full_name'],
                'password_hash' => $this->passwordHash,
                'role_id' => $roleEncadrant,
                'is_active' => true,
            ]);
            $this->encadrantIds[] = $u->id;
        }

        // ── 16 Stagiaires ────────────────────────────────────────────────
        $interns = [
            // Currently active interns (12)
            ['name' => 'Youssef Bouazza',       'cin' => 'BK' . rand(100000, 999999), 'school' => 'ENSIAS - Rabat',              'specialty' => 'Génie Logiciel',                    'start' => '2026-04-01', 'end' => '2026-07-31', 'archived' => false],
            ['name' => 'Fatima Zahra El Idrissi','cin' => 'BH' . rand(100000, 999999), 'school' => 'INPT - Rabat',                'specialty' => 'Data Science & Intelligence Artificielle', 'start' => '2026-04-15', 'end' => '2026-07-15', 'archived' => false],
            ['name' => 'Amine Tahiri',          'cin' => 'BB' . rand(100000, 999999), 'school' => 'EMI - Rabat',                 'specialty' => 'Génie Informatique',                 'start' => '2026-03-15', 'end' => '2026-07-15', 'archived' => false],
            ['name' => 'Salma Benhaddou',       'cin' => 'BJ' . rand(100000, 999999), 'school' => 'EMSI Casablanca',             'specialty' => 'Développement Web Full Stack',       'start' => '2026-05-01', 'end' => '2026-08-31', 'archived' => false],
            ['name' => 'Hamza Zouaki',          'cin' => 'BE' . rand(100000, 999999), 'school' => 'FST Mohammedia',              'specialty' => 'Réseaux et Systèmes',                'start' => '2026-04-01', 'end' => '2026-06-30', 'archived' => false],
            ['name' => 'Imane El Mansouri',     'cin' => 'BM' . rand(100000, 999999), 'school' => 'ENSA Marrakech',              'specialty' => 'Cloud Computing & DevOps',           'start' => '2026-05-15', 'end' => '2026-08-15', 'archived' => false],
            ['name' => 'Mehdi Lahlou',          'cin' => 'BA' . rand(100000, 999999), 'school' => 'Faculté des Sciences Ain Chock - Casablanca', 'specialty' => 'Cybersécurité', 'start' => '2026-04-01', 'end' => '2026-07-31', 'archived' => false],
            ['name' => 'Hajar Chraibi',         'cin' => 'BL' . rand(100000, 999999), 'school' => 'UIR - Rabat',                 'specialty' => 'Génie Logiciel',                    'start' => '2026-05-01', 'end' => '2026-07-31', 'archived' => false],
            ['name' => 'Omar El Ouazzani',      'cin' => 'BN' . rand(100000, 999999), 'school' => 'EST Salé',                    'specialty' => 'Développement Web Full Stack',       'start' => '2026-06-01', 'end' => '2026-08-31', 'archived' => false],
            ['name' => 'Khadija Sefrioui',      'cin' => 'BP' . rand(100000, 999999), 'school' => 'ENCG Casablanca',             'specialty' => 'Finance et Comptabilité',             'start' => '2026-04-15', 'end' => '2026-07-15', 'archived' => false],
            ['name' => 'Zakaria Filali',        'cin' => 'BQ' . rand(100000, 999999), 'school' => 'OFPPT ISTA Hay Riad - Rabat', 'specialty' => 'Systèmes Embarqués',                 'start' => '2026-05-01', 'end' => '2026-07-31', 'archived' => false],
            ['name' => 'Ghita Moussaoui',       'cin' => 'BR' . rand(100000, 999999), 'school' => 'ENSA Kenitra',                'specialty' => 'Génie Informatique',                 'start' => '2026-05-15', 'end' => '2026-08-15', 'archived' => false],

            // Archived (past) interns (4)
            ['name' => 'Bilal Sahraoui',        'cin' => 'BS' . rand(100000, 999999), 'school' => 'ENSA Tétouan',                'specialty' => 'Génie Logiciel',           'start' => '2026-01-15', 'end' => '2026-04-15', 'archived' => true],
            ['name' => 'Meryem Belkadi',        'cin' => 'BT' . rand(100000, 999999), 'school' => 'FST Fès',                     'specialty' => 'Data Science & Intelligence Artificielle', 'start' => '2026-01-01', 'end' => '2026-03-31', 'archived' => true],
            ['name' => 'Adil Jazouli',          'cin' => 'BU' . rand(100000, 999999), 'school' => 'ENSET Mohammedia',            'specialty' => 'Réseaux et Systèmes',      'start' => '2025-10-01', 'end' => '2026-01-31', 'archived' => true],
            ['name' => 'Zineb Sqalli',          'cin' => 'BV' . rand(100000, 999999), 'school' => 'Sup\'Management Casablanca',   'specialty' => 'Marketing Digital',        'start' => '2026-02-01', 'end' => '2026-04-30', 'archived' => true],
        ];

        foreach ($interns as $i => $data) {
            $emailSlug = strtolower(str_replace([' ', '\''], ['.', ''], $data['name']));
            $email = $emailSlug . '@test.com';

            $user = User::updateOrCreate(['email' => $email], [
                'full_name' => $data['name'],
                'password_hash' => $this->passwordHash,
                'role_id' => $roleStagiaire,
                'is_active' => !$data['archived'],
            ]);
            $this->stagiaireUserIds[] = $user->id;

            $phone = '06' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            $intern = Intern::updateOrCreate(['cin' => $data['cin']], [
                'user_id' => $user->id,
                'school' => $data['school'],
                'specialty' => $data['specialty'],
                'phone' => $phone,
                'start_date' => $data['start'],
                'end_date' => $data['end'],
                'is_archived' => $data['archived'],
            ]);

            $this->internRecords[] = $intern;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  INTERNSHIPS
    // ═══════════════════════════════════════════════════════════════════════

    private array $internshipRecords = [];

    private function seedInternships(): void
    {
        $titles = $this->internshipTitles;
        $depts = $this->departments;

        $internshipDefs = [
            // Active internships (matched with active interns)
            ['title' => $titles[0],  'dept' => $depts[0], 'status' => 'en_cours',  'enc' => 0, 'rc' => 0, 'interns' => [0, 1],      'grade' => null],
            ['title' => $titles[1],  'dept' => $depts[1], 'status' => 'en_cours',  'enc' => 1, 'rc' => 0, 'interns' => [2],          'grade' => null],
            ['title' => $titles[2],  'dept' => $depts[2], 'status' => 'en_cours',  'enc' => 2, 'rc' => 1, 'interns' => [3, 4],       'grade' => null],
            ['title' => $titles[3],  'dept' => $depts[3], 'status' => 'en_cours',  'enc' => 0, 'rc' => 1, 'interns' => [5],          'grade' => null],
            ['title' => $titles[4],  'dept' => $depts[1], 'status' => 'en_cours',  'enc' => 3, 'rc' => 2, 'interns' => [6, 7],       'grade' => null],
            ['title' => $titles[5],  'dept' => $depts[0], 'status' => 'en_cours',  'enc' => 4, 'rc' => 2, 'interns' => [8],          'grade' => null],
            ['title' => $titles[6],  'dept' => $depts[4], 'status' => 'planifie',  'enc' => 1, 'rc' => 0, 'interns' => [9],          'grade' => null],
            ['title' => $titles[7],  'dept' => $depts[5], 'status' => 'en_cours',  'enc' => 2, 'rc' => 1, 'interns' => [10, 11],     'grade' => null],

            // Completed internships (archived interns)
            ['title' => $titles[8],  'dept' => $depts[0], 'status' => 'termine', 'enc' => 0, 'rc' => 0, 'interns' => [12], 'grade' => 16.5],
            ['title' => $titles[9],  'dept' => $depts[3], 'status' => 'termine', 'enc' => 3, 'rc' => 1, 'interns' => [13], 'grade' => 14.0],
            ['title' => $titles[10], 'dept' => $depts[2], 'status' => 'termine', 'enc' => 4, 'rc' => 2, 'interns' => [14], 'grade' => 12.0],
            ['title' => $titles[11], 'dept' => $depts[6], 'status' => 'termine', 'enc' => 1, 'rc' => 0, 'interns' => [15], 'grade' => 17.5],
        ];

        foreach ($internshipDefs as $def) {
            $intern0 = $this->internRecords[$def['interns'][0]];
            $internship = Internship::create([
                'title' => $def['title'],
                'description' => 'Stage encadré au sein de l\'entreprise dans le département ' . $def['dept'] . '. Le stagiaire travaillera sur un projet concret avec un suivi régulier de l\'encadrant.',
                'department' => $def['dept'],
                'start_date' => $intern0->start_date,
                'end_date' => $intern0->end_date,
                'status' => $def['status'],
                'supervisor_id' => $this->encadrantIds[$def['enc']],
                'responsible_id' => $this->rcIds[$def['rc']],
                'grade' => $def['grade'],
            ]);

            // Attach interns via pivot
            foreach ($def['interns'] as $internIdx) {
                DB::table('internship_intern')->insert([
                    'internship_id' => $internship->id,
                    'intern_id' => $this->internRecords[$internIdx]->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->internshipRecords[] = $internship;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TASKS
    // ═══════════════════════════════════════════════════════════════════════

    private function seedTasks(): void
    {
        $titles = $this->taskTitles;
        $details = $this->taskDetails;

        foreach ($this->internshipRecords as $idx => $internship) {
            // Get the interns linked to this internship
            $internIds = DB::table('internship_intern')
                ->where('internship_id', $internship->id)
                ->pluck('intern_id');

            $taskCount = rand(3, 6);

            for ($t = 0; $t < $taskCount; $t++) {
                $titleIdx = ($idx * 6 + $t) % count($titles);
                $detailIdx = $t % count($details);

                // Pick an intern for this task
                $internId = $internIds[$t % $internIds->count()];
                $intern = Intern::find($internId);
                $assignedTo = $intern->user_id;

                // Determine status variety
                if ($internship->status === 'termine') {
                    $status = 'termine';
                } else {
                    $statuses = ['a_faire', 'en_cours', 'termine'];
                    $weights = $t < 2 ? [0, 1, 2] : [0, 0, 1, 1, 2]; // earlier tasks more likely done
                    $status = $statuses[$weights[array_rand($weights)]];
                }

                $dueDate = Carbon::parse($internship->start_date)
                    ->addDays(rand(7, (int)Carbon::parse($internship->start_date)->diffInDays($internship->end_date)));

                $weeklyComment = null;
                if ($status !== 'a_faire' && rand(1, 100) > 30) {
                    $comments = [
                        'Bon avancement cette semaine. J\'ai terminé la partie backend et commencé l\'intégration frontend.',
                        'Quelques difficultés rencontrées avec la configuration. Résolu après discussion avec l\'encadrant.',
                        'Travail sur les tests unitaires. Couverture à 75% pour le moment.',
                        'Progression normale. Les fonctionnalités principales sont implémentées.',
                        'Semaine productive. Module terminé et prêt pour la revue de code.',
                    ];
                    $weeklyComment = $comments[array_rand($comments)];
                }

                Task::create([
                    'internship_id' => $internship->id,
                    'assigned_by' => $internship->supervisor_id,
                    'assigned_to' => $assignedTo,
                    'title' => $titles[$titleIdx],
                    'details' => $details[$detailIdx],
                    'due_date' => $dueDate,
                    'status' => $status,
                    'weekly_comment' => $weeklyComment,
                ]);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  DAILY LOGS
    // ═══════════════════════════════════════════════════════════════════════

    private function seedDailyLogs(): void
    {
        $notes = $this->dailyNotes;

        foreach ($this->internRecords as $intern) {
            $start = Carbon::parse($intern->start_date);
            $end = min(today(), Carbon::parse($intern->end_date));

            if ($start->gt($end)) {
                continue;
            }

            $workingDays = CarbonPeriod::create($start, $end)
                ->filter(fn(Carbon $d) => $d->isWeekday());

            foreach ($workingDays as $day) {
                // 85% chance of being present
                $isPresent = rand(1, 100) <= 85;
                $note = null;

                if ($isPresent) {
                    // 90% chance of having a note when present
                    if (rand(1, 100) <= 90) {
                        $note = $notes[array_rand($notes)];
                    }
                }

                DailyLog::create([
                    'intern_id' => $intern->id,
                    'log_date' => $day->format('Y-m-d'),
                    'is_present' => $isPresent,
                    'daily_note' => $note,
                ]);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ABSENCES
    // ═══════════════════════════════════════════════════════════════════════

    private function seedAbsences(): void
    {
        $reasons = $this->absenceReasons;
        $recorders = array_merge($this->adminIds, $this->rcIds);

        foreach ($this->internRecords as $intern) {
            if ($intern->is_archived) {
                $absCount = rand(1, 3);
            } else {
                $absCount = rand(0, 5);
            }

            $start = Carbon::parse($intern->start_date);
            $end = min(today(), Carbon::parse($intern->end_date));
            if ($start->gte($end)) continue;

            $usedDates = [];
            for ($a = 0; $a < $absCount; $a++) {
                $diffDays = (int) $start->diffInDays($end);
                if ($diffDays <= 0) continue;

                $date = (clone $start)->addDays(rand(1, $diffDays));
                if ($date->isWeekend()) $date->addDays(2);
                $dateStr = $date->format('Y-m-d');

                if (in_array($dateStr, $usedDates)) continue;
                $usedDates[] = $dateStr;

                Absence::create([
                    'intern_id' => $intern->id,
                    'date_absence' => $dateStr,
                    'reason' => $reasons[array_rand($reasons)],
                    'justified' => rand(0, 1) === 1,
                    'recorded_by' => $recorders[array_rand($recorders)],
                ]);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  REQUESTS (all types & statuses)
    // ═══════════════════════════════════════════════════════════════════════

    private function seedRequests(): void
    {
        $now = now();

        foreach ($this->internRecords as $idx => $intern) {
            // ── Attestation requests (various workflow stages) ────────────
            if ($idx < 4) {
                // Fully completed attestation workflow
                $req = InternshipRequest::create([
                    'intern_id' => $intern->id,
                    'type' => 'attestation',
                    'message' => 'Je sollicite une attestation de stage pour mes dossiers universitaires. Merci de traiter ma demande dans les meilleurs délais.',
                    'status' => 'acceptee',
                    'workflow_status' => 'archived',
                    'processed_by' => $this->adminIds[0],
                    'supervisor_validated_by' => $this->encadrantIds[$idx % count($this->encadrantIds)],
                    'supervisor_validated_at' => $now->copy()->subDays(20),
                    'supervisor_grade' => rand(12, 18),
                    'rc_validated_by' => $this->rcIds[$idx % count($this->rcIds)],
                    'rc_validated_at' => $now->copy()->subDays(18),
                    'sent_to_rh_at' => $now->copy()->subDays(16),
                    'rh_processed_by' => $this->rhIds[0],
                    'rh_processed_at' => $now->copy()->subDays(14),
                    'attestation_printed_at' => $now->copy()->subDays(12),
                    'attestation_recovered_at' => $now->copy()->subDays(10),
                    'attestation_archived_at' => $now->copy()->subDays(8),
                ]);
            }

            if ($idx === 4 || $idx === 5) {
                // Attestation waiting for supervisor validation
                InternshipRequest::create([
                    'intern_id' => $intern->id,
                    'type' => 'attestation',
                    'message' => 'Demande d\'attestation de stage. Mon stage touche bientôt à sa fin et j\'en ai besoin pour mon dossier.',
                    'status' => 'en_attente',
                    'workflow_status' => 'supervisor_pending',
                    'processed_by' => null,
                ]);
            }

            if ($idx === 6) {
                // Attestation at RC validation stage
                InternshipRequest::create([
                    'intern_id' => $intern->id,
                    'type' => 'attestation',
                    'message' => 'Je voudrais obtenir mon attestation de stage svp.',
                    'status' => 'en_attente',
                    'workflow_status' => 'rc_pending',
                    'processed_by' => null,
                    'supervisor_validated_by' => $this->encadrantIds[3],
                    'supervisor_validated_at' => $now->copy()->subDays(5),
                    'supervisor_grade' => 15,
                ]);
            }

            if ($idx === 7) {
                // Attestation at RH stage
                InternshipRequest::create([
                    'intern_id' => $intern->id,
                    'type' => 'attestation',
                    'message' => 'Attestation de stage demandée pour compléter mon dossier de fin d\'études.',
                    'status' => 'en_attente',
                    'workflow_status' => 'sent_to_rh',
                    'processed_by' => null,
                    'supervisor_validated_by' => $this->encadrantIds[0],
                    'supervisor_validated_at' => $now->copy()->subDays(10),
                    'supervisor_grade' => 16,
                    'rc_validated_by' => $this->rcIds[1],
                    'rc_validated_at' => $now->copy()->subDays(7),
                    'sent_to_rh_at' => $now->copy()->subDays(5),
                ]);
            }

            // ── Prolongation requests ────────────────────────────────────
            if ($idx % 4 === 0) {
                $status = ['en_attente', 'acceptee', 'refusee'][rand(0, 2)];
                InternshipRequest::create([
                    'intern_id' => $intern->id,
                    'type' => 'prolongation',
                    'message' => 'Je souhaite prolonger mon stage d\'un mois supplémentaire pour terminer le projet en cours. Le travail restant nécessite plus de temps que prévu.',
                    'status' => $status,
                    'processed_by' => $status !== 'en_attente' ? $this->adminIds[array_rand($this->adminIds)] : null,
                ]);
            }

            // ── Absence requests ─────────────────────────────────────────
            if ($idx % 3 === 0) {
                $status = ['en_attente', 'acceptee'][rand(0, 1)];
                InternshipRequest::create([
                    'intern_id' => $intern->id,
                    'type' => 'absence',
                    'motif_absence' => $this->absenceReasons[array_rand($this->absenceReasons)],
                    'message' => 'Je demande une autorisation d\'absence pour raison personnelle. Je m\'engage à rattraper le travail manqué.',
                    'status' => $status,
                    'processed_by' => $status !== 'en_attente' ? $this->adminIds[0] : null,
                    'absence_generated_at' => $status === 'acceptee' ? $now->copy()->subDays(rand(1, 15)) : null,
                ]);
            }

            // ── Other requests ───────────────────────────────────────────
            if ($idx % 5 === 0) {
                InternshipRequest::create([
                    'intern_id' => $intern->id,
                    'type' => 'autre',
                    'message' => 'Je voudrais demander l\'accès au VPN de l\'entreprise pour pouvoir travailler à distance certains jours. Cela me permettrait d\'être plus productif.',
                    'status' => 'acceptee',
                    'processed_by' => $this->adminIds[0],
                ]);
            }

            // ── Retard attestation ───────────────────────────────────────
            if ($idx === 9) {
                InternshipRequest::create([
                    'intern_id' => $intern->id,
                    'type' => 'retard_attestation',
                    'message' => 'Mon attestation est en retard. J\'ai besoin de la récupérer au plus vite pour mon inscription.',
                    'status' => 'en_attente',
                    'workflow_status' => 'supervisor_pending',
                    'processed_by' => null,
                ]);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  MESSAGES (use DB::table to avoid triggering email events)
    // ═══════════════════════════════════════════════════════════════════════

    private function seedMessages(): void
    {
        $subjects = $this->messageSubjects;
        $bodies = $this->messageBodies;
        $now = now();

        $conversations = [
            // Admin ↔ Encadrants
            [$this->adminIds[0], $this->encadrantIds[0], 0, 0],
            [$this->encadrantIds[0], $this->adminIds[0], 1, 1],
            [$this->adminIds[0], $this->encadrantIds[1], 2, 2],
            [$this->encadrantIds[1], $this->adminIds[0], 3, 3],

            // Encadrants ↔ Stagiaires
            [$this->encadrantIds[0], $this->stagiaireUserIds[0], 4, 4],
            [$this->stagiaireUserIds[0], $this->encadrantIds[0], 5, 5],
            [$this->encadrantIds[0], $this->stagiaireUserIds[1], 0, 6],
            [$this->encadrantIds[1], $this->stagiaireUserIds[2], 1, 7],
            [$this->stagiaireUserIds[2], $this->encadrantIds[1], 6, 8],
            [$this->encadrantIds[2], $this->stagiaireUserIds[3], 2, 9],
            [$this->stagiaireUserIds[3], $this->encadrantIds[2], 7, 10],
            [$this->encadrantIds[3], $this->stagiaireUserIds[6], 3, 11],

            // Admin ↔ Stagiaires
            [$this->adminIds[0], $this->stagiaireUserIds[0], 8, 0],
            [$this->adminIds[0], $this->stagiaireUserIds[4], 9, 1],
            [$this->stagiaireUserIds[5], $this->adminIds[0], 6, 2],

            // RC ↔ Encadrants
            [$this->rcIds[0], $this->encadrantIds[0], 4, 3],
            [$this->encadrantIds[0], $this->rcIds[0], 5, 4],
            [$this->rcIds[1], $this->encadrantIds[2], 7, 5],

            // RH ↔ Admin
            [$this->rhIds[0], $this->adminIds[0], 8, 6],
            [$this->adminIds[0], $this->rhIds[0], 9, 7],

            // RC ↔ Stagiaires
            [$this->rcIds[0], $this->stagiaireUserIds[0], 3, 8],
            [$this->rcIds[1], $this->stagiaireUserIds[5], 2, 9],

            // More encadrant-stagiaire exchanges
            [$this->encadrantIds[4], $this->stagiaireUserIds[8], 0, 10],
            [$this->stagiaireUserIds[8], $this->encadrantIds[4], 4, 11],
            [$this->encadrantIds[0], $this->stagiaireUserIds[0], 1, 0],
            [$this->stagiaireUserIds[1], $this->encadrantIds[0], 5, 1],
        ];

        foreach ($conversations as $i => $conv) {
            DB::table('messages')->insert([
                'sender_id' => $conv[0],
                'receiver_id' => $conv[1],
                'subject' => $subjects[$conv[2]],
                'body' => $bodies[$conv[3]],
                'is_read' => $i < 18, // Recent messages unread
                'created_at' => $now->copy()->subDays(count($conversations) - $i)->subHours(rand(1, 12)),
                'updated_at' => $now->copy()->subDays(count($conversations) - $i)->subHours(rand(1, 12)),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  WEEKLY REPORTS (simulated AI reports)
    // ═══════════════════════════════════════════════════════════════════════

    private function seedWeeklyReports(): void
    {
        $sentiments = ['positive', 'positive', 'positive', 'neutral', 'neutral', 'negative'];
        $ratings = [
            'Excellent travail cette semaine',
            'Bonne progression générale',
            'Travail satisfaisant',
            'Performance moyenne, à améliorer',
            'Résultats encourageants',
            'Besoin d\'un meilleur suivi',
        ];

        foreach ($this->internRecords as $idx => $intern) {
            $start = Carbon::parse($intern->start_date);
            $end = min(today(), Carbon::parse($intern->end_date));

            if ($start->gte($end)) continue;

            // Generate reports for the last few weeks
            $weekStart = $start->copy()->startOfWeek(Carbon::MONDAY);
            $weekNum = 0;

            while ($weekStart->lt($end) && $weekNum < 8) {
                $weekEnd = $weekStart->copy()->endOfWeek(Carbon::FRIDAY);
                if ($weekEnd->gt($end)) $weekEnd = $end->copy();

                // Vary scores per intern personality
                $baseScore = match (true) {
                    $idx < 4 => rand(72, 95),   // High performers
                    $idx < 8 => rand(50, 78),   // Medium performers
                    $idx < 12 => rand(35, 65),  // Lower performers
                    default => rand(55, 85),     // Archived - mixed
                };

                $engagement = min(10, max(1, (int) round($baseScore / 10)));
                $completion = min(100, max(0, $baseScore + rand(-15, 15)));
                $sentiment = $baseScore >= 70 ? 'positive' : ($baseScore >= 45 ? 'neutral' : 'negative');

                $reportJson = [
                    'summary' => 'Rapport hebdomadaire généré automatiquement par l\'agent IA.',
                    'key_accomplishments' => [
                        'Avancement sur les tâches assignées',
                        'Participation aux réunions d\'équipe',
                        'Documentation du travail réalisé',
                    ],
                    'areas_for_improvement' => $baseScore < 60 ? [
                        'Améliorer la régularité de présence',
                        'Accélérer le rythme de progression sur les tâches',
                    ] : [
                        'Continuer sur cette lancée',
                    ],
                    'recommendations' => 'Suivi régulier avec l\'encadrant recommandé.',
                ];

                $generatedBy = $this->adminIds[0];

                // Find the internship's supervisor for this intern
                $internshipForIntern = DB::table('internship_intern')
                    ->where('intern_id', $intern->id)
                    ->first();
                if ($internshipForIntern) {
                    $sup = Internship::find($internshipForIntern->internship_id);
                    if ($sup) $generatedBy = $sup->supervisor_id ?? $generatedBy;
                }

                WeeklyReport::updateOrCreate(
                    ['intern_id' => $intern->id, 'week_start' => $weekStart->format('Y-m-d')],
                    [
                        'generated_by' => $generatedBy,
                        'week_end' => $weekEnd->format('Y-m-d'),
                        'week_score' => $baseScore,
                        'engagement_score' => $engagement,
                        'task_completion_rate' => $completion,
                        'overall_sentiment' => $sentiment,
                        'overall_rating' => $ratings[array_rand($ratings)],
                        'report_json' => $reportJson,
                    ]
                );

                $weekStart->addWeek();
                $weekNum++;
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  NOTIFICATIONS (use DB::table to avoid triggering email events)
    // ═══════════════════════════════════════════════════════════════════════

    private function seedNotifications(): void
    {
        $now = now();

        $notifs = [
            // For admins
            ['user' => $this->adminIds[0], 'type' => 'new_request',       'icon' => 'bi-file-earmark-text', 'color' => 'text-primary',  'title' => 'Nouvelle demande d\'attestation',      'body' => 'Salma Benhaddou a soumis une demande d\'attestation de stage.',     'url' => '/requests',      'read' => false],
            ['user' => $this->adminIds[0], 'type' => 'new_request',       'icon' => 'bi-file-earmark-text', 'color' => 'text-primary',  'title' => 'Nouvelle demande de prolongation',     'body' => 'Youssef Bouazza demande une prolongation de son stage.',           'url' => '/requests',      'read' => true],
            ['user' => $this->adminIds[0], 'type' => 'intern_assigned',   'icon' => 'bi-person-plus',       'color' => 'text-success',  'title' => 'Nouveau stagiaire inscrit',            'body' => 'Omar El Ouazzani a été ajouté dans le système.',                   'url' => '/interns',       'read' => true],
            ['user' => $this->adminIds[0], 'type' => 'password_reset',    'icon' => 'bi-key',               'color' => 'text-warning',  'title' => 'Demande de réinitialisation',          'body' => 'Amine Tahiri a demandé un changement de mot de passe.',            'url' => '/password-reset-requests', 'read' => false],
            ['user' => $this->adminIds[1], 'type' => 'message_received',  'icon' => 'bi-envelope',          'color' => 'text-info',     'title' => 'Nouveau message',                      'body' => 'Vous avez reçu un message de Karim Berrada.',                      'url' => '/messages',      'read' => false],

            // For encadrants
            ['user' => $this->encadrantIds[0], 'type' => 'task_completed',    'icon' => 'bi-check-circle',  'color' => 'text-success',  'title' => 'Tâche terminée',                       'body' => 'Youssef Bouazza a marqué la tâche "Analyser les besoins" comme terminée.', 'url' => '/tasks', 'read' => false],
            ['user' => $this->encadrantIds[0], 'type' => 'intern_assigned',   'icon' => 'bi-person-plus',   'color' => 'text-success',  'title' => 'Stagiaire assigné',                    'body' => 'Fatima Zahra El Idrissi a été assignée à votre encadrement.',       'url' => '/interns',       'read' => true],
            ['user' => $this->encadrantIds[0], 'type' => 'attestation_review','icon' => 'bi-clipboard-check','color' => 'text-warning',  'title' => 'Attestation à valider',                'body' => 'Hamza Zouaki demande une validation pour son attestation.',         'url' => '/requests',      'read' => false],
            ['user' => $this->encadrantIds[1], 'type' => 'message_received',  'icon' => 'bi-envelope',      'color' => 'text-info',     'title' => 'Nouveau message',                      'body' => 'Vous avez reçu un message de Amine Tahiri.',                       'url' => '/messages',      'read' => true],
            ['user' => $this->encadrantIds[2], 'type' => 'deadline_reminder', 'icon' => 'bi-clock',         'color' => 'text-danger',   'title' => 'Échéance proche',                      'body' => 'La tâche "Rédiger le cahier des charges" arrive à échéance dans 2 jours.', 'url' => '/tasks', 'read' => false],
            ['user' => $this->encadrantIds[3], 'type' => 'task_completed',    'icon' => 'bi-check-circle',  'color' => 'text-success',  'title' => 'Tâche terminée',                       'body' => 'Mehdi Lahlou a terminé le développement du module d\'authentification.', 'url' => '/tasks', 'read' => false],

            // For stagiaires
            ['user' => $this->stagiaireUserIds[0], 'type' => 'task_assigned',     'icon' => 'bi-list-task',       'color' => 'text-primary',  'title' => 'Nouvelle tâche assignée',              'body' => 'Karim Berrada vous a assigné la tâche "Développer l\'API REST".',   'url' => '/tasks',      'read' => false],
            ['user' => $this->stagiaireUserIds[0], 'type' => 'message_received',  'icon' => 'bi-envelope',        'color' => 'text-info',     'title' => 'Nouveau message',                      'body' => 'Vous avez reçu un message de l\'administrateur.',                   'url' => '/messages',   'read' => true],
            ['user' => $this->stagiaireUserIds[1], 'type' => 'deadline_reminder', 'icon' => 'bi-clock',           'color' => 'text-danger',   'title' => 'Échéance demain',                      'body' => 'La tâche "Concevoir le schéma de base de données" est due demain.', 'url' => '/tasks',      'read' => false],
            ['user' => $this->stagiaireUserIds[2], 'type' => 'request_approved',  'icon' => 'bi-check2-circle',   'color' => 'text-success',  'title' => 'Demande acceptée',                     'body' => 'Votre demande d\'absence a été acceptée par l\'administrateur.',    'url' => '/requests',   'read' => true],
            ['user' => $this->stagiaireUserIds[3], 'type' => 'task_assigned',     'icon' => 'bi-list-task',       'color' => 'text-primary',  'title' => 'Nouvelle tâche',                       'body' => 'Soufiane Kettani vous a assigné une nouvelle tâche de développement.', 'url' => '/tasks', 'read' => false],
            ['user' => $this->stagiaireUserIds[4], 'type' => 'message_received',  'icon' => 'bi-envelope',        'color' => 'text-info',     'title' => 'Message reçu',                         'body' => 'Vous avez reçu un nouveau message concernant votre stage.',         'url' => '/messages',   'read' => false],
            ['user' => $this->stagiaireUserIds[5], 'type' => 'request_approved',  'icon' => 'bi-check2-circle',   'color' => 'text-success',  'title' => 'Prolongation acceptée',                'body' => 'Votre demande de prolongation de stage a été acceptée.',            'url' => '/requests',   'read' => true],
            ['user' => $this->stagiaireUserIds[6], 'type' => 'task_assigned',     'icon' => 'bi-list-task',       'color' => 'text-primary',  'title' => 'Tâche assignée',                       'body' => 'Une nouvelle tâche vous a été assignée par votre encadrant.',       'url' => '/tasks',      'read' => false],

            // For RC
            ['user' => $this->rcIds[0], 'type' => 'attestation_review', 'icon' => 'bi-clipboard-check', 'color' => 'text-warning', 'title' => 'Attestation à valider', 'body' => 'L\'encadrant a validé l\'attestation de Mehdi Lahlou. En attente de votre validation.', 'url' => '/requests', 'read' => false],
            ['user' => $this->rcIds[1], 'type' => 'intern_assigned',    'icon' => 'bi-person-plus',      'color' => 'text-success', 'title' => 'Nouveau stagiaire',     'body' => 'Un nouveau stagiaire a été assigné à votre compétence.',                                'url' => '/interns',  'read' => true],

            // For RH
            ['user' => $this->rhIds[0], 'type' => 'attestation_ready', 'icon' => 'bi-printer',       'color' => 'text-primary', 'title' => 'Attestation à imprimer',   'body' => 'L\'attestation de Hajar Chraibi est prête pour impression.',         'url' => '/requests', 'read' => false],
            ['user' => $this->rhIds[0], 'type' => 'message_received',  'icon' => 'bi-envelope',      'color' => 'text-info',    'title' => 'Nouveau message',          'body' => 'L\'administrateur vous a envoyé un message.',                       'url' => '/messages', 'read' => true],
        ];

        foreach ($notifs as $i => $n) {
            DB::table('notifications')->insert([
                'user_id' => $n['user'],
                'type' => $n['type'],
                'icon' => $n['icon'],
                'color' => $n['color'],
                'title' => $n['title'],
                'body' => $n['body'],
                'url' => $n['url'],
                'is_read' => $n['read'],
                'created_at' => $now->copy()->subHours(count($notifs) - $i + rand(0, 5)),
                'updated_at' => $now->copy()->subHours(count($notifs) - $i + rand(0, 5)),
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  PASSWORD RESET REQUESTS
    // ═══════════════════════════════════════════════════════════════════════

    private function seedPasswordResetRequests(): void
    {
        $now = now();

        // One pending request
        DB::table('password_reset_requests')->insert([
            'user_id' => $this->stagiaireUserIds[2],
            'pending_password_hash' => Hash::make('newpassword123'),
            'status' => 'en_attente',
            'processed_by' => null,
            'processed_at' => null,
            'created_at' => $now->copy()->subDays(1),
            'updated_at' => $now->copy()->subDays(1),
        ]);

        // One accepted request
        DB::table('password_reset_requests')->insert([
            'user_id' => $this->stagiaireUserIds[5],
            'pending_password_hash' => Hash::make('password123'),
            'status' => 'acceptee',
            'processed_by' => $this->adminIds[0],
            'processed_at' => $now->copy()->subDays(5),
            'created_at' => $now->copy()->subDays(7),
            'updated_at' => $now->copy()->subDays(5),
        ]);

        // One rejected request
        DB::table('password_reset_requests')->insert([
            'user_id' => $this->stagiaireUserIds[8],
            'pending_password_hash' => Hash::make('test1234'),
            'status' => 'refusee',
            'processed_by' => $this->adminIds[0],
            'processed_at' => $now->copy()->subDays(3),
            'created_at' => $now->copy()->subDays(4),
            'updated_at' => $now->copy()->subDays(3),
        ]);
    }
}
