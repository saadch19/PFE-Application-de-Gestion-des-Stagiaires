@extends('layouts.app')

@section('title', 'Detail stagiaire')

@section('content')
@php
    $canViewInternTasks = ! auth()->user()->hasRole('Responsable de competence', 'Responsable RH');
    $isSupervisor = auth()->user()->hasRole('Encadrant');
    $isRh = auth()->user()->hasRole('Responsable RH');
    $supervisors = $intern->internships
        ->pluck('supervisor')
        ->filter()
        ->unique('id')
        ->pluck('full_name')
        ->values();
    $latestInternship = $intern->internships->sortByDesc('end_date')->first();
    $attestationRequest = $intern->requests
        ->where('type', 'attestation')
        ->sortByDesc('created_at')
        ->first();
    $duration = $intern->start_date && $intern->end_date
        ? $intern->start_date->diffInDays($intern->end_date) + 1 . ' jours'
        : '-';
    $stageStatus = $intern->is_archived
        ? 'archive'
        : ($latestInternship?->status ?? ($latestInternship ? 'en_cours' : 'en_attente'));
    $historyItems = collect([
        ['label' => 'Rapport envoyé', 'date' => $attestationRequest?->created_at, 'show' => $attestationRequest?->report_path !== null],
        ['label' => 'Validé par encadrant', 'date' => $attestationRequest?->supervisor_validated_at, 'show' => $attestationRequest?->supervisor_validated_at !== null],
        ['label' => 'Validé par RC', 'date' => $attestationRequest?->rc_validated_at, 'show' => $attestationRequest?->rc_validated_at !== null],
        ['label' => 'Transmis au RH', 'date' => $attestationRequest?->sent_to_rh_at, 'show' => $attestationRequest?->sent_to_rh_at !== null],
        ['label' => 'Attestation générée', 'date' => $attestationRequest?->rh_processed_at, 'show' => $attestationRequest?->rh_processed_at !== null],
        ['label' => 'Attestation imprimée', 'date' => $attestationRequest?->attestation_printed_at, 'show' => $attestationRequest?->attestation_printed_at !== null],
        ['label' => 'Attestation récupérée', 'date' => $attestationRequest?->attestation_recovered_at, 'show' => $attestationRequest?->attestation_recovered_at !== null],
        ['label' => 'Dossier archivé', 'date' => $attestationRequest?->attestation_archived_at, 'show' => $attestationRequest?->attestation_archived_at !== null],
    ])->where('show', true);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 fade-in">
    <div>
        <h1 class="h3 mb-1">{{ $intern->user?->full_name ?? 'Stagiaire non lie' }}</h1>
        <p class="text-muted mb-0">{{ $intern->school }} - {{ $intern->specialty }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $isSupervisor ? route('supervisor.interns') : route('interns.index') }}" class="btn btn-outline-secondary btn-sm">Retour</a>
        @unless($isSupervisor || $isRh)
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
                        <p class="text-muted mb-0">Évaluation calculée depuis les absences et les tâches.</p>
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
                            <small class="text-muted">Tâches</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="fw-semibold">{{ $score['deadlines'] }}/20</div>
                            <small class="text-muted">Délais</small>
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
                            <small class="text-muted">Tâche : {{ $alert['task']->title }}</small>
                        @endisset
                        <button type="button" class="btn-close py-3" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @empty
                    <p class="text-muted mb-0">Aucune alerte détectée pour ce stagiaire.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card card-soft fade-in">
    <div class="card-body">
        <ul class="nav nav-tabs flex-nowrap overflow-auto mb-4" id="internDetailsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active text-nowrap" id="infos-tab" data-bs-toggle="tab" data-bs-target="#infos-pane" type="button" role="tab">Informations</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap" id="stage-tab" data-bs-toggle="tab" data-bs-target="#stage-pane" type="button" role="tab">Stage</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap" id="team-tab" data-bs-toggle="tab" data-bs-target="#team-pane" type="button" role="tab">Encadrant / RC</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap" id="absences-tab" data-bs-toggle="tab" data-bs-target="#absences-pane" type="button" role="tab">Absences</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap" id="report-tab" data-bs-toggle="tab" data-bs-target="#report-pane" type="button" role="tab">Rapport</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap" id="attestation-tab" data-bs-toggle="tab" data-bs-target="#attestation-pane" type="button" role="tab">Attestation</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab">Historique</button>
            </li>
        </ul>

        <div class="tab-content" id="internDetailsTabsContent">
            <div class="tab-pane fade show active" id="infos-pane" role="tabpanel" aria-labelledby="infos-tab" tabindex="0">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nom</dt>
                    <dd class="col-sm-9">{{ $intern->user?->full_name ?? '-' }}</dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $intern->user?->email ?? '-' }}</dd>
                    <dt class="col-sm-3">Téléphone</dt>
                    <dd class="col-sm-9">{{ $intern->phone ?? '-' }}</dd>
                    <dt class="col-sm-3">CIN</dt>
                    <dd class="col-sm-9">{{ $intern->cin }}</dd>
                    <dt class="col-sm-3">École</dt>
                    <dd class="col-sm-9">{{ $intern->school }}</dd>
                    <dt class="col-sm-3">Spécialité</dt>
                    <dd class="col-sm-9">{{ $intern->specialty }}</dd>
                    <dt class="col-sm-3">État</dt>
                    <dd class="col-sm-9">@statusBadge($intern->is_archived ? 'archive' : 'en_cours')</dd>
                </dl>
            </div>

            <div class="tab-pane fade" id="stage-pane" role="tabpanel" aria-labelledby="stage-tab" tabindex="0">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Sujet</dt>
                    <dd class="col-sm-9">{{ $latestInternship?->title ?? '-' }}</dd>
                    <dt class="col-sm-3">Département</dt>
                    <dd class="col-sm-9">{{ $latestInternship?->department ?? '-' }}</dd>
                    <dt class="col-sm-3">Date début / fin</dt>
                    <dd class="col-sm-9">{{ $intern->start_date?->format('d/m/Y') ?? '-' }} - {{ $intern->end_date?->format('d/m/Y') ?? '-' }}</dd>
                    <dt class="col-sm-3">Durée</dt>
                    <dd class="col-sm-9">{{ $duration }}</dd>
                    <dt class="col-sm-3">Statut</dt>
                    <dd class="col-sm-9">@statusBadge($stageStatus)</dd>
                </dl>
            </div>

            <div class="tab-pane fade" id="team-pane" role="tabpanel" aria-labelledby="team-tab" tabindex="0">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Encadrant</dt>
                    <dd class="col-sm-9">{{ $supervisors->isNotEmpty() ? $supervisors->join(', ') : '-' }}</dd>
                    <dt class="col-sm-3">Responsable compétence</dt>
                    <dd class="col-sm-9">{{ $latestInternship?->responsible?->full_name ?? '-' }}</dd>
                    <dt class="col-sm-3">Validation encadrant</dt>
                    <dd class="col-sm-9">{{ $attestationRequest?->supervisor_validated_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                    <dt class="col-sm-3">Validation RC</dt>
                    <dd class="col-sm-9">{{ $attestationRequest?->rc_validated_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                </dl>
            </div>

            <div class="tab-pane fade" id="absences-pane" role="tabpanel" aria-labelledby="absences-tab" tabindex="0">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge text-bg-secondary">Total : {{ $intern->absences->count() }}</span>
                    <span class="badge text-bg-danger">Non justifiées : {{ $intern->absences->where('justified', false)->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Motif</th>
                                <th>Statut</th>
                                <th>Ajouté par</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($intern->absences->sortByDesc('date_absence') as $absence)
                                <tr>
                                    <td>{{ $absence->date_absence?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $absence->reason ?? '-' }}</td>
                                    <td>@statusBadge($absence->justified ? 'valide' : 'en_attente')</td>
                                    <td>{{ $absence->recordedBy?->full_name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">Aucune absence enregistrée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="report-pane" role="tabpanel" aria-labelledby="report-tab" tabindex="0">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Rapport PDF</dt>
                    <dd class="col-sm-9">
                        @if($attestationRequest?->report_path)
                            <span>{{ $attestationRequest->report_original_name ?? 'rapport-stage.pdf' }}</span>
                            @unless($isRh)
                                <a href="{{ route('requests.report', $attestationRequest) }}" class="btn btn-sm btn-outline-primary ms-2">Télécharger</a>
                            @endunless
                        @else
                            -
                        @endif
                    </dd>
                    <dt class="col-sm-3">Validation encadrant</dt>
                    <dd class="col-sm-9">{{ $attestationRequest?->supervisor_validated_at ? 'Validé le ' . $attestationRequest->supervisor_validated_at->format('d/m/Y H:i') : 'En attente' }}</dd>
                    <dt class="col-sm-3">Validation RC</dt>
                    <dd class="col-sm-9">{{ $attestationRequest?->rc_validated_at ? 'Validé le ' . $attestationRequest->rc_validated_at->format('d/m/Y H:i') : 'En attente' }}</dd>
                    <dt class="col-sm-3">Note encadrant</dt>
                    <dd class="col-sm-9">{{ $attestationRequest?->supervisor_grade !== null ? $attestationRequest->supervisor_grade . '/20' : '-' }}</dd>
                </dl>
            </div>

            <div class="tab-pane fade" id="attestation-pane" role="tabpanel" aria-labelledby="attestation-tab" tabindex="0">
                @if($attestationRequest)
                    @include('partials.attestation-timeline', ['requestItem' => $attestationRequest])
                @endif
                <dl class="row mb-0 mt-3">
                    <dt class="col-sm-3">État actuel</dt>
                    <dd class="col-sm-9">@statusBadge($attestationRequest?->workflow_status ?? 'en_attente')</dd>
                    <dt class="col-sm-3">Note encadrant</dt>
                    <dd class="col-sm-9">{{ $attestationRequest?->supervisor_grade !== null ? $attestationRequest->supervisor_grade . '/20' : '-' }}</dd>
                    <dt class="col-sm-3">Date génération</dt>
                    <dd class="col-sm-9">{{ $attestationRequest?->rh_processed_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                    <dt class="col-sm-3">Date récupération</dt>
                    <dd class="col-sm-9">{{ $attestationRequest?->attestation_recovered_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                </dl>
            </div>

            <div class="tab-pane fade" id="history-pane" role="tabpanel" aria-labelledby="history-tab" tabindex="0">
                @if($attestationRequest)
                    @include('partials.attestation-timeline', ['requestItem' => $attestationRequest])
                @endif

                <div class="list-group list-group-flush mt-3">
                    @forelse($historyItems as $item)
                        <div class="list-group-item px-0 d-flex justify-content-between gap-3">
                            <span>{{ $item['label'] }}</span>
                            <span class="text-muted text-nowrap">{{ $item['date']?->format('d/m/Y H:i') }}</span>
                        </div>
                    @empty
                        <div class="text-muted">Aucun historique d attestation pour le moment.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@if($canViewInternTasks)
    <div class="card card-soft mt-4 fade-in">
        <div class="card-body">
            <h2 class="h5 mb-3">Tâches du stagiaire</h2>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
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
                                <td>@statusBadge($task->status)</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">Aucune tâche liée au stagiaire.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
