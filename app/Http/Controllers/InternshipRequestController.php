<?php

namespace App\Http\Controllers;

use App\Models\InternshipRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InternshipRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $requests = InternshipRequest::query()
            ->with(['intern.user', 'processedBy'])
            ->when($user->hasRole('Stagiaire') && $user->intern !== null, fn ($query) => $query->where('intern_id', $user->intern->id))
            ->when($user->hasRole('Stagiaire') && $user->intern === null, fn ($query) => $query->whereRaw('1 = 0'))
            ->latest()
            ->paginate(12);

        return view('requests.index', compact('requests'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        if (! $user->hasRole('Stagiaire') || $user->intern === null) {
            abort(403, 'Seuls les stagiaires lies a une fiche peuvent creer une demande.');
        }

        return view('requests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole('Stagiaire') || $user->intern === null) {
            abort(403, 'Action non autorisee.');
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(['prolongation', 'attestation', 'autre'])],
            'message' => ['required', 'string'],
        ]);

        InternshipRequest::query()->create([
            'intern_id' => $user->intern->id,
            'type' => $validated['type'],
            'message' => $validated['message'],
            'status' => 'en_attente',
        ]);

        return redirect()->route('requests.index')->with('success', 'Demande envoyee.');
    }

    public function process(Request $request, InternshipRequest $requestItem): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole('Administrateur', 'Responsable de competence')) {
            abort(403, 'Action reservee aux responsables et administrateurs.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['acceptee', 'refusee'])],
        ]);

        $requestItem->update([
            'status' => $validated['status'],
            'processed_by' => $user->id,
        ]);

        return back()->with('success', 'Demande traitee.');
    }

    public function destroy(Request $request, InternshipRequest $requestItem): RedirectResponse
    {
        $user = $request->user();

        $canDelete = $user->hasRole('Administrateur', 'Responsable de competence')
            || ($user->hasRole('Stagiaire')
                && $user->intern !== null
                && $requestItem->intern_id === $user->intern->id
                && $requestItem->status === 'en_attente');

        if (! $canDelete) {
            abort(403, 'Suppression non autorisee.');
        }

        $requestItem->delete();

        return back()->with('success', 'Demande supprimee.');
    }
}
