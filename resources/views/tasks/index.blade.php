@extends('layouts.app')

@section('title', 'Taches')

@section('content')
@php $canManage = auth()->user()->hasRole('Administrateur', 'Responsable de competence', 'Encadrant'); @endphp

<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Gestion des taches</h1>
            @if($canManage)
                <a href="{{ route('tasks.create') }}" class="btn btn-success btn-sm">Nouvelle tache</a>
            @endif
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <select class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="a_faire" @selected($status === 'a_faire')>A faire</option>
                    <option value="en_cours" @selected($status === 'en_cours')>En cours</option>
                    <option value="termine" @selected($status === 'termine')>Termine</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" type="submit">Filtrer</button>
            </div>
        </form>

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
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette tache ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Aucune tache.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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
    });
</script>
@endpush
