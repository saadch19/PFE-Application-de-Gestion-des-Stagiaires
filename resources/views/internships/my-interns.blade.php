@extends('layouts.app')

@section('title', 'Mes stages')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h1 class="h4 mb-1">Mes stages</h1>
                <p class="text-muted mb-0">Stages affectes a vous avec leurs stagiaires et periodes.</p>
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
                        <th>Titre du stage</th>
                        <th>Departement</th>
                        <th>Periode</th>
                        <th>Statut</th>
                        <th>Stagiaires</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($internships as $internship)
                        <tr>
                            <td>
                                <div>{{ $internship->title }}</div>
                                @if($internship->description)
                                    <small class="text-muted">{{ $internship->description }}</small>
                                @endif
                            </td>
                            <td>{{ $internship->department }}</td>
                            <td>{{ $internship->start_date?->format('d/m/Y') ?? '-' }} - {{ $internship->end_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $internship->status === 'termine' ? 'secondary' : ($internship->status === 'en_cours' ? 'success' : 'info') }}">
                                    {{ str_replace('_', ' ', $internship->status) }}
                                </span>
                            </td>
                            <td>
                                @forelse($internship->interns as $intern)
                                    <a
                                        href="{{ route('supervisor.interns', ['highlight' => $intern->id]) }}"
                                        class="btn btn-sm btn-outline-success me-1 mb-1"
                                    >
                                        {{ $intern->user?->full_name ?? $intern->cin }}
                                    </a>
                                @empty
                                    <div class="text-muted">Aucun stagiaire lie</div>
                                @endforelse
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1 flex-nowrap">
                                <a href="{{ route('supervisor.internships.show', $internship) }}" class="btn btn-sm btn-outline-primary">Consulter</a>
                                @if($internship->status !== 'termine')
                                    <form action="{{ route('supervisor.internships.validate', $internship) }}" method="POST" class="m-0" onsubmit="return confirm('Valider la fin de ce stage ?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-success text-nowrap" type="submit">Valider fin</button>
                                    </form>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun stage ne vous est affecte pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $internships->links() }}
    </div>
</div>
@endsection
