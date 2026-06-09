<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\Internship;
use App\Models\InternshipRequest;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const RECENT_LIMIT = 3;

    public function index(): View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('Administrateur');
        $isHr = $user->hasRole('Responsable RH');
        $isManager = $user->hasRole('Responsable de competence', 'Encadrant');
        $canViewTasks = ! $user->hasRole('Responsable de competence', 'Responsable RH');
        $isIntern = $user->hasRole('Stagiaire');

        $managedInternIds = collect();

        if ($isManager) {
            $managedInternIds = Intern::query()
                ->whereHas('internships', function ($query) use ($user) {
                    $this->scopeManagedInternships($query, $user);
                })
                ->pluck('id')
                ->unique()
                ->values();
        }

        $stats = [];

        if ($isAdmin || $isHr) {
            $stats = [
                'interns' => Intern::query()->count(),
                'active_internships' => Internship::query()->where('status', 'en_cours')->count(),
                'completed_internships' => Internship::query()->where('status', 'termine')->count(),
                'attestations_to_process' => InternshipRequest::query()
                    ->where('type', 'attestation')
                    ->where('workflow_status', 'transmise_rh')
                    ->count(),
                'generated_attestations' => InternshipRequest::query()
                    ->where('type', 'attestation')
                    ->whereIn('workflow_status', ['attestation_generee', 'attestation_prete', 'attestation_imprimee', 'attestation_recuperee', 'attestation_archivee'])
                    ->count(),
                'pending_requests' => InternshipRequest::query()
                    ->where('status', 'en_attente')
                    ->when($isHr, fn ($query) => $query->where('type', 'attestation')->whereNotNull('sent_to_rh_at'))
                    ->count(),
            ];

            if ($isAdmin) {
                $stats['users'] = User::query()->count();
            }
        } elseif ($isManager) {
            $stats = [
                'interns' => $managedInternIds->count(),
                'active_internships' => Internship::query()
                    ->where('status', 'en_cours')
                    ->whereHas('interns', fn ($query) => $query->whereIn('interns.id', $managedInternIds))
                    ->count(),
                'pending_requests' => InternshipRequest::query()
                    ->whereIn('intern_id', $managedInternIds)
                    ->where('status', 'en_attente')
                    ->count(),
            ];
        } elseif ($isIntern && $user->intern !== null) {
            $stats = [
                'pending_requests' => InternshipRequest::query()
                    ->where('intern_id', $user->intern->id)
                    ->where('status', 'en_attente')
                    ->count(),
                'my_open_tasks' => Task::query()
                    ->where('assigned_to', $user->id)
                    ->whereIn('status', ['a_faire', 'en_cours'])
                    ->count(),
                'unread_messages' => Message::query()
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count(),
            ];
        }

        $statCards = [];

        if ($isAdmin) {
            $statCards = [
                ['label' => 'Utilisateurs', 'value' => $stats['users'] ?? 0],
                ['label' => 'Stagiaires', 'value' => $stats['interns'] ?? 0],
                ['label' => 'Stages en cours', 'value' => $stats['active_internships'] ?? 0],
                ['label' => 'Demandes en attente', 'value' => $stats['pending_requests'] ?? 0],
            ];
        } elseif ($isHr) {
            $statCards = [
                ['label' => 'Total stagiaires', 'value' => $stats['interns'] ?? 0],
                ['label' => 'Stages terminés', 'value' => $stats['completed_internships'] ?? 0],
                ['label' => 'Attestations à traiter', 'value' => $stats['attestations_to_process'] ?? 0],
                ['label' => 'Attestations générées', 'value' => $stats['generated_attestations'] ?? 0],
                ['label' => 'Demandes en attente', 'value' => $stats['pending_requests'] ?? 0],
            ];
        } elseif ($isManager) {
            $statCards = [
                ['label' => 'Stagiaires', 'value' => $stats['interns'] ?? 0],
                ['label' => 'Stages en cours', 'value' => $stats['active_internships'] ?? 0],
                ['label' => 'Demandes en attente', 'value' => $stats['pending_requests'] ?? 0],
            ];
        } elseif ($isIntern) {
            $statCards = [
                ['label' => 'Mes demandes en attente', 'value' => $stats['pending_requests'] ?? 0],
                ['label' => 'Mes tâches ouvertes', 'value' => $stats['my_open_tasks'] ?? 0],
                ['label' => 'Messages non lus', 'value' => $stats['unread_messages'] ?? 0],
            ];
        }

        $latestTasks = collect();

        if ($canViewTasks) {
            $latestTasks = Task::query()
                ->with(['assignedBy', 'assignedTo'])
                ->when($isIntern, fn ($query) => $query->where('assigned_to', $user->id))
                ->when($isManager, function ($query) use ($managedInternIds, $user) {
                    if ($managedInternIds->isEmpty()) {
                        $query->whereRaw('1 = 0');
                    } else {
                        $query
                            ->whereHas('internship.interns', fn ($subQuery) => $subQuery->whereIn('interns.id', $managedInternIds))
                            ->whereHas('internship', function ($subQuery) use ($user) {
                                $this->scopeManagedInternships($subQuery, $user);
                            });
                    }
                })
                ->latest()
                ->take(self::RECENT_LIMIT)
                ->get();
        }

        $latestRequests = InternshipRequest::query()
            ->with(['intern.user', 'processedBy'])
            ->when($isIntern && $user->intern !== null, fn ($query) => $query->where('intern_id', $user->intern->id))
            ->when($isIntern && $user->intern === null, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($isHr, fn ($query) => $query->where('type', 'attestation')->whereNotNull('sent_to_rh_at'))
            ->when($isManager, function ($query) use ($managedInternIds) {
                if ($managedInternIds->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('intern_id', $managedInternIds);
                }
            })
            ->latest()
            ->take(self::RECENT_LIMIT)
            ->get();

        $allEvaluatedInterns = collect();
        $alertEvaluatedInterns = collect();

        if ($isAdmin || $isHr) {
            $allEvaluatedInterns = Intern::query()
                ->with(['user', 'absences', 'internships.tasks', 'weeklyReports'])
                ->where('is_archived', false)
                ->latest()
                ->get();
            $alertEvaluatedInterns = $allEvaluatedInterns;
        } elseif ($isManager) {
            $allEvaluatedInterns = Intern::query()
                ->with(['user', 'absences', 'internships.tasks', 'weeklyReports'])
                ->where('is_archived', false)
                ->when($managedInternIds->isEmpty(), fn ($query) => $query->whereRaw('1 = 0'))
                ->when($managedInternIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $managedInternIds))
                ->latest()
                ->get();

            $alertEvaluatedInterns = Intern::query()
                ->with([
                    'user',
                    'absences',
                    'internships' => function ($query) use ($user) {
                        $this->scopeManagedInternships($query, $user);
                    },
                    'internships.tasks',
                ])
                ->where('is_archived', false)
                ->when($managedInternIds->isEmpty(), fn ($query) => $query->whereRaw('1 = 0'))
                ->when($managedInternIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $managedInternIds))
                ->latest()
                ->get();
        } elseif ($isIntern && $user->intern !== null) {
            $allEvaluatedInterns = collect([
                $user->intern->load(['user', 'absences', 'internships.tasks', 'weeklyReports']),
            ]);
            $alertEvaluatedInterns = $allEvaluatedInterns;
        }

        $sortedInternsByScore = $allEvaluatedInterns
            ->sortByDesc(fn (Intern $intern): int => $intern->performanceScore()['score'] ?? -1)
            ->values();

        $scoresPage = max(1, (int) request()->query('scores_page', 1));

        $evaluatedInterns = new LengthAwarePaginator(
            $sortedInternsByScore->forPage($scoresPage, self::RECENT_LIMIT)->values(),
            $sortedInternsByScore->count(),
            self::RECENT_LIMIT,
            $scoresPage,
            [
                'path' => request()->url(),
                'pageName' => 'scores_page',
                'query' => request()->except('scores_page'),
            ]
        );

        $smartAlerts = $alertEvaluatedInterns
            ->flatMap(fn (Intern $intern) => collect($intern->smartAlerts())
                ->when(! $canViewTasks, fn ($alerts) => $alerts->reject(fn (array $alert): bool => $alert['type'] === 'task'))
                ->map(fn (array $alert) => [
                    'intern' => $intern,
                    'alert' => $alert,
                ]))
            ->take(self::RECENT_LIMIT)
            ->values();

        return view('dashboard.index', compact('stats', 'statCards', 'latestTasks', 'latestRequests', 'evaluatedInterns', 'smartAlerts', 'canViewTasks'));
    }

    private function scopeManagedInternships($query, User $user): void
    {
        $query->where(function (Builder $subQuery) use ($user): void {
            if ($user->hasRole('Responsable de competence')) {
                $subQuery->orWhere('responsible_id', $user->id);
            }

            if ($user->hasRole('Encadrant')) {
                $subQuery->orWhere('supervisor_id', $user->id);
            }
        });
    }
}
