<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string) $request->string('search');
        $showArchived = $request->boolean('archived');

        $interns = Intern::query()
            ->with('user')
            ->when(! $showArchived, fn ($query) => $query->where('is_archived', false))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('cin', 'like', "%{$search}%")
                        ->orWhere('school', 'like', "%{$search}%")
                        ->orWhere('specialty', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('interns.index', compact('interns', 'search', 'showArchived'));
    }

    public function create(): View
    {
        $users = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Stagiaire'))
            ->whereDoesntHave('intern')
            ->orderBy('full_name')
            ->get();

        return view('interns.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id', 'unique:interns,user_id'],
            'cin' => ['required', 'string', 'max:40', 'unique:interns,cin'],
            'school' => ['required', 'string', 'max:120'],
            'specialty' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        Intern::query()->create($validated + ['is_archived' => false]);

        return redirect()->route('interns.index')->with('success', 'Stagiaire ajoute.');
    }

    public function edit(Intern $intern): View
    {
        $users = User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', 'Stagiaire'))
            ->where(function ($query) use ($intern) {
                $query->whereDoesntHave('intern')
                    ->orWhere('id', $intern->user_id);
            })
            ->orderBy('full_name')
            ->get();

        return view('interns.edit', compact('intern', 'users'));
    }

    public function update(Request $request, Intern $intern): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('interns', 'user_id')->ignore($intern->id),
            ],
            'cin' => ['required', 'string', 'max:40', Rule::unique('interns', 'cin')->ignore($intern->id)],
            'school' => ['required', 'string', 'max:120'],
            'specialty' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $intern->update($validated);

        return redirect()->route('interns.index')->with('success', 'Stagiaire mis a jour.');
    }

    public function destroy(Intern $intern): RedirectResponse
    {
        $intern->delete();

        return redirect()->route('interns.index')->with('success', 'Stagiaire supprime.');
    }

    public function archive(Intern $intern): RedirectResponse
    {
        $intern->update(['is_archived' => true]);

        return back()->with('success', 'Stagiaire archive.');
    }

    public function restore(Intern $intern): RedirectResponse
    {
        $intern->update(['is_archived' => false]);

        return back()->with('success', 'Stagiaire restaure.');
    }
}
