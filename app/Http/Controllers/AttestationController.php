<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttestationController extends Controller
{
    public function show(Request $request, Intern $intern): View
    {
        $user = $request->user();

        $canAccess = $user->hasRole('Administrateur', 'Responsable RH', 'Responsable de competence', 'Encadrant')
            || ($user->hasRole('Stagiaire') && $user->intern !== null && $user->intern->id === $intern->id);

        if (! $canAccess) {
            abort(403, 'Acces refuse a cette attestation.');
        }

        $intern->load('user');

        $internships = $intern->internships()
            ->with(['supervisor', 'responsible', 'interns'])
            ->orderByDesc('end_date')
            ->get();

        $generatedAt = now();
        $rhUser = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'Responsable RH'))
            ->where(function ($query) {
                $query->whereNotNull('rh_signature_path')
                    ->orWhereNotNull('company_stamp_path');
            })
            ->latest()
            ->first();

        return view('attestations.show', compact('intern', 'internships', 'generatedAt', 'rhUser'));
    }
}
