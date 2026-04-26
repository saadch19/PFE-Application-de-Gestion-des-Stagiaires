@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h1 class="h3 mb-1">Tableau de bord</h1>
        <p class="text-muted mb-0">Vue d'ensemble de l'activite des stages.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3 fade-in">
        <div class="card card-soft stat-card h-100">
            <div class="card-body">
                <div class="text-muted">Utilisateurs</div>
                <div class="display-6 fw-semibold">{{ $stats['users'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 fade-in">
        <div class="card card-soft stat-card h-100">
            <div class="card-body">
                <div class="text-muted">Stagiaires</div>
                <div class="display-6 fw-semibold">{{ $stats['interns'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 fade-in">
        <div class="card card-soft stat-card h-100">
            <div class="card-body">
                <div class="text-muted">Stages en cours</div>
                <div class="display-6 fw-semibold">{{ $stats['active_internships'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 fade-in">
        <div class="card card-soft stat-card h-100">
            <div class="card-body">
                <div class="text-muted">Demandes en attente</div>
                <div class="display-6 fw-semibold">{{ $stats['pending_requests'] }}</div>
            </div>
        </div>
    </div>
</div>

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
