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

        $stats = [
            'users' => User::query()->count(),
            'interns' => Intern::query()->count(),
            'active_internships' => Internship::query()->where('status', 'en_cours')->count(),
            'pending_requests' => InternshipRequest::query()->where('status', 'en_attente')->count(),
            'my_open_tasks' => Task::query()
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['a_faire', 'en_cours'])
                ->count(),
            'unread_messages' => Message::query()
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count(),
        ];

        $latestTasks = Task::query()
            ->with(['assignedBy', 'assignedTo'])
            ->when($user->hasRole('Stagiaire'), fn ($query) => $query->where('assigned_to', $user->id))
            ->latest()
            ->take(5)
            ->get();

        $latestRequests = InternshipRequest::query()
            ->with(['intern.user', 'processedBy'])
            ->when($user->hasRole('Stagiaire') && $user->intern !== null, fn ($query) => $query->where('intern_id', $user->intern->id))
            ->when($user->hasRole('Stagiaire') && $user->intern === null, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact('stats', 'latestTasks', 'latestRequests'));
    }
}
