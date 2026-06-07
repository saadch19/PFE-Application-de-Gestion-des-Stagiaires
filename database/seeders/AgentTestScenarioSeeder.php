<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\DailyLog;
use App\Models\Intern;
use App\Models\Internship;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * AgentTestScenarioSeeder
 *
 * Creates 4 interns linked to encadrant@internships.local,
 * each representing a distinct scenario so you can test
 * every kind of AI-generated report:
 *
 * ① Ahmed Benali    — Excellent (all tasks done, daily logs full, positive)
 * ② Sara Alaoui     — Average   (mixed completion, some absences, neutral)
 * ③ Youssef Tazi    — Struggling (overdue tasks, many absences, negative logs)
 * ④ Lina Chraibi    — New intern (just started, minimal data)
 *
 * All accounts use password: password123
 */
class AgentTestScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $stagiaireRoleId = Role::query()->where('name', 'Stagiaire')->value('id');

        $encadrant   = User::query()->where('email', 'encadrant@internships.local')->first();
        $responsable = User::query()->where('email', 'responsable@internships.local')->first();

        // Week we'll seed data for — current week Mon→Sun
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // ─────────────────────────────────────────────────────────
        // ① AHMED BENALI — Excellent intern
        // ─────────────────────────────────────────────────────────
        $ahmed = $this->makeIntern(
            stagiaireRoleId: $stagiaireRoleId,
            fullName:   'Ahmed Benali',
            email:      'ahmed.benali@internships.local',
            cin:        'AG001001',
            school:     'ENSIAS Rabat',
            specialty:  'Génie Logiciel',
            phone:      '0601010101',
            startDate:  now()->subDays(25),
            endDate:    now()->addDays(65),
        );

        $ahmedInternship = $this->makeInternship(
            title:       'Développement d\'une API REST – Excellent',
            description: 'Création d\'une API REST complète pour la gestion des stagiaires.',
            department:  'Informatique',
            startDate:   now()->subDays(25),
            endDate:     now()->addDays(65),
            status:      'en_cours',
            supervisor:  $encadrant,
            responsible: $responsable,
            intern:      $ahmed,
        );

        // Tasks — all done or nearly done
        $this->makeTask($ahmedInternship, $encadrant, $ahmed->user, 'Mise en place de l\'environnement', now()->subDays(20), 'termine',
            'Configuration terminée sans problème. Environnement Docker opérationnel.');
        $this->makeTask($ahmedInternship, $encadrant, $ahmed->user, 'Modélisation de la base de données', now()->subDays(12), 'termine',
            'Schéma validé par l\'encadrant. Relations bien normalisées.');
        $this->makeTask($ahmedInternship, $encadrant, $ahmed->user, 'Implémentation des endpoints CRUD', now()->subDays(4), 'termine',
            'Tous les endpoints testés avec Postman. Documentation Swagger générée.');
        $this->makeTask($ahmedInternship, $encadrant, $ahmed->user, 'Tests unitaires et d\'intégration', now()->addDays(3), 'en_cours',
            'En cours, 80% de couverture atteinte. Quelques cas limites à traiter.');

        // Daily logs — present every day, positive notes
        $dailyNotes = [
            'Finalisé les tests des endpoints GET/POST. Tout fonctionne comme prévu.',
            'Implémenté les middlewares d\'authentification JWT. Bien avancé.',
            'Réunion avec l\'encadrant très productive. Nouveau sprint planifié.',
            'Commencé les tests unitaires avec PHPUnit. 60% de couverture.',
            'Bonne journée ! Atteint 80% de couverture, documentation à jour.',
        ];
        $this->makeDailyLogs($ahmed, $weekStart, $dailyNotes, allPresent: true);

        // Messages — positive, professional
        $this->makeMessage($ahmed->user, $encadrant,
            'Avancement sprint 3', 'Bonjour, je suis heureux de vous informer que j\'ai terminé tous les endpoints CRUD. Les tests passent à 100%. Je commence les tests d\'intégration demain.');
        $this->makeMessage($encadrant, $ahmed->user,
            'Re: Avancement sprint 3', 'Excellent travail Ahmed ! Continuez sur cette lancée. N\'hésitez pas si vous avez des questions sur les tests d\'intégration.');

        // ─────────────────────────────────────────────────────────
        // ② SARA ALAOUI — Average intern
        // ─────────────────────────────────────────────────────────
        $sara = $this->makeIntern(
            stagiaireRoleId: $stagiaireRoleId,
            fullName:   'Sara Alaoui',
            email:      'sara.alaoui.agent@internships.local',
            cin:        'SA002002',
            school:     'ENSA Casablanca',
            specialty:  'Data Science',
            phone:      '0602020202',
            startDate:  now()->subDays(20),
            endDate:    now()->addDays(70),
        );

        $saraInternship = $this->makeInternship(
            title:       'Analyse de données RH – Moyen',
            description: 'Extraction et analyse des données RH pour tableaux de bord.',
            department:  'Ressources Humaines',
            startDate:   now()->subDays(20),
            endDate:     now()->addDays(70),
            status:      'en_cours',
            supervisor:  $encadrant,
            responsible: $responsable,
            intern:      $sara,
        );

        // Tasks — mixed statuses
        $this->makeTask($saraInternship, $encadrant, $sara->user, 'Collecte et nettoyage des données', now()->subDays(10), 'termine',
            'Données collectées mais nettoyage a pris plus de temps que prévu.');
        $this->makeTask($saraInternship, $encadrant, $sara->user, 'Analyse exploratoire (EDA)', now()->subDays(2), 'en_cours',
            'J\'ai du mal avec certaines librairies Python, j\'avance doucement.');
        $this->makeTask($saraInternship, $encadrant, $sara->user, 'Création du tableau de bord Power BI', now()->addDays(8), 'a_faire',
            null);
        $this->makeTask($saraInternship, $encadrant, $sara->user, 'Rapport final d\'analyse', now()->addDays(15), 'a_faire',
            null);

        // Absences — 2 days absent
        Absence::updateOrCreate(
            ['intern_id' => $sara->id, 'date_absence' => $weekStart->copy()->addDay()->toDateString()],
            ['reason' => 'Rendez-vous médical', 'justified' => true, 'recorded_by' => $encadrant->id]
        );
        Absence::updateOrCreate(
            ['intern_id' => $sara->id, 'date_absence' => $weekStart->copy()->addDays(3)->toDateString()],
            ['reason' => 'Problème de transport', 'justified' => false, 'recorded_by' => $encadrant->id]
        );

        // Daily logs — absent 2 days, moderate notes
        $saraDailyNotes = [
            'Continué l\'analyse exploratoire. Quelques corrélations intéressantes trouvées.',
            null, // absent
            'Essayé de comprendre Pandas pour le pivot mais c\'est compliqué. J\'ai regardé des tutoriels.',
            null, // absent
            'Repris le travail sur l\'EDA. Un peu découragée par les obstacles techniques.',
        ];
        $saraPresence = [true, false, true, false, true];
        $this->makeDailyLogsCustom($sara, $weekStart, $saraDailyNotes, $saraPresence);

        // Messages — neutral tone, some uncertainty
        $this->makeMessage($sara->user, $encadrant,
            'Question sur Python Pandas', 'Bonjour, j\'ai du mal avec les opérations de pivot dans Pandas. Pourriez-vous m\'orienter vers des ressources ou m\'accorder 30 minutes pour en discuter ?');
        $this->makeMessage($encadrant, $sara->user,
            'Re: Question sur Python Pandas', 'Bonjour Sara, bien sûr. Je vous envoie quelques liens utiles. On peut se voir vendredi à 14h si ça vous convient.');

        // ─────────────────────────────────────────────────────────
        // ③ YOUSSEF TAZI — Struggling intern
        // ─────────────────────────────────────────────────────────
        $youssef = $this->makeIntern(
            stagiaireRoleId: $stagiaireRoleId,
            fullName:   'Youssef Tazi',
            email:      'youssef.tazi.agent@internships.local',
            cin:        'YT003003',
            school:     'ENSA Fès',
            specialty:  'Réseaux & Télécommunications',
            phone:      '0603030303',
            startDate:  now()->subDays(30),
            endDate:    now()->addDays(60),
        );

        $youssefInternship = $this->makeInternship(
            title:       'Migration infrastructure réseau – En difficulté',
            description: 'Migration des serveurs de développement vers une nouvelle infrastructure réseau.',
            department:  'Infrastructure',
            startDate:   now()->subDays(30),
            endDate:     now()->addDays(60),
            status:      'en_cours',
            supervisor:  $encadrant,
            responsible: $responsable,
            intern:      $youssef,
        );

        // Tasks — mostly overdue, nothing done
        $this->makeTask($youssefInternship, $encadrant, $youssef->user, 'Audit de l\'infrastructure existante', now()->subDays(18), 'a_faire',
            'Je ne sais pas par où commencer. L\'environnement est trop complexe.');
        $this->makeTask($youssefInternship, $encadrant, $youssef->user, 'Plan de migration réseau', now()->subDays(8), 'a_faire',
            'Pas encore commencé. Difficultés à accéder à la documentation.');
        $this->makeTask($youssefInternship, $encadrant, $youssef->user, 'Configuration des VLANs', now()->subDays(2), 'en_cours',
            'J\'essaie mais les accès aux équipements sont limités. Je suis bloqué.');
        $this->makeTask($youssefInternship, $encadrant, $youssef->user, 'Tests de connectivité', now()->addDays(5), 'a_faire',
            null);

        // Many absences
        foreach ([14, 11, 8, 5, 2] as $daysAgo) {
            Absence::updateOrCreate(
                ['intern_id' => $youssef->id, 'date_absence' => now()->subDays($daysAgo)->toDateString()],
                ['reason' => $daysAgo % 3 === 0 ? 'Absence non justifiée' : 'Problème personnel', 'justified' => false, 'recorded_by' => $encadrant->id]
            );
        }

        // Daily logs — mostly absent or very short/negative notes
        $youssefDailyNotes = [
            'Je ne comprends rien à la topologie réseau. Personne pour m\'aider.',
            null, // absent
            'Essayé de lire la doc mais c\'est en anglais technique et je n\'y arrive pas.',
            null, // absent
            'Je pense que ce stage n\'est pas fait pour moi. Très découragé.',
        ];
        $youssefPresence = [true, false, true, false, true];
        $this->makeDailyLogsCustom($youssef, $weekStart, $youssefDailyNotes, $youssefPresence);

        // Messages — frustrated, negative
        $this->makeMessage($youssef->user, $encadrant,
            'Problème accès équipements', 'Bonjour, je n\'arrive toujours pas à accéder aux switchs pour configurer les VLANs. Ça fait 3 jours que je suis bloqué au même point. J\'ai l\'impression de ne servir à rien.');
        $this->makeMessage($youssef->user, $encadrant,
            'Besoin d\'aide urgent', 'Je veux vraiment réussir ce stage mais je n\'ai aucun support. Pouvez-vous m\'allouer du temps cette semaine ? Je suis sur le point d\'abandonner.');

        // ─────────────────────────────────────────────────────────
        // ④ LINA CHRAIBI — New intern (just started, minimal data)
        // ─────────────────────────────────────────────────────────
        $lina = $this->makeIntern(
            stagiaireRoleId: $stagiaireRoleId,
            fullName:   'Lina Chraibi',
            email:      'lina.chraibi.agent@internships.local',
            cin:        'LC004004',
            school:     'FST Fès',
            specialty:  'Informatique & Systèmes',
            phone:      '0604040404',
            startDate:  now()->subDays(3),
            endDate:    now()->addDays(87),
        );

        $linaInternship = $this->makeInternship(
            title:       'Application web de gestion documentaire – Débutante',
            description: 'Développement d\'une application web pour la gestion des documents RH.',
            department:  'Ressources Humaines',
            startDate:   now()->subDays(3),
            endDate:     now()->addDays(87),
            status:      'en_cours',
            supervisor:  $encadrant,
            responsible: $responsable,
            intern:      $lina,
        );

        // Tasks — just one assigned, not started
        $this->makeTask($linaInternship, $encadrant, $lina->user, 'Prise en main de l\'environnement', now()->addDays(7), 'a_faire',
            null);
        $this->makeTask($linaInternship, $encadrant, $lina->user, 'Analyse des besoins', now()->addDays(14), 'a_faire',
            null);

        // Only a couple of daily logs — just started
        $linaDailyNotes = [
            null,
            null,
            null,
            'Premier jour officiel. Réunion d\'accueil avec l\'équipe. Beaucoup à apprendre !',
            'Exploration du projet et de la base de code. Motivée et curieuse.',
        ];
        $linaPresence = [false, false, false, true, true];
        $this->makeDailyLogsCustom($lina, $weekStart, $linaDailyNotes, $linaPresence);

        $this->command->info('✅  AgentTestScenarioSeeder — 4 test interns created:');
        $this->command->info('   ① ahmed.benali@internships.local     → Excellent');
        $this->command->info('   ② sara.alaoui.agent@internships.local → Moyen');
        $this->command->info('   ③ youssef.tazi.agent@internships.local → En difficulté');
        $this->command->info('   ④ lina.chraibi.agent@internships.local → Nouveau stagiaire');
        $this->command->info('   Password for all: password123');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function makeIntern(
        int $stagiaireRoleId,
        string $fullName,
        string $email,
        string $cin,
        string $school,
        string $specialty,
        string $phone,
        Carbon $startDate,
        Carbon $endDate,
    ): Intern {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'full_name'     => $fullName,
                'password_hash' => Hash::make('password123'),
                'role_id'       => $stagiaireRoleId,
                'is_active'     => true,
            ]
        );

        return Intern::updateOrCreate(
            ['user_id' => $user->id],
            [
                'cin'         => $cin,
                'school'      => $school,
                'specialty'   => $specialty,
                'phone'       => $phone,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'is_archived' => false,
            ]
        );
    }

    private function makeInternship(
        string $title,
        string $description,
        string $department,
        Carbon $startDate,
        Carbon $endDate,
        string $status,
        User $supervisor,
        User $responsible,
        Intern $intern,
    ): Internship {
        $internship = Internship::updateOrCreate(
            ['title' => $title],
            [
                'description'   => $description,
                'department'    => $department,
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'status'        => $status,
                'supervisor_id' => $supervisor->id,
                'responsible_id'=> $responsible->id,
            ]
        );

        $internship->interns()->syncWithoutDetaching([$intern->id]);
        return $internship;
    }

    private function makeTask(
        Internship $internship,
        User $assignedBy,
        User $assignedTo,
        string $title,
        Carbon $dueDate,
        string $status,
        ?string $weeklyComment = null,
    ): Task {
        return Task::updateOrCreate(
            ['internship_id' => $internship->id, 'title' => $title],
            [
                'assigned_by'    => $assignedBy->id,
                'assigned_to'    => $assignedTo->id,
                'details'        => "Tâche de test — scénario agent IA.",
                'due_date'       => $dueDate,
                'status'         => $status,
                'weekly_comment' => $weeklyComment,
            ]
        );
    }

    /** All 5 days present with the given notes. */
    private function makeDailyLogs(Intern $intern, Carbon $weekStart, array $notes, bool $allPresent = true): void
    {
        foreach ($notes as $i => $note) {
            $date = $weekStart->copy()->addDays($i)->toDateString();
            DailyLog::updateOrCreate(
                ['intern_id' => $intern->id, 'log_date' => $date],
                ['is_present' => $allPresent, 'daily_note' => $note]
            );
        }
    }

    /** Custom presence per day. */
    private function makeDailyLogsCustom(Intern $intern, Carbon $weekStart, array $notes, array $presence): void
    {
        foreach ($notes as $i => $note) {
            $date = $weekStart->copy()->addDays($i)->toDateString();
            DailyLog::updateOrCreate(
                ['intern_id' => $intern->id, 'log_date' => $date],
                ['is_present' => $presence[$i] ?? false, 'daily_note' => $note]
            );
        }
    }

    private function makeMessage(User $from, User $to, string $subject, string $body): Message
    {
        return Message::create([
            'sender_id'   => $from->id,
            'receiver_id' => $to->id,
            'subject'     => $subject,
            'body'        => $body,
        ]);
    }
}
