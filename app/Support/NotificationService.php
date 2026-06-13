<?php

namespace App\Support;

use App\Models\Internship;
use App\Models\InternshipRequest;
use App\Models\Message;
use App\Models\Notification;
use App\Models\PasswordResetRequest;
use App\Models\Task;
use App\Models\User;

class NotificationService
{
    // ── 1. Task assigned to intern ─────────────────────────────────────────

    public static function taskAssigned(Task $task): void
    {
        $task->loadMissing(['assignedTo', 'assignedBy', 'internship']);

        $recipient = $task->assignedTo;
        if (! $recipient) {
            return;
        }

        $assigner = $task->assignedBy?->full_name ?? 'Un responsable';
        $url = route('tasks.index');

        Notification::notify($recipient, [
            'type'  => 'task_assigned',
            'icon'  => 'bi-list-task',
            'color' => 'text-primary',
            'title' => 'Nouvelle tâche assignée',
            'body'  => "{$assigner} vous a assigné la tâche : « {$task->title} ».",
            'url'   => $url,
        ]);
    }

    // ── 2. Task marked as "Terminé" — notify encadrant + admin ───────────

    public static function taskCompleted(Task $task): void
    {
        $task->loadMissing(['assignedTo', 'assignedBy', 'internship.supervisor']);

        $intern = $task->assignedTo;
        $internName = $intern?->full_name ?? 'Un stagiaire';

        // Notify the encadrant (assigned_by)
        $encadrant = $task->assignedBy;
        if ($encadrant && $encadrant->hasRole('Encadrant')) {
            Notification::notify($encadrant, [
                'type'  => 'task_completed',
                'icon'  => 'bi-check-circle',
                'color' => 'text-success',
                'title' => 'Tâche terminée',
                'body'  => "{$internName} a marqué la tâche « {$task->title} » comme terminée.",
                'url'   => route('tasks.index'),
            ]);
        }

        // Also notify admins
        User::query()
            ->whereHas('role', fn ($q) => $q->where('name', 'Administrateur'))
            ->get()
            ->each(function (User $admin) use ($task, $internName): void {
                Notification::notify($admin, [
                    'type'  => 'task_completed',
                    'icon'  => 'bi-check-circle',
                    'color' => 'text-success',
                    'title' => 'Tâche terminée',
                    'body'  => "{$internName} a marqué la tâche « {$task->title} » comme terminée.",
                    'url'   => route('tasks.index'),
                ]);
            });
    }

    // ── 3. Task deadline approaching ───────────────────────────────────────

    public static function taskDeadlineApproaching(Task $task): void
    {
        $task->loadMissing('assignedTo');

        $recipient = $task->assignedTo;
        if (! $recipient) {
            return;
        }

        $daysLeft = (int) now()->startOfDay()->diffInDays($task->due_date, false);
        $daysText = $daysLeft === 1 ? 'demain' : "dans {$daysLeft} jours";

        Notification::notify($recipient, [
            'type'  => 'task_deadline',
            'icon'  => 'bi-alarm',
            'color' => 'text-warning',
            'title' => 'Échéance de tâche proche',
            'body'  => "La tâche « {$task->title} » est due {$daysText}.",
            'url'   => route('tasks.index'),
        ]);
    }

    // ── 4. Internship request submitted ───────────────────────────────────

    public static function requestSubmitted(InternshipRequest $req): void
    {
        $req->loadMissing(['intern.user', 'intern.internships.supervisor']);

        $internName = $req->intern?->user?->full_name ?? 'Un stagiaire';
        $typeLabel  = self::requestTypeLabel($req->type);
        $url = route('requests.index');

        // Notify all admins
        User::query()
            ->whereHas('role', fn ($q) => $q->where('name', 'Administrateur'))
            ->get()
            ->each(fn (User $admin) => Notification::notify($admin, [
                'type'  => 'request_submitted',
                'icon'  => 'bi-question-circle',
                'color' => 'text-info',
                'title' => 'Nouvelle demande',
                'body'  => "{$internName} a soumis une demande de type « {$typeLabel} ».",
                'url'   => $url,
            ]));

        // Notify encadrant(s)
        $req->intern?->internships->each(function (Internship $internship) use ($internName, $typeLabel, $url): void {
            if ($internship->supervisor) {
                Notification::notify($internship->supervisor, [
                    'type'  => 'request_submitted',
                    'icon'  => 'bi-question-circle',
                    'color' => 'text-info',
                    'title' => 'Nouvelle demande',
                    'body'  => "{$internName} a soumis une demande de type « {$typeLabel} ».",
                    'url'   => $url,
                ]);
            }
        });
    }

    // ── 5. Request accepted or refused ────────────────────────────────────

    public static function requestProcessed(InternshipRequest $req): void
    {
        $req->loadMissing(['intern.user']);

        $recipient = $req->intern?->user;
        if (! $recipient) {
            return;
        }

        $statusLabel = $req->status === 'acceptee' ? 'acceptée' : 'refusée';
        $typeLabel   = self::requestTypeLabel($req->type);

        Notification::notify($recipient, [
            'type'  => 'request_processed',
            'icon'  => $req->status === 'acceptee' ? 'bi-check2-circle' : 'bi-x-circle',
            'color' => $req->status === 'acceptee' ? 'text-success' : 'text-danger',
            'title' => "Demande {$statusLabel}",
            'body'  => "Votre demande de type « {$typeLabel} » a été {$statusLabel}.",
            'url'   => route('requests.index'),
        ]);
    }

    // ── 6. Attestation workflow step ──────────────────────────────────────

    public static function attestationStep(InternshipRequest $req, string $step): void
    {
        $req->loadMissing(['intern.user', 'intern.internships.supervisor', 'intern.internships.responsible']);

        $internName = $req->intern?->user?->full_name ?? 'Un stagiaire';
        $url = route('requests.index');

        switch ($step) {
            case 'supervisor':
                // Encadrant validated → notify RC
                $req->intern?->internships->each(function (Internship $i) use ($internName, $url): void {
                    if ($i->responsible) {
                        Notification::notify($i->responsible, [
                            'type'  => 'attestation_step',
                            'icon'  => 'bi-file-earmark-check',
                            'color' => 'text-primary',
                            'title' => 'Rapport validé par l\'encadrant',
                            'body'  => "Le rapport de {$internName} a été validé par l'encadrant. Votre validation est requise.",
                            'url'   => $url,
                        ]);
                    }
                });
                break;

            case 'rc':
                // RC validated → notify RH
                User::query()
                    ->whereHas('role', fn ($q) => $q->where('name', 'Responsable RH'))
                    ->get()
                    ->each(fn (User $rh) => Notification::notify($rh, [
                        'type'  => 'attestation_step',
                        'icon'  => 'bi-file-earmark-check',
                        'color' => 'text-primary',
                        'title' => 'Rapport transmis au RH',
                        'body'  => "Le rapport de {$internName} est transmis au RH pour génération de l'attestation.",
                        'url'   => $url,
                    ]));
                break;

            case 'rh':
                // RH generated attestation → notify intern
                $intern = $req->intern?->user;
                if ($intern) {
                    Notification::notify($intern, [
                        'type'  => 'attestation_ready',
                        'icon'  => 'bi-award',
                        'color' => 'text-success',
                        'title' => 'Attestation de stage prête',
                        'body'  => 'Votre attestation de stage est prête. Présentez-vous au service RH pour la récupérer.',
                        'url'   => $url,
                    ]);
                }
                break;
        }
    }

    // ── 7. Internship assignment ───────────────────────────────────────────

    public static function internshipAssigned(Internship $internship, array $previousInternIds = [], ?int $previousSupervisorId = null): void
    {
        $internship->loadMissing(['interns.user', 'supervisor']);

        // Notify newly added interns
        foreach ($internship->interns as $intern) {
            if ($intern->user && ! in_array($intern->id, $previousInternIds, true)) {
                Notification::notify($intern->user, [
                    'type'  => 'internship_assigned',
                    'icon'  => 'bi-briefcase',
                    'color' => 'text-primary',
                    'title' => 'Nouveau stage',
                    'body'  => "Vous avez été affecté au stage : « {$internship->title} ».",
                    'url'   => route('requests.index'),
                ]);
            }
        }

        // Notify new encadrant if changed
        $supervisor = $internship->supervisor;
        if ($supervisor && $internship->supervisor_id !== $previousSupervisorId) {
            Notification::notify($supervisor, [
                'type'  => 'internship_assigned',
                'icon'  => 'bi-briefcase',
                'color' => 'text-info',
                'title' => 'Stage assigné',
                'body'  => "Vous avez été désigné encadrant du stage : « {$internship->title} ».",
                'url'   => route('supervisor.internships'),
            ]);
        }
    }

    // ── 8. New message received ────────────────────────────────────────────

    public static function messageReceived(Message $message): void
    {
        $message->loadMissing(['receiver', 'sender']);

        $recipient = $message->receiver;
        if (! $recipient) {
            return;
        }

        $senderName = $message->sender?->full_name ?? 'Quelqu\'un';

        Notification::notify($recipient, [
            'type'  => 'message_received',
            'icon'  => 'bi-chat-dots',
            'color' => 'text-primary',
            'title' => "Message de {$senderName}",
            'body'  => $message->subject,
            'url'   => route('messages.index') . '?user=' . $message->sender_id,
        ]);
    }

    // ── 9. Password reset requested ───────────────────────────────────────

    public static function passwordResetRequested(): void
    {
        $url = route('admin.password-reset.index');

        User::query()
            ->whereHas('role', fn ($q) => $q->where('name', 'Administrateur'))
            ->get()
            ->each(fn (User $admin) => Notification::notify($admin, [
                'type'  => 'password_reset_requested',
                'icon'  => 'bi-key',
                'color' => 'text-warning',
                'title' => 'Demande de réinitialisation',
                'body'  => 'Un utilisateur a demandé la réinitialisation de son mot de passe.',
                'url'   => $url,
            ]));
    }

    // ── 10. Password reset processed ──────────────────────────────────────

    public static function passwordResetProcessed(PasswordResetRequest $resetRequest): void
    {
        $resetRequest->loadMissing('user');

        $recipient = $resetRequest->user;
        if (! $recipient) {
            return;
        }

        $accepted = $resetRequest->status === 'approuvee';
        $label = $accepted ? 'approuvée' : 'refusée';

        Notification::notify($recipient, [
            'type'  => 'password_reset_processed',
            'icon'  => $accepted ? 'bi-check-circle' : 'bi-x-circle',
            'color' => $accepted ? 'text-success' : 'text-danger',
            'title' => "Réinitialisation de mot de passe {$label}",
            'body'  => $accepted
                ? 'Votre demande de réinitialisation a été approuvée. Vérifiez vos identifiants.'
                : 'Votre demande de réinitialisation a été refusée. Contactez un administrateur.',
            'url'   => route('login'),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private static function requestTypeLabel(string $type): string
    {
        return match ($type) {
            'prolongation'         => 'Prolongation',
            'attestation'          => 'Attestation de stage',
            'retard_attestation'   => 'Retard d\'attestation',
            'absence'              => 'Absence',
            default                => 'Autre',
        };
    }
}
