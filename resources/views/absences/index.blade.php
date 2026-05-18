@extends('layouts.app')

@section('title', 'Absences')

@section('content')
@php $isHr = auth()->user()->hasRole('Responsable RH'); @endphp

<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Suivi des absences</h1>
            @unless($isHr)
                <a href="{{ route('absences.create') }}" class="btn btn-success btn-sm">Nouvelle absence</a>
            @endunless
        </div>

        @if($isHr)
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted">Nombre absences</div>
                        <div class="display-6 fw-semibold">{{ $absenceStats['total'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="border rounded p-3 bg-light">
                        <div class="text-muted">Absences non justifiees</div>
                        <div class="display-6 fw-semibold">{{ $absenceStats['unjustified'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Date</th>
                        <th>Motif</th>
                        <th>Justifiee</th>
                        <th>Saisie par</th>
                        @unless($isHr)
                            <th class="text-end">Actions</th>
                        @endunless
                    </tr>
                </thead>
                <tbody>
                    @forelse($absences as $absence)
                        <tr>
                            <td>{{ $absence->intern->user?->full_name ?? $absence->intern->cin }}</td>
                            <td>{{ $absence->date_absence?->format('d/m/Y') }}</td>
                            <td>{{ $absence->reason }}</td>
                            <td>@statusBadge($absence->justified ? 'valide' : 'en_attente')</td>
                            <td>{{ $absence->recordedBy?->full_name }}</td>
                            @unless($isHr)
                                <td class="text-end">
                                    <a href="{{ route('absences.edit', $absence) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                    <form action="{{ route('absences.destroy', $absence) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette absence ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                    </form>
                                </td>
                            @endunless
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isHr ? 5 : 6 }}" class="text-center text-muted">Aucune absence enregistree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $absences->links() }}
    </div>
</div>
@endsection
