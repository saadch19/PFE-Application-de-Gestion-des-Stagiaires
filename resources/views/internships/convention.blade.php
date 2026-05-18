@extends('layouts.app')

@section('title', 'Convention de stage')

@section('content')
<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ route('internships.index') }}" class="btn btn-outline-secondary">Retour</a>
    <button class="btn btn-outline-primary" onclick="window.print()">Imprimer</button>
</div>

<div class="card card-soft fade-in" id="convention-card">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <img src="{{ asset('images/ALTEN-Logo.wine.png') }}" alt="ALTEN" style="height: 76px; width: auto;">
            <div class="text-end">
                <div class="text-muted">Date</div>
                <strong>{{ now()->format('d/m/Y') }}</strong>
            </div>
        </div>

        <div class="text-center mb-4">
            <h1 class="h3 mb-1">Convention de stage</h1>
            <p class="text-muted mb-0">{{ $internship->title }}</p>
        </div>

        <p>
            La presente convention confirme l'affectation du ou des stagiaires ci-dessous au service
            <strong>{{ $internship->department }}</strong>, pour la période du
            <strong>{{ $internship->start_date?->format('d/m/Y') }}</strong> au
            <strong>{{ $internship->end_date?->format('d/m/Y') }}</strong>.
        </p>

        <h2 class="h5 mt-4">Stagiaires concernes</h2>
        <ul>
            @forelse($internship->interns as $intern)
                <li>
                    <strong>{{ $intern->user?->full_name ?? $intern->cin }}</strong>
                    - CIN {{ $intern->cin }}
                    - {{ $intern->school }} / {{ $intern->specialty }}
                </li>
            @empty
                <li>Aucun stagiaire affecté.</li>
            @endforelse
        </ul>

        <h2 class="h5 mt-4">Encadrement</h2>
        <p>
            Encadrant : <strong>{{ $internship->supervisor?->full_name ?? 'Non assigné' }}</strong><br>
            Responsable de compétence : <strong>{{ $internship->responsible?->full_name ?? 'Non assigné' }}</strong>
        </p>

        @if($internship->description)
            <h2 class="h5 mt-4">Objet du stage</h2>
            <p>{{ $internship->description }}</p>
        @endif

        <div class="row mt-5">
            <div class="col-4">
                <div class="text-muted">Signature RH</div>
                <div style="height: 60px;"></div>
                <strong>__________________</strong>
            </div>
            <div class="col-4 text-center">
                <div class="text-muted">Signature encadrant</div>
                <div style="height: 60px;"></div>
                <strong>__________________</strong>
            </div>
            <div class="col-4 text-end">
                <div class="text-muted">Signature stagiaire</div>
                <div style="height: 60px;"></div>
                <strong>__________________</strong>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .navbar, .alert, .btn, footer {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        #convention-card {
            box-shadow: none !important;
            border: 1px solid #ddd;
        }
    }
</style>
@endsection
