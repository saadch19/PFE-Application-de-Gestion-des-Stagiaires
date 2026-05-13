@extends('layouts.app')

@section('title', 'Details du stage')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 fade-in">
    <div>
        <h1 class="h4 mb-1">Details du stage</h1>
        <p class="text-muted mb-0">Vue complete du stage et de ses taches.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('supervisor.internships') }}" class="btn btn-outline-secondary">Retour</a>
        @if($internship->status !== 'termine')
            <form action="{{ route('supervisor.internships.validate', $internship) }}" method="POST" onsubmit="return confirm('Valider la fin de ce stage ?')">
                @csrf
                @method('PATCH')
                <button class="btn btn-success" type="submit">Valider fin de stage</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7 fade-in">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Informations du stage</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted">Projet</div>
                        <div class="fw-semibold">{{ $internship->title }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Departement</div>
                        <div class="fw-semibold">{{ $internship->department }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Periode</div>
                        <div class="fw-semibold">
                            {{ $internship->start_date?->format('d/m/Y') }} - {{ $internship->end_date?->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted">Statut</div>
                        <span class="badge text-bg-{{ $internship->status === 'termine' ? 'secondary' : ($internship->status === 'en_cours' ? 'success' : 'info') }}">
                            {{ str_replace('_', ' ', $internship->status) }}
                        </span>
                    </div>
                    <div class="col-12">
                        <div class="text-muted">Stagiaires</div>
                        <div class="fw-semibold">
                            {{ $internship->interns->map(fn ($intern) => $intern->user?->full_name ?? $intern->cin)->join(', ') }}
                        </div>
                    </div>
                    @if($internship->description)
                        <div class="col-12">
                            <div class="text-muted">Description</div>
                            <div>{{ $internship->description }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 fade-in">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h2 class="h5 mb-3">Statistiques des taches</h2>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="text-muted">Total</div>
                        <div class="display-6 fw-semibold">{{ $taskStats['totalTasks'] }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Terminees</div>
                        <div class="display-6 fw-semibold">{{ $taskStats['doneTasks'] }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">En cours</div>
                        <div class="display-6 fw-semibold">{{ $taskStats['openTasks'] }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">En retard</div>
                        <div class="display-6 fw-semibold">{{ $taskStats['overdueTasks'] }}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-muted">Progression</div>
                    @php
                        $progress = $taskStats['totalTasks'] > 0
                            ? (int) round(($taskStats['doneTasks'] / $taskStats['totalTasks']) * 100)
                            : 0;
                    @endphp
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="small text-muted mt-2">{{ $progress }}% termine</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft fade-in">
    <div class="card-body">
        <h2 class="h5 mb-3">Liste des taches</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Assignee a</th>
                        <th>Statut</th>
                        <th>Date limite</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->assignedTo?->full_name ?? '-' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $task->status === 'termine' ? 'secondary' : ($task->status === 'en_cours' ? 'success' : 'info') }}">
                                    {{ str_replace('_', ' ', $task->status) }}
                                </span>
                            </td>
                            <td>{{ $task->due_date?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucune tache associee.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
