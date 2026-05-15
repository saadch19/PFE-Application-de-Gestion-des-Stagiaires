@extends('layouts.app')

@section('title', 'Consulter stage')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 fade-in">
    <div>
        <h1 class="h4 mb-1">Consulter le stage</h1>
        <p class="text-muted mb-0">Informations generales du stage.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($internship->status !== 'termine')
            <form action="{{ route('supervisor.internships.validate', $internship) }}" method="POST" onsubmit="return confirm('Valider la fin de ce stage ?')">
                @csrf
                @method('PATCH')
                <button class="btn btn-success btn-sm" type="submit">Valider fin de stage</button>
            </form>
        @endif
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

            <dt class="col-sm-3">Stagiaires</dt>
            <dd class="col-sm-9 mb-0">
                @forelse($internship->interns as $intern)
                    <a
                        href="{{ route('supervisor.interns', ['highlight' => $intern->id]) }}"
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
@endsection
