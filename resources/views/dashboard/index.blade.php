@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h1 class="h3 mb-1">Tableau de bord</h1>
        <p class="text-muted mb-0">Vue d'ensemble de l'activite des stages.</p>
    </div>
</div>

@if(!empty($statCards))
    <div class="row g-3 mb-4">
        @foreach($statCards as $card)
            <div class="col-sm-6 col-lg-3 fade-in">
                <div class="card card-soft stat-card h-100">
                    <div class="card-body">
                        <div class="text-muted">{{ $card['label'] }}</div>
                        <div class="display-6 fw-semibold">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if($evaluatedInterns->count() > 0)
    <div class="row g-4 mb-4">
        <div class="col-lg-7 fade-in">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Scores automatiques des stagiaires</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Stagiaire</th>
                                    <th>Score</th>
                                    <th>Statut</th>
                                    <th class="text-end">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($evaluatedInterns as $intern)
                                    @php $score = $intern->performanceScore(); @endphp
                                    <tr>
                                        <td>{{ $intern->user?->full_name ?? 'Non lie' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $score['score'] }}/100</div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $score['badge'] }}" style="width: {{ $score['score'] }}%"></div>
                                            </div>
                                        </td>
                                        <td><span class="badge text-bg-{{ $score['badge'] }}">{{ $score['label'] }}</span></td>
                                        <td class="text-end">
                                            @if(auth()->user()->hasRole('Administrateur', 'Responsable RH', 'Responsable de competence'))
                                                <a href="{{ route('interns.show', $intern) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                            @elseif(auth()->user()->hasRole('Encadrant'))
                                                <a href="{{ route('supervisor.interns.show', $intern) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($evaluatedInterns->hasPages())
                        <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
                            <a
                                href="{{ $evaluatedInterns->previousPageUrl() ?? '#' }}"
                                class="btn btn-sm btn-outline-secondary {{ $evaluatedInterns->onFirstPage() ? 'disabled' : '' }}"
                                @if($evaluatedInterns->onFirstPage()) aria-disabled="true" @endif
                            >
                                Precedent
                            </a>
                            <span class="text-muted small">
                                Page {{ $evaluatedInterns->currentPage() }} / {{ $evaluatedInterns->lastPage() }}
                            </span>
                            <a
                                href="{{ $evaluatedInterns->nextPageUrl() ?? '#' }}"
                                class="btn btn-sm btn-outline-secondary {{ $evaluatedInterns->hasMorePages() ? '' : 'disabled' }}"
                                @if(! $evaluatedInterns->hasMorePages()) aria-disabled="true" @endif
                            >
                                Suivant
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5 fade-in">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Alertes intelligentes</h2>
                    @forelse($smartAlerts as $item)
                        <div class="alert alert-warning alert-dismissible fade show py-2 pe-5 mb-2" role="alert">
                            <div class="fw-semibold">{{ $item['intern']->user?->full_name ?? 'Stagiaire non lie' }}</div>
                            <div>{{ $item['alert']['message'] }}</div>
                            @if($canViewTasks && isset($item['alert']['task']))
                                <small class="text-muted">Tache : {{ $item['alert']['task']->title }}</small>
                            @endif
                            <button type="button" class="btn-close py-3" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune alerte detectee pour le moment.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row g-4">
    @if($canViewTasks)
        <div class="col-lg-6 fade-in">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Dernieres taches</h2>
                    <div class="d-grid gap-2">
                        @forelse($latestTasks as $task)
                            <div class="border-bottom pb-2">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-break">{{ $task->title }}</div>
                                        <small class="text-muted">Assignee a : {{ $task->assignedTo?->full_name ?? '-' }}</small>
                                    </div>
                                    <span class="flex-shrink-0">@statusBadge($task->status)</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Aucune tache pour le moment.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="col-lg-6 fade-in">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Dernieres demandes</h2>
                <div class="d-grid gap-2">
                    @forelse($latestRequests as $requestItem)
                        <div class="border-bottom pb-2">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-break">{{ $requestItem->intern->user?->full_name ?? 'Non lie' }}</div>
                                    <small class="text-muted">Type : {{ $requestItem->type }}</small>
                                </div>
                                <span class="flex-shrink-0">@statusBadge($requestItem->status)</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune demande recente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
