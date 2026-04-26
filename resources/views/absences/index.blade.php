@extends('layouts.app')

@section('title', 'Absences')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Suivi des absences</h1>
            <a href="{{ route('absences.create') }}" class="btn btn-success btn-sm">Nouvelle absence</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Date</th>
                        <th>Motif</th>
                        <th>Justifiee</th>
                        <th>Saisie par</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absences as $absence)
                        <tr>
                            <td>{{ $absence->intern->user?->full_name ?? $absence->intern->cin }}</td>
                            <td>{{ $absence->date_absence?->format('d/m/Y') }}</td>
                            <td>{{ $absence->reason }}</td>
                            <td><span class="badge {{ $absence->justified ? 'text-bg-success' : 'text-bg-warning' }}">{{ $absence->justified ? 'Oui' : 'Non' }}</span></td>
                            <td>{{ $absence->recordedBy?->full_name }}</td>
                            <td class="text-end">
                                <a href="{{ route('absences.edit', $absence) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                <form action="{{ route('absences.destroy', $absence) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette absence ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune absence enregistree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $absences->links() }}
    </div>
</div>
@endsection
