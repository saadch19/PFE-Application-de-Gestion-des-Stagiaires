@extends('layouts.app')

@section('title', 'Taches')

@section('content')
@php
    $canManage = auth()->user()->hasRole('Administrateur', 'Encadrant');
    $view = (string) request()->query('view', 'table');
    $isKanban = $view === 'kanban';
    $taskColumns = [
        'a_faire' => 'A faire',
        'en_cours' => 'En cours',
        'termine' => 'Termine',
    ];
    $tasksByStatus = $tasks->getCollection()->groupBy('status');
    $isEncadrant = auth()->user()->hasRole('Encadrant');
    $isIntern = auth()->user()->hasRole('Stagiaire');
    $myTasksOnly = $myTasksOnly ?? true;
@endphp

<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h1 class="h4 mb-0">Gestion des taches</h1>
                <p class="text-muted mb-0">Vue {{ $isKanban ? 'kanban' : 'liste' }} des taches.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="btn-group" role="group" aria-label="Changer la vue">
                    <a class="btn btn-outline-secondary btn-sm {{ $isKanban ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['view' => 'kanban']) }}">Kanban</a>
                    <a class="btn btn-outline-secondary btn-sm {{ ! $isKanban ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}">Table</a>
                </div>
                @if($canManage)
                    <a href="{{ route('tasks.create') }}" class="btn btn-success btn-sm">Nouvelle tache</a>
                @endif
            </div>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="view" value="{{ $view }}">
            @if($isEncadrant)
                <div class="col-md-5">
                    <select class="form-select" name="internship_id">
                        <option value="">Tous les stages</option>
                        @foreach($internships ?? [] as $internship)
                            <option value="{{ $internship->id }}" @selected((string) $internshipId === (string) $internship->id)>
                                {{ $internship->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="col-md-4">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="a_faire" @selected($status === 'a_faire')>A faire</option>
                        <option value="en_cours" @selected($status === 'en_cours')>En cours</option>
                        <option value="termine" @selected($status === 'termine')>Termine</option>
                    </select>
                </div>
            @endif
            @if($isIntern)
                <div class="col-md-4">
                    <div class="form-check mt-2">
                        <input type="hidden" name="my_tasks" value="0">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="my_tasks"
                            name="my_tasks"
                            value="1"
                            @checked($myTasksOnly)
                            onchange="this.form.submit()"
                        >
                        <label class="form-check-label" for="my_tasks">Afficher uniquement mes taches</label>
                    </div>
                </div>
            @endif
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" type="submit">Filtrer</button>
            </div>
        </form>

        @if($isKanban)
            <div class="kanban-board">
                <div class="row g-3">
                    @foreach($taskColumns as $key => $label)
                        @php $columnTasks = $tasksByStatus->get($key, collect()); @endphp
                        <div class="col-12 col-lg-4">
                            <div class="kanban-column" data-status="{{ $key }}">
                                <div class="kanban-header">
                                    <div class="fw-semibold">{{ $label }}</div>
                                    <span class="badge text-bg-secondary">{{ $columnTasks->count() }}</span>
                                </div>
                                <div class="kanban-body">
                                    @forelse($columnTasks as $task)
                                        @php $isOwner = (string) $task->assigned_to === (string) auth()->id(); @endphp
                                        <div
                                            class="card kanban-card"
                                            data-task-id="{{ $task->id }}"
                                            data-owner-id="{{ $task->assigned_to }}"
                                            data-status="{{ $task->status }}"
                                            data-update-url="{{ route('tasks.status', $task) }}"
                                            @if($isIntern && $isOwner) draggable="true" @endif
                                        >
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div class="fw-semibold">{{ $task->title }}</div>
                                                    <span class="badge text-bg-light">{{ $task->internship?->title ?? '-' }}</span>
                                                </div>
                                                <div class="small text-muted mt-2">Assignee a : {{ $task->assignedTo?->full_name ?? '-' }}</div>
                                                <div class="small text-muted">Date limite : {{ $task->due_date?->format('d/m/Y') ?? '-' }}</div>
                                                @if(! $isIntern)
                                                    <div class="mt-2">
                                                        <select class="form-select form-select-sm task-status" data-url="{{ route('tasks.status', $task) }}">
                                                            @foreach($taskColumns as $statusKey => $statusLabel)
                                                                <option value="{{ $statusKey }}" @selected($task->status === $statusKey)>{{ $statusLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                @if($canManage)
                                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="m-0" onsubmit="return confirm('Supprimer cette tache ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                                        </form>
                                                    </div>
                                                @endif
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
                            <th>Stage</th>
                            <th>Assignee par</th>
                            <th>Assignee a</th>
                            <th>Date limite</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>{{ $task->title }}</td>
                                <td>{{ $task->internship?->title ?? '-' }}</td>
                                <td>{{ $task->assignedBy?->full_name }}</td>
                                <td>{{ $task->assignedTo?->full_name }}</td>
                                <td>{{ $task->due_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <select class="form-select form-select-sm task-status" data-url="{{ route('tasks.status', $task) }}">
                                        @foreach(['a_faire' => 'A faire', 'en_cours' => 'En cours', 'termine' => 'Termine'] as $key => $label)
                                            <option value="{{ $key }}" @selected($task->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end">
                                    @if($canManage)
                                        <div class="d-inline-flex align-items-center justify-content-end gap-1 flex-nowrap">
                                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="m-0" onsubmit="return confirm('Supprimer cette tache ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger text-nowrap" type="submit">Supprimer</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Aucune tache.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{ $tasks->links() }}
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

        const isIntern = {{ $isIntern ? 'true' : 'false' }};
        const userId = '{{ auth()->id() }}';

        if (isIntern) {
            $('.kanban-card[draggable="true"]').on('dragstart', function (event) {
                const $card = $(this);
                event.originalEvent.dataTransfer.setData('text/task-id', $card.data('task-id'));
            });

            $('.kanban-column').on('dragover', function (event) {
                event.preventDefault();
                $(this).addClass('kanban-drop-target');
            });

            $('.kanban-column').on('dragleave', function () {
                $(this).removeClass('kanban-drop-target');
            });

            $('.kanban-column').on('drop', function (event) {
                event.preventDefault();
                const $column = $(this);
                const taskId = event.originalEvent.dataTransfer.getData('text/task-id');
                const targetStatus = $column.data('status');
                const $card = $('.kanban-card[data-task-id="' + taskId + '"]');

                $column.removeClass('kanban-drop-target');

                if (! taskId || ! targetStatus || $card.length === 0) {
                    return;
                }

                if (String($card.data('owner-id')) !== String(userId)) {
                    alert('Vous ne pouvez pas modifier cette tache.');
                    return;
                }

                $.ajax({
                    url: $card.data('update-url'),
                    method: 'PATCH',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        status: targetStatus
                    }
                }).done(function () {
                    $card.attr('data-status', targetStatus);
                    $card.appendTo($column.find('.kanban-body'));
                }).fail(function (xhr) {
                    const message = xhr.status === 403
                        ? 'Vous n\'etes pas autorise a modifier cette tache.'
                        : 'Erreur lors de la mise a jour du statut.';

                    alert(message);
                });
            });
        }
    });
</script>
@endpush

@push('scripts')
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

    .kanban-card[draggable="true"] {
        cursor: grab;
    }

    .kanban-drop-target {
        outline: 2px dashed rgba(15, 23, 42, 0.2);
        outline-offset: 4px;
    }
</style>
@endpush
