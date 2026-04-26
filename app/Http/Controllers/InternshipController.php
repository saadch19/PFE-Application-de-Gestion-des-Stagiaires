<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\Internship;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternshipController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->string('status');

        $internships = Internship::query()
            ->with(['intern.user', 'supervisor', 'responsible'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('internships.index', compact('internships', 'status'));
    }

    public function create(): View
    {
        $interns = Intern::query()->where('is_archived', false)->orderBy('cin')->get();

        $supervisors = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Encadrant'))
            ->orderBy('full_name')
            ->get();

        $responsibles = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Responsable de competence'))
            ->orderBy('full_name')
            ->get();

        return view('internships.create', compact('interns', 'supervisors', 'responsibles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'department' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['planifie', 'en_cours', 'termine'])],
            'intern_id' => ['required', 'exists:interns,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'responsible_id' => ['nullable', 'exists:users,id'],
        ]);

        Internship::query()->create($validated);

        return redirect()->route('internships.index')->with('success', 'Stage cree avec succes.');
    }

    public function edit(Internship $internship): View
    {
        $interns = Intern::query()->where('is_archived', false)->orderBy('cin')->get();

        $supervisors = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Encadrant'))
            ->orderBy('full_name')
            ->get();

        $responsibles = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Responsable de competence'))
            ->orderBy('full_name')
            ->get();

        return view('internships.edit', compact('internship', 'interns', 'supervisors', 'responsibles'));
    }

    public function update(Request $request, Internship $internship): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'department' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['planifie', 'en_cours', 'termine'])],
            'intern_id' => ['required', 'exists:interns,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'responsible_id' => ['nullable', 'exists:users,id'],
        ]);

        $internship->update($validated);

        return redirect()->route('internships.index')->with('success', 'Stage mis a jour.');
    }

    public function destroy(Internship $internship): RedirectResponse
    {
        $internship->delete();

        return redirect()->route('internships.index')->with('success', 'Stage supprime.');
    }

    public function updateStatus(Request $request, Internship $internship): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['planifie', 'en_cours', 'termine'])],
        ]);

        $internship->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Statut du stage mis a jour.']);
    }
}
