@extends('layouts.app')

@section('title', 'Stages')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Gestion des stages</h1>
            <a href="{{ route('internships.create') }}" class="btn btn-success btn-sm">Nouveau stage</a>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <select class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="planifie" @selected($status === 'planifie')>Planifie</option>
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
                        <th>Stagiaire</th>
                        <th>Encadrant</th>
                        <th>Periode</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($internships as $internship)
                        <tr>
                            <td>
                                <div>{{ $internship->title }}</div>
                                <small class="text-muted">{{ $internship->department }}</small>
                            </td>
                            <td>{{ $internship->intern->user?->full_name ?? $internship->intern->cin }}</td>
                            <td>{{ $internship->supervisor?->full_name ?? 'Non assigne' }}</td>
                            <td>{{ $internship->start_date?->format('d/m/Y') }} - {{ $internship->end_date?->format('d/m/Y') }}</td>
                            <td>
                                <select class="form-select form-select-sm internship-status" data-url="{{ route('internships.status', $internship) }}">
                                    @foreach(['planifie' => 'Planifie', 'en_cours' => 'En cours', 'termine' => 'Termine'] as $key => $label)
                                        <option value="{{ $key }}" @selected($internship->status === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('internships.edit', $internship) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                <form action="{{ route('internships.destroy', $internship) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce stage ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun stage.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $internships->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('.internship-status').on('change', function () {
            const $select = $(this);

            $.ajax({
                url: $select.data('url'),
                method: 'PATCH',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: $select.val()
                }
            }).fail(function () {
                alert('Erreur lors de la mise a jour du statut.');
            });
        });
    });
</script>
@endpush
