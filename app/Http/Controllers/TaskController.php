<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('tasks.index', compact('tasks', 'status'));
    }

    public function create(): View
    {
        $internships = Internship::query()
            ->with('intern.user')
            ->whereIn('status', ['planifie', 'en_cours'])
            ->orderBy('title')
            ->get();

        $users = User::query()
            ->with('role')
            ->where('is_active', true)
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

        Task::query()->create($validated + ['assigned_by' => $request->user()->id]);

        return redirect()->route('tasks.index')->with('success', 'Tache creee avec succes.');
    }

    public function edit(Task $task): View
    {
        $internships = Internship::query()
            ->with('intern.user')
            ->whereIn('status', ['planifie', 'en_cours'])
            ->orderBy('title')
            ->get();

        $users = User::query()
            ->with('role')
            ->where('is_active', true)
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
            || $user->hasRole('Administrateur', 'Responsable de competence', 'Encadrant');

        if (! $canUpdate) {
            abort(403, 'Non autorise a modifier cette tache.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['a_faire', 'en_cours', 'termine'])],
        ]);

        $task->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Statut de la tache mis a jour.']);
    }
}
