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

        $tasks = Task::query()
            ->with(['internship.intern.user', 'assignedBy', 'assignedTo'])
            ->when($user->hasRole('Stagiaire'), fn ($query) => $query->where('assigned_to', $user->id))
            ->when($user->hasRole('Encadrant'), function ($query) use ($user) {
                $query->whereHas('assignedTo.intern.internships', function ($internshipQuery) use ($user) {
                    $internshipQuery->where('supervisor_id', $user->id);
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('tasks.index', compact('tasks', 'status'));
    }

    public function create(): View
    {
        $user = auth()->user();

        $internships = Internship::query()
            ->with('intern.user')
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
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['a_faire', 'en_cours', 'termine'])],
        ]);

        $this->validateDueDateLimit($validated);

        Task::query()->create($validated + ['assigned_by' => $request->user()->id]);

        return redirect()->route('tasks.index')->with('success', 'Tache creee avec succes.');
    }

    public function edit(Task $task): View
    {
        $user = auth()->user();

        $internships = Internship::query()
            ->with('intern.user')
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
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['a_faire', 'en_cours', 'termine'])],
        ]);

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
                ->with('intern')
                ->find($internshipId);

            if ($internship?->intern?->user_id !== null && (int) $validated['assigned_to'] !== (int) $internship->intern->user_id) {
                throw ValidationException::withMessages([
                    'assigned_to' => 'Cette tache doit etre assignee au stagiaire lie au stage selectionne.',
                ]);
            }

            if ($dueDate !== null && $internship?->end_date !== null && $internship->end_date->lt($dueDate)) {
                throw ValidationException::withMessages([
                    'due_date' => 'La date limite ne doit pas depasser la date de fin du stage.',
                ]);
            }

            return;
        }

        if ($dueDate === null) {
            return;
        }

        $assignedUser = User::query()
            ->with('intern')
            ->find($validated['assigned_to']);

        if ($assignedUser?->intern?->end_date !== null && $assignedUser->intern->end_date->lt($dueDate)) {
            throw ValidationException::withMessages([
                'due_date' => 'La date limite ne doit pas depasser la date de fin du stage du stagiaire.',
            ]);
        }
    }
}
