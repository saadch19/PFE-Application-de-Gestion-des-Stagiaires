@extends('layouts.app')

@section('title', 'Detail stagiaire')

@section('content')
@php
    $canViewInternTasks = ! auth()->user()->hasRole('Responsable de competence');
    $isSupervisor = auth()->user()->hasRole('Encadrant');
    $supervisors = $intern->internships
        ->pluck('supervisor')
        ->filter()
        ->unique('id')
        ->pluck('full_name')
        ->values();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 fade-in">
    <div>
        <h1 class="h3 mb-1">{{ $intern->user?->full_name ?? 'Stagiaire non lie' }}</h1>
        <p class="text-muted mb-0">{{ $intern->school }} - {{ $intern->specialty }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $isSupervisor ? route('supervisor.interns') : route('interns.index') }}" class="btn btn-outline-secondary btn-sm">Retour</a>
        @unless($isSupervisor)
            <a href="{{ route('interns.edit', $intern) }}" class="btn btn-outline-primary btn-sm">Modifier</a>
        @endunless
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-5 fade-in">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Score automatique</h2>
                        <p class="text-muted mb-0">Evaluation calculee depuis les absences et les taches.</p>
                    </div>
                    <span class="badge text-bg-{{ $score['badge'] }}">{{ $score['label'] }}</span>
                </div>

                <div class="display-5 fw-semibold mb-2">{{ $score['score'] }}/100</div>
                <div class="progress mb-4" style="height: 10px;">
                    <div class="progress-bar bg-{{ $score['badge'] }}" style="width: {{ $score['score'] }}%"></div>
                </div>

                <div class="row g-2">
                    <div class="col-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-semibold">{{ $score['presence'] }}/40</div>
                            <small class="text-muted">Presence</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-semibold">{{ $score['tasks'] }}/40</div>
                            <small class="text-muted">Taches</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-semibold">{{ $score['deadlines'] }}/20</div>
                            <small class="text-muted">Delais</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7 fade-in">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Alertes intelligentes</h2>
                @forelse($alerts as $alert)
                    <div class="alert alert-warning alert-dismissible fade show py-2 pe-5 mb-2" role="alert">
                        <div>{{ $alert['message'] }}</div>
                        @isset($alert['task'])
                            <small class="text-muted">Tache : {{ $alert['task']->title }}</small>
                        @endisset
                        <button type="button" class="btn-close py-3" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune alerte detectee pour ce stagiaire.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5 fade-in">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Informations</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">CIN</dt>
                    <dd class="col-sm-8">{{ $intern->cin }}</dd>
                    <dt class="col-sm-4">Telephone</dt>
                    <dd class="col-sm-8">{{ $intern->phone ?? '-' }}</dd>
                    <dt class="col-sm-4">Periode</dt>
                    <dd class="col-sm-8">{{ $intern->start_date?->format('d/m/Y') }} - {{ $intern->end_date?->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Encadrant</dt>
                    <dd class="col-sm-8">{{ $supervisors->isNotEmpty() ? $supervisors->join(', ') : '-' }}</dd>
                    <dt class="col-sm-4">Absences</dt>
                    <dd class="col-sm-8">{{ $intern->absenceCount() }}</dd>
                    <dt class="col-sm-4">Etat</dt>
                    <dd class="col-sm-8">
                        <span class="badge {{ $intern->is_archived ? 'text-bg-secondary' : 'text-bg-success' }}">
                            {{ $intern->is_archived ? 'Archive' : 'Actif' }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    @if($canViewInternTasks)
        <div class="col-lg-7 fade-in">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Taches du stagiaire</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Date limite</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                    <tr>
                                        <td>{{ $task->title }}</td>
                                        <td>{{ $task->due_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td><span class="badge text-bg-secondary">{{ $task->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted">Aucune tache liee au stagiaire.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
