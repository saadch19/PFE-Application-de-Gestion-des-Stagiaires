<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttestationController extends Controller
{
    public function show(Request $request, Intern $intern): View
    {
        $user = $request->user();

        $canAccess = $user->hasRole('Administrateur', 'Responsable de competence', 'Encadrant')
            || ($user->hasRole('Stagiaire') && $user->intern !== null && $user->intern->id === $intern->id);

        if (! $canAccess) {
            abort(403, 'Acces refuse a cette attestation.');
        }

        $intern->load('user');

        $internship = $intern->internships()
            ->with(['supervisor', 'responsible'])
            ->latest('end_date')
            ->first();

        $generatedAt = now();

        return view('attestations.show', compact('intern', 'internship', 'generatedAt'));
    }
}
