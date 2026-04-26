@extends('layouts.app')

@section('title', 'Attestation')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-outline-primary" onclick="window.print()">Imprimer</button>
</div>

<div class="card card-soft fade-in" id="attestation-card">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <h1 class="h3 mb-1">Attestation de stage</h1>
            <p class="text-muted mb-0">Generee automatiquement par la plateforme</p>
        </div>

        <p>
            Nous attestons que <strong>{{ $intern->user?->full_name ?? 'Stagiaire (sans compte lie)' }}</strong>,
            CIN <strong>{{ $intern->cin }}</strong>,
            a effectue un stage au sein de l'entreprise.
        </p>

        @if($internship)
            <p>
                Stage: <strong>{{ $internship->title }}</strong><br>
                Departement: <strong>{{ $internship->department }}</strong><br>
                Periode: <strong>{{ $internship->start_date?->format('d/m/Y') }} au {{ $internship->end_date?->format('d/m/Y') }}</strong><br>
                Encadrant: <strong>{{ $internship->supervisor?->full_name ?? 'Non assigne' }}</strong>
            </p>
        @else
            <p><em>Aucun stage associe n'a ete trouve pour ce stagiaire.</em></p>
        @endif

        <p>
            Cette attestation est delivree pour servir et valoir ce que de droit.
        </p>

        <div class="mt-5 d-flex justify-content-between">
            <div>
                <div class="text-muted">Date de generation</div>
                <strong>{{ $generatedAt->format('d/m/Y H:i') }}</strong>
            </div>
            <div class="text-end">
                <div class="text-muted">Signature RH</div>
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

        #attestation-card {
            box-shadow: none !important;
            border: 1px solid #ddd;
        }
    }
</style>
@endsection
