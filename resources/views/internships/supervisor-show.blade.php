@extends('layouts.app')

@section('title', 'Consulter stage')

@section('content')
@php
    $view = (string) request()->query('view', 'kanban');
    $isKanban = $view === 'kanban';
    $taskColumns = [
        'a_faire' => 'A faire',
        'en_cours' => 'En cours',
        'termine' => 'Termine',
    ];
    $tasksByStatus = $tasks->groupBy('status');
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 fade-in">
    <div>
        <h1 class="h4 mb-1">Consulter le stage</h1>
        <p class="text-muted mb-0">Informations generales du stage.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <form action="{{ route('supervisor.internships.validate', $internship) }}" method="POST" class="d-flex flex-wrap gap-2" onsubmit="return confirm('Enregistrer la note ?')">
            @csrf
            @method('PATCH')
            <div>
                <label class="form-label mb-0 small" for="grade">Note (/20)</label>
                <input type="number" class="form-control form-control-sm" id="grade" name="grade" min="0" max="20" step="0.5" value="{{ old('grade', $internship->grade ?? '') }}" required>
            </div>
            @if($internship->status !== 'termine')
                <button class="btn btn-success btn-sm" type="submit">Valider fin de stage</button>
            @else
                <button class="btn btn-outline-primary btn-sm" type="submit">Enregistrer la note</button>
            @endif
        </form>
        @if($internship->status === 'termine')
            <form action="{{ route('supervisor.internships.undo', $internship) }}" method="POST" onsubmit="return confirm('Annuler la validation de fin de stage ?')">
                @csrf
                @method('PATCH')
                <button class="btn btn-outline-danger btn-sm" type="submit">Annuler validation</button>
            </form>
        @endif
        <div class="btn-group" role="group" aria-label="Changer la vue">
            <a class="btn btn-outline-secondary btn-sm {{ $isKanban ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['view' => 'kanban']) }}">Kanban</a>
            <a class="btn btn-outline-secondary btn-sm {{ ! $isKanban ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}">Table</a>
        </div>
        <a href="{{ route('supervisor.internships') }}" class="btn btn-outline-secondary btn-sm">Retour</a>
    </div>
</div>

<div class="card card-soft fade-in">
    <div class="card-body">
        <h2 class="h5 mb-3">Informations du stage</h2>
        <dl class="row mb-0">
            <dt class="col-sm-3">Titre</dt>
            <dd class="col-sm-9">{{ $internship->title }}</dd>

            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9">{{ $internship->description ?: '-' }}</dd>

            <dt class="col-sm-3">Departement</dt>
            <dd class="col-sm-9">{{ $internship->department }}</dd>

            <dt class="col-sm-3">Date debut</dt>
            <dd class="col-sm-9">{{ $internship->start_date?->format('d/m/Y') ?? '-' }}</dd>

            <dt class="col-sm-3">Date fin</dt>
            <dd class="col-sm-9">{{ $internship->end_date?->format('d/m/Y') ?? '-' }}</dd>

            <dt class="col-sm-3">Statut</dt>
            <dd class="col-sm-9">
                <span class="badge text-bg-{{ $internship->status === 'termine' ? 'secondary' : ($internship->status === 'en_cours' ? 'success' : 'info') }}">
                    {{ str_replace('_', ' ', $internship->status) }}
                </span>
            </dd>

            <dt class="col-sm-3">Note</dt>
            <dd class="col-sm-9">
                {{ $internship->grade !== null ? number_format($internship->grade, 1) . ' / 20' : '-' }}
            </dd>

            <dt class="col-sm-3">Stagiaires</dt>
            <dd class="col-sm-9 mb-0">
                @forelse($internship->interns as $intern)
                    <a
                        href="{{ route('supervisor.interns.show', $intern) }}"
                        class="btn btn-sm btn-outline-success me-1 mb-1"
                    >
                        {{ $intern->user?->full_name ?? $intern->cin }}
                    </a>
                @empty
                    <span class="text-muted">Aucun stagiaire lie</span>
                @endforelse
            </dd>
        </dl>
    </div>
</div>

<div class="card card-soft fade-in mt-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">Taches du stage</h2>
                <p class="text-muted mb-0">Suivi par statut.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge text-bg-secondary">Total: {{ $taskStats['totalTasks'] }}</span>
                <span class="badge text-bg-success">Terminees: {{ $taskStats['doneTasks'] }}</span>
                <span class="badge text-bg-warning">Ouvertes: {{ $taskStats['openTasks'] }}</span>
                <span class="badge text-bg-danger">En retard: {{ $taskStats['overdueTasks'] }}</span>
            </div>
        </div>

        @if($isKanban)
            <div class="kanban-board">
                <div class="row g-3">
                    @foreach($taskColumns as $key => $label)
                        @php $columnTasks = $tasksByStatus->get($key, collect()); @endphp
                        <div class="col-12 col-lg-4">
                            <div class="kanban-column">
                                <div class="kanban-header">
                                    <div class="fw-semibold">{{ $label }}</div>
                                    <span class="badge text-bg-secondary">{{ $columnTasks->count() }}</span>
                                </div>
                                <div class="kanban-body">
                                    @forelse($columnTasks as $task)
                                        <div class="card kanban-card">
                                            <div class="card-body">
                                                <div class="fw-semibold">{{ $task->title }}</div>
                                                <div class="small text-muted mt-2">Assignee a : {{ $task->assignedTo?->full_name ?? '-' }}</div>
                                                <div class="small text-muted">Date limite : {{ $task->due_date?->format('d/m/Y') ?? '-' }}</div>
                                                <div class="mt-2">
                                                    <select class="form-select form-select-sm task-status" data-url="{{ route('tasks.status', $task) }}">
                                                        @foreach($taskColumns as $statusKey => $statusLabel)
                                                            <option value="{{ $statusKey }}" @selected($task->status === $statusKey)>{{ $statusLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted small">Aucune tache.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Assignee a</th>
                            <th>Date limite</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>{{ $task->title }}</td>
                                <td>{{ $task->assignedTo?->full_name ?? '-' }}</td>
                                <td>{{ $task->due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <select class="form-select form-select-sm task-status" data-url="{{ route('tasks.status', $task) }}">
                                        @foreach($taskColumns as $statusKey => $statusLabel)
                                            <option value="{{ $statusKey }}" @selected($task->status === $statusKey)>{{ $statusLabel }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Aucune tache.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('.task-status').on('change', function () {
            const $select = $(this);

            $.ajax({
                url: $select.data('url'),
                method: 'PATCH',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: $select.val()
                }
            }).fail(function (xhr) {
                const message = xhr.status === 403
                    ? 'Vous n\'etes pas autorise a modifier cette tache.'
                    : 'Erreur lors de la mise a jour du statut.';

                alert(message);
            });
        });
    });
</script>
<style>
    .kanban-board {
        padding: 0.25rem;
    }

    .kanban-column {
        background: rgba(248, 250, 252, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.3);
        border-radius: 1rem;
        padding: 1rem;
        min-height: 100%;
    }

    .kanban-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .kanban-body {
        display: grid;
        gap: 0.75rem;
    }

    .kanban-card {
        border-radius: 0.85rem;
        border: 1px solid rgba(148, 163, 184, 0.25);
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
    }
</style>
@endpush
