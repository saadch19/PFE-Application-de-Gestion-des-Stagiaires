<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = (string) $request->string('status');
        $internshipId = (string) $request->string('internship_id');
        $myTasksOnly = (string) $request->query('my_tasks', '1') === '1';

        $tasksQuery = Task::query()
            ->with(['internship.interns.user', 'assignedBy', 'assignedTo'])
            ->when($user->hasRole('Stagiaire'), function ($query) use ($user, $myTasksOnly) {
                if ($user->intern !== null) {
                    $query->whereHas('internship.interns', fn ($subQuery) => $subQuery->where('interns.id', $user->intern->id));
                    if ($myTasksOnly) {
                        $query->where('assigned_to', $user->id);
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when($user->hasRole('Encadrant'), function ($query) use ($user) {
                $query->whereHas('internship', fn ($subQuery) => $subQuery->where('supervisor_id', $user->id));
            });

        if ($user->hasRole('Encadrant') && $internshipId !== '') {
            $tasksQuery->where('internship_id', $internshipId);
        }

        if (! $user->hasRole('Encadrant') && $status !== '') {
            $tasksQuery->where('status', $status);
        }

        $tasks = $tasksQuery
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $internships = null;

        if ($user->hasRole('Encadrant')) {
            $internships = Internship::query()
                ->where('supervisor_id', $user->id)
                ->orderBy('title')
                ->get();
        }

        return view('tasks.index', compact('tasks', 'status', 'internshipId', 'internships', 'myTasksOnly'));
    }

    public function create(): View
    {
        $user = auth()->user();

        $internships = Internship::query()
            ->with('interns.user')
            ->whereIn('status', ['planifie', 'en_cours'])
            ->when($user->hasRole('Encadrant'), fn ($query) => $query->where('supervisor_id', $user->id))
            ->orderBy('title')
            ->get();

        $users = User::query()
            ->with(['role', 'intern'])
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Stagiaire'))
            ->when($user->hasRole('Encadrant'), function ($query) use ($user) {
                $query->whereHas('intern.internships', fn ($internshipQuery) => $internshipQuery->where('supervisor_id', $user->id));
            })
            ->orderBy('full_name')
            ->get();

        return view('tasks.create', compact('internships', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'internship_id' => ['nullable', 'exists:internships,id'],
            'assigned_to' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:180'],
            'details' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date_format:d/m/Y'],
            'status' => ['required', Rule::in(['a_faire', 'en_cours', 'termine'])],
        ]);

        if (! empty($validated['due_date'])) {
            $validated['due_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['due_date'])->toDateString();
        }

        $this->validateDueDateLimit($validated);

        Task::query()->create($validated + ['assigned_by' => $request->user()->id]);

        return redirect()->route('tasks.index')->with('success', 'Tache creee avec succes.');
    }

    public function edit(Task $task): View
    {
        $user = auth()->user();

        $internships = Internship::query()
            ->with('interns.user')
            ->whereIn('status', ['planifie', 'en_cours'])
            ->when($user->hasRole('Encadrant'), fn ($query) => $query->where('supervisor_id', $user->id))
            ->orderBy('title')
            ->get();

        $users = User::query()
            ->with(['role', 'intern'])
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Stagiaire'))
            ->when($user->hasRole('Encadrant'), function ($query) use ($user) {
                $query->whereHas('intern.internships', fn ($internshipQuery) => $internshipQuery->where('supervisor_id', $user->id));
            })
            ->orderBy('full_name')
            ->get();

        return view('tasks.edit', compact('task', 'internships', 'users'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'internship_id' => ['nullable', 'exists:internships,id'],
            'assigned_to' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:180'],
            'details' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date_format:d/m/Y'],
            'status' => ['required', Rule::in(['a_faire', 'en_cours', 'termine'])],
        ]);

        if (! empty($validated['due_date'])) {
            $validated['due_date'] = \Carbon\Carbon::createFromFormat('d/m/Y', $validated['due_date'])->toDateString();
        }

        $this->validateDueDateLimit($validated);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Tache mise a jour.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tache supprimee.');
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        $canUpdate = $user->id === $task->assigned_to
            || $user->id === $task->assigned_by
            || $user->hasRole('Administrateur', 'Encadrant');

        if (! $canUpdate) {
            abort(403, 'Non autorise a modifier cette tache.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['a_faire', 'en_cours', 'termine'])],
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Statut de la tache mis a jour.']);
    }

    private function validateDueDateLimit(array $validated): void
    {
        $dueDate = $validated['due_date'] ?? null;
        $internshipId = $validated['internship_id'] ?? null;

        if ($internshipId !== null) {
            $internship = Internship::query()
                ->with('interns')
                ->find($internshipId);

            $allowedAssigneeIds = $internship?->interns
                ?->pluck('user_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all() ?? [];

            if (! empty($allowedAssigneeIds) && ! in_array((int) $validated['assigned_to'], $allowedAssigneeIds, true)) {
                throw ValidationException::withMessages([
                    'assigned_to' => 'Cette tache doit etre assignee au stagiaire lie au stage selectionne.',
                ]);
            }

            if ($dueDate !== null) {
                $dueDateValue = $dueDate instanceof \Carbon\CarbonInterface
                    ? $dueDate
                    : \Carbon\Carbon::parse($dueDate);

                if ($internship?->start_date !== null && $internship->start_date->gt($dueDateValue)) {
                    throw ValidationException::withMessages([
                        'due_date' => 'La date limite doit etre apres la date de debut du stage.',
                    ]);
                }

                if ($internship?->end_date !== null && $internship->end_date->lt($dueDateValue)) {
                    throw ValidationException::withMessages([
                        'due_date' => 'La date limite ne doit pas depasser la date de fin du stage.',
                    ]);
                }

                if ($internship?->end_date === null || $internship?->start_date === null) {
                    throw ValidationException::withMessages([
                        'due_date' => 'La date limite ne peut pas etre definie sans dates de stage.',
                    ]);
                }
            }

            return;
        }

        return;
    }
}
