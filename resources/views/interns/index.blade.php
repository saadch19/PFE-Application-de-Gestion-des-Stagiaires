@extends('layouts.app')

@section('title', 'Stagiaires')

@section('content')
@php
    $isSupervisor = auth()->user()->hasRole('Encadrant');
    $isHr = auth()->user()->hasRole('Responsable RH');
    $highlightInternId = $highlightInternId ?? null;
@endphp

<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Gestion des stagiaires</h1>
            @unless($isSupervisor)
                <a href="{{ route('interns.create') }}" class="btn btn-success btn-sm">Nouveau stagiaire</a>
            @endunless
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Rechercher (CIN, ecole, specialite)">
            </div>
            @unless($isSupervisor)
                <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check mt-2 mt-md-0">
                        <input class="form-check-input" type="checkbox" value="1" id="archived" name="archived" @checked($showArchived)>
                        <label class="form-check-label" for="archived">Afficher les archives</label>
                    </div>
                </div>
            @endunless
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" type="submit">Filtrer</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Compte</th>
                        <th>Ecole / Specialite</th>
                        <th>Periode</th>
                        <th>Etat</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($interns as $intern)
                        @php
                            $isCompleted = $intern->end_date !== null && $intern->end_date->lt(today()->subDay());
                            $hasAssignedInternship = $intern->internships
                                ->contains(fn ($internship) => $internship->supervisor_id !== null);
                            $attestationRequest = $intern->requests
                                ->where('type', 'attestation')
                                ->sortByDesc('created_at')
                                ->first();
                            $hasSupervisorValidation = $attestationRequest?->supervisor_validated_at !== null;
                            $hasRcValidation = $attestationRequest?->rc_validated_at !== null;
                            $isValidatedForRh = $hasSupervisorValidation || $hasRcValidation;
                            $canRhEdit = ! $intern->is_archived && ! $isValidatedForRh;
                            $canShowAttestation = ! $isHr || ($hasSupervisorValidation && $hasRcValidation);
                            $showHighlight = (int) $highlightInternId === (int) $intern->id;
                        @endphp
                        <tr @class(['table-success' => $showHighlight])>
                            <td>{{ $intern->cin }}</td>
                            <td>{{ $intern->user?->full_name ?? 'Non lie' }}</td>
                            <td>
                                <div>{{ $intern->school }}</div>
                                <small class="text-muted">{{ $intern->specialty }}</small>
                            </td>
                            <td>{{ $intern->start_date?->format('d/m/Y') }} - {{ $intern->end_date?->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $intern->is_archived ? 'text-bg-secondary' : ($isCompleted ? 'text-bg-warning' : ($hasAssignedInternship ? 'text-bg-success' : 'text-bg-info')) }}">
                                    {{ $intern->is_archived ? 'Archive' : ($isCompleted ? 'Termine' : ($hasAssignedInternship ? 'Actif' : 'En attente')) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ $isSupervisor ? route('supervisor.interns.show', $intern) : route('interns.show', $intern) }}" class="btn btn-sm btn-outline-secondary">Voir</a>

                                @unless($isSupervisor)
                                    @if(! $isHr || $canRhEdit)
                                        <a href="{{ route('interns.edit', $intern) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                    @endif

                                    @if($intern->is_archived)
                                        <form action="{{ route('interns.restore', $intern) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success" type="submit">Restaurer</button>
                                        </form>
                                    @elseif($isCompleted)
                                        <form action="{{ route('interns.archive', $intern) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning" type="submit">Archiver</button>
                                        </form>
                                    @endif

                                    @if($canShowAttestation)
                                        <a href="{{ route('attestations.show', $intern) }}" class="btn btn-sm btn-outline-info">Attestation</a>
                                    @endif
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun stagiaire.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $interns->links() }}
    </div>
</div>
@endsection
