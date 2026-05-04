<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\Internship;
use App\Models\InternshipRequest;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('Administrateur');
        $isManager = $user->hasRole('Responsable de competence', 'Encadrant');
        $isIntern = $user->hasRole('Stagiaire');

        $managedInternIds = collect();

        if ($isManager) {
            $managedInternIds = Internship::query()
                ->when($user->hasRole('Responsable de competence'), fn ($query) => $query->orWhere('responsible_id', $user->id))
                ->when($user->hasRole('Encadrant'), fn ($query) => $query->orWhere('supervisor_id', $user->id))
                ->pluck('intern_id')
                ->unique()
                ->values();
        }

        $stats = [];

        if ($isAdmin) {
            $stats = [
                'users' => User::query()->count(),
                'interns' => Intern::query()->count(),
                'active_internships' => Internship::query()->where('status', 'en_cours')->count(),
                'pending_requests' => InternshipRequest::query()->where('status', 'en_attente')->count(),
            ];
        } elseif ($isManager) {
            $stats = [
                'interns' => $managedInternIds->count(),
                'active_internships' => Internship::query()
                    ->whereIn('intern_id', $managedInternIds)
                    ->where('status', 'en_cours')
                    ->count(),
                'pending_requests' => InternshipRequest::query()
                    ->whereIn('intern_id', $managedInternIds)
                    ->where('status', 'en_attente')
                    ->count(),
            ];
        } elseif ($isIntern && $user->intern !== null) {
            $stats = [
                'active_internships' => Internship::query()
                    ->where('intern_id', $user->intern->id)
                    ->where('status', 'en_cours')
                    ->count(),
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
        } elseif ($isManager) {
            $statCards = [
                ['label' => 'Stagiaires', 'value' => $stats['interns'] ?? 0],
                ['label' => 'Stages en cours', 'value' => $stats['active_internships'] ?? 0],
                ['label' => 'Demandes en attente', 'value' => $stats['pending_requests'] ?? 0],
            ];
        } elseif ($isIntern) {
            $statCards = [
                ['label' => 'Mes stages actifs', 'value' => $stats['active_internships'] ?? 0],
                ['label' => 'Mes demandes en attente', 'value' => $stats['pending_requests'] ?? 0],
                ['label' => 'Mes taches ouvertes', 'value' => $stats['my_open_tasks'] ?? 0],
                ['label' => 'Messages non lus', 'value' => $stats['unread_messages'] ?? 0],
            ];
        }

        $latestTasks = Task::query()
            ->with(['assignedBy', 'assignedTo'])
            ->when($isIntern, fn ($query) => $query->where('assigned_to', $user->id))
            ->when($isManager, function ($query) use ($managedInternIds) {
                if ($managedInternIds->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereHas('internship', fn ($subQuery) => $subQuery->whereIn('intern_id', $managedInternIds));
                }
            })
            ->latest()
            ->take(5)
            ->get();

        $latestRequests = InternshipRequest::query()
            ->with(['intern.user', 'processedBy'])
            ->when($isIntern && $user->intern !== null, fn ($query) => $query->where('intern_id', $user->intern->id))
            ->when($isIntern && $user->intern === null, fn ($query) => $query->whereRaw('1 = 0'))
            ->when($isManager, function ($query) use ($managedInternIds) {
                if ($managedInternIds->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('intern_id', $managedInternIds);
                }
            })
            ->latest()
            ->take(5)
            ->get();

        $evaluatedInterns = collect();

        if ($isAdmin) {
            $evaluatedInterns = Intern::query()
                ->with(['user', 'absences', 'internships.tasks'])
                ->where('is_archived', false)
                ->latest()
                ->take(5)
                ->get();
        } elseif ($isManager) {
            $evaluatedInterns = Intern::query()
                ->with(['user', 'absences', 'internships.tasks'])
                ->where('is_archived', false)
                ->when($managedInternIds->isEmpty(), fn ($query) => $query->whereRaw('1 = 0'))
                ->when($managedInternIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $managedInternIds))
                ->latest()
                ->take(5)
                ->get();
        } elseif ($isIntern && $user->intern !== null) {
            $evaluatedInterns = collect([
                $user->intern->load(['user', 'absences', 'internships.tasks']),
            ]);
        }

        $smartAlerts = $evaluatedInterns
            ->flatMap(fn (Intern $intern) => collect($intern->smartAlerts())->map(fn (array $alert) => [
                'intern' => $intern,
                'alert' => $alert,
            ]))
            ->take(8)
            ->values();

        return view('dashboard.index', compact('stats', 'statCards', 'latestTasks', 'latestRequests', 'evaluatedInterns', 'smartAlerts'));
    }
}
