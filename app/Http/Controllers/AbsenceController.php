<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Intern;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsenceController extends Controller
{
    public function index(): View
    {
        $absences = Absence::query()
            ->with(['intern.user', 'recordedBy'])
            ->latest('date_absence')
            ->paginate(12);

        return view('absences.index', compact('absences'));
    }

    public function create(): View
    {
        $interns = Intern::query()
            ->where('is_archived', false)
            ->orderBy('cin')
            ->get();

        return view('absences.create', compact('interns'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'intern_id' => ['required', 'exists:interns,id'],
            'date_absence' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'justified' => ['nullable', 'boolean'],
        ]);

        Absence::query()->create($validated + [
            'justified' => $request->boolean('justified'),
            'recorded_by' => $request->user()->id,
        ]);

        return redirect()->route('absences.index')->with('success', 'Absence enregistree.');
    }

    public function edit(Absence $absence): View
    {
        $interns = Intern::query()
            ->where('is_archived', false)
            ->orderBy('cin')
            ->get();

        return view('absences.edit', compact('absence', 'interns'));
    }

    public function update(Request $request, Absence $absence): RedirectResponse
    {
        $validated = $request->validate([
            'intern_id' => ['required', 'exists:interns,id'],
            'date_absence' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'justified' => ['nullable', 'boolean'],
        ]);

        $absence->update($validated + ['justified' => $request->boolean('justified')]);

        return redirect()->route('absences.index')->with('success', 'Absence mise a jour.');
    }

    public function destroy(Absence $absence): RedirectResponse
    {
        $absence->delete();

        return redirect()->route('absences.index')->with('success', 'Absence supprimee.');
    }
}
