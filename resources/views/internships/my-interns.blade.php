@extends('layouts.app')

@section('title', 'Mes stagiaires')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">Mes stagiaires</h1>
                <p class="text-muted mb-0">Stagiaires affectes a vous avec leurs projets et periodes de stage.</p>
            </div>
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
                        <th>Stagiaire</th>
                        <th>Projet</th>
                        <th>Departement</th>
                        <th>Date debut</th>
                        <th>Date fin</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($internships as $internship)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $internship->intern->user?->full_name ?? 'Stagiaire non lie' }}</div>
                                <small class="text-muted">{{ $internship->intern->school }} - {{ $internship->intern->specialty }}</small>
                            </td>
                            <td>
                                <div>{{ $internship->title }}</div>
                                @if($internship->description)
                                    <small class="text-muted">{{ $internship->description }}</small>
                                @endif
                            </td>
                            <td>{{ $internship->department }}</td>
                            <td>{{ $internship->start_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $internship->end_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $internship->status === 'termine' ? 'secondary' : ($internship->status === 'en_cours' ? 'success' : 'info') }}">
                                    {{ str_replace('_', ' ', $internship->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun stagiaire ne vous est affecte pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $internships->links() }}
    </div>
</div>
@endsection
