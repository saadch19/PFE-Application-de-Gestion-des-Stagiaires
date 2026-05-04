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

@if($evaluatedInterns->isNotEmpty())
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
                                            @if(auth()->user()->hasRole('Administrateur', 'Responsable de competence'))
                                                <a href="{{ route('interns.show', $intern) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 fade-in">
            <div class="card card-soft h-100">
                <div class="card-body">
                    <h2 class="h5 mb-3">Alertes intelligentes</h2>
                    @forelse($smartAlerts as $item)
                        <div class="alert alert-warning py-2 mb-2">
                            <div class="fw-semibold">{{ $item['intern']->user?->full_name ?? 'Stagiaire non lie' }}</div>
                            <div>{{ $item['alert']['message'] }}</div>
                            @isset($item['alert']['task'])
                                <small class="text-muted">Tache : {{ $item['alert']['task']->title }}</small>
                            @endisset
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
    <div class="col-lg-6 fade-in">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Dernieres taches</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Assignee a</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestTasks as $task)
                                <tr>
                                    <td>{{ $task->title }}</td>
                                    <td>{{ $task->assignedTo?->full_name }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $task->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Aucune tache pour le moment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 fade-in">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Dernieres demandes</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Stagiaire</th>
                                <th>Type</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestRequests as $requestItem)
                                <tr>
                                    <td>{{ $requestItem->intern->user?->full_name ?? 'Non lie' }}</td>
                                    <td>{{ $requestItem->type }}</td>
                                    <td><span class="badge text-bg-info">{{ $requestItem->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Aucune demande recente.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
