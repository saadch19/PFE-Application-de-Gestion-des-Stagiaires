@extends('layouts.app')

@section('title', 'Stagiaires')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Gestion des stagiaires</h1>
            <a href="{{ route('interns.create') }}" class="btn btn-success btn-sm">Nouveau stagiaire</a>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Rechercher (CIN, ecole, specialite)">
            </div>
            <div class="col-md-3 d-flex align-items-center">
                <div class="form-check mt-2 mt-md-0">
                    <input class="form-check-input" type="checkbox" value="1" id="archived" name="archived" @checked($showArchived)>
                    <label class="form-check-label" for="archived">Afficher les archives</label>
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" type="submit">Filtrer</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Compte</th>
                        <th>Ecole / Specialite</th>
                        <th>Periode</th>
                        <th>Etat</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($interns as $intern)
                        <tr>
                            <td>{{ $intern->cin }}</td>
                            <td>{{ $intern->user?->full_name ?? 'Non lie' }}</td>
                            <td>
                                <div>{{ $intern->school }}</div>
                                <small class="text-muted">{{ $intern->specialty }}</small>
                            </td>
                            <td>{{ $intern->start_date?->format('d/m/Y') }} - {{ $intern->end_date?->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $intern->is_archived ? 'text-bg-secondary' : 'text-bg-success' }}">
                                    {{ $intern->is_archived ? 'Archive' : 'Actif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('interns.edit', $intern) }}" class="btn btn-sm btn-outline-primary">Modifier</a>

                                @if(! $intern->is_archived)
                                    <form action="{{ route('interns.archive', $intern) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-warning" type="submit">Archiver</button>
                                    </form>
                                @else
                                    <form action="{{ route('interns.restore', $intern) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-success" type="submit">Restaurer</button>
                                    </form>
                                @endif

                                <a href="{{ route('attestations.show', $intern) }}" class="btn btn-sm btn-outline-info">Attestation</a>

                                <form action="{{ route('interns.destroy', $intern) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce stagiaire ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucun stagiaire.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $interns->links() }}
    </div>
</div>
@endsection
