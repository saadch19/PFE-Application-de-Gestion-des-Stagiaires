@extends('layouts.app')

@section('title', 'Attestation')

@section('content')
@php
    $mainInternship = $internships->first();
    $period = $mainInternship
        ? ($mainInternship->start_date?->format('d/m/Y') ?? '-') . ' au ' . ($mainInternship->end_date?->format('d/m/Y') ?? '-')
        : ($intern->start_date?->format('d/m/Y') ?? '-') . ' au ' . ($intern->end_date?->format('d/m/Y') ?? '-');
    $department = $mainInternship?->department ?? '-';
    $subject = $mainInternship?->title ?? '-';
    $supervisor = $mainInternship?->supervisor?->full_name ?? 'Non assigné';
@endphp

<div class="d-flex justify-content-end mb-3 print-actions">
    <button class="btn btn-primary" onclick="window.print()">Imprimer</button>
</div>

<section class="attestation-document fade-in" id="attestation-card">
    <header class="attestation-top">
        <div>
            <img src="{{ asset('images/ALTEN-Logo.wine.png') }}" alt="ALTEN" class="attestation-logo">
        </div>
        <div class="text-end">
            <div class="attestation-meta-label">Date</div>
            <div class="attestation-meta-value">{{ $generatedAt->format('d/m/Y') }}</div>
        </div>
    </header>

    <div class="attestation-title">
        <h1>Attestation de stage</h1>
        <p>Document généré par le service Ressources Humaines</p>
    </div>

    <div class="attestation-body">
        <p>
            Nous soussignés, attestons que
            <strong>{{ $intern->user?->full_name ?? 'Stagiaire sans compte lié' }}</strong>,
            titulaire de la CIN <strong>{{ $intern->cin }}</strong>, a effectué un stage au sein de notre entreprise.
        </p>

        <div class="attestation-info-grid">
            <div>
                <span>Nom du stagiaire</span>
                <strong>{{ $intern->user?->full_name ?? '-' }}</strong>
            </div>
            <div>
                <span>Sujet du stage</span>
                <strong>{{ $subject }}</strong>
            </div>
            <div>
                <span>Période</span>
                <strong>{{ $period }}</strong>
            </div>
            <div>
                <span>Département</span>
                <strong>{{ $department }}</strong>
            </div>
            <div>
                <span>Encadrant</span>
                <strong>{{ $supervisor }}</strong>
            </div>
            <div>
                <span>Note encadrant</span>
                <strong>{{ $attestationRequest?->supervisor_grade !== null ? $attestationRequest->supervisor_grade . '/20' : '-' }}</strong>
            </div>
        </div>

        <p>
            Cette attestation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.
        </p>
    </div>

    <footer class="attestation-footer">
        <div>
            <div class="attestation-meta-label">Fait le</div>
            <div class="attestation-meta-value">{{ $generatedAt->format('d/m/Y') }}</div>
        </div>

        <div class="attestation-signature-block">
            <div class="attestation-meta-label">Signature RH et cachet entreprise</div>
            <div class="attestation-assets">
                @if($rhUser?->company_stamp_path)
                    <img src="{{ route('rh.profile.asset', [$rhUser, 'cachet']) }}" alt="Cachet entreprise">
                @endif
                @if($rhUser?->rh_signature_path)
                    <img src="{{ route('rh.profile.asset', [$rhUser, 'signature']) }}" alt="Signature RH">
                @endif
            </div>
            <div class="attestation-sign-line"></div>
        </div>
    </footer>
</section>

<style>
    .attestation-document {
        background: #fff;
        border: 1px solid #dfe4ea;
        border-radius: 8px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        color: #172033;
        margin: 0 auto;
        max-width: 900px;
        min-height: 980px;
        padding: 52px 58px;
    }

    .attestation-top,
    .attestation-footer {
        align-items: flex-start;
        display: flex;
        justify-content: space-between;
        gap: 32px;
    }

    .attestation-logo {
        height: 78px;
        width: auto;
        object-fit: contain;
    }

    .attestation-title {
        border-bottom: 2px solid #172033;
        margin: 44px 0 34px;
        padding-bottom: 18px;
        text-align: center;
    }

    .attestation-title h1 {
        font-size: 32px;
        font-weight: 700;
        margin: 0;
        text-transform: uppercase;
    }

    .attestation-title p,
    .attestation-meta-label {
        color: #697386;
        font-size: 13px;
        margin: 6px 0 0;
    }

    .attestation-meta-value {
        font-weight: 700;
    }

    .attestation-body {
        font-size: 17px;
        line-height: 1.8;
    }

    .attestation-info-grid {
        border: 1px solid #e4e8ef;
        border-radius: 8px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin: 30px 0;
        overflow: hidden;
    }

    .attestation-info-grid div {
        border-bottom: 1px solid #e4e8ef;
        min-height: 86px;
        padding: 16px 18px;
    }

    .attestation-info-grid div:nth-child(odd) {
        border-right: 1px solid #e4e8ef;
    }

    .attestation-info-grid div:nth-last-child(-n + 2) {
        border-bottom: 0;
    }

    .attestation-info-grid span {
        color: #697386;
        display: block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .attestation-info-grid strong {
        display: block;
        font-size: 15px;
        line-height: 1.45;
    }

    .attestation-footer {
        margin-top: 72px;
    }

    .attestation-signature-block {
        min-width: 300px;
        text-align: right;
    }

    .attestation-assets {
        align-items: end;
        display: flex;
        gap: 12px;
        height: 92px;
        justify-content: flex-end;
        margin-top: 8px;
    }

    .attestation-assets img {
        max-height: 84px;
        max-width: 145px;
        object-fit: contain;
    }

    .attestation-sign-line {
        border-top: 1px solid #172033;
        margin-left: auto;
        margin-top: 10px;
        width: 240px;
    }

    @media (max-width: 767.98px) {
        .attestation-document {
            padding: 28px 22px;
        }

        .attestation-top,
        .attestation-footer {
            flex-direction: column;
        }

        .attestation-info-grid {
            grid-template-columns: 1fr;
        }

        .attestation-info-grid div,
        .attestation-info-grid div:nth-child(odd) {
            border-right: 0;
        }

        .attestation-info-grid div:nth-last-child(2) {
            border-bottom: 1px solid #e4e8ef;
        }

        .attestation-signature-block {
            min-width: 100%;
            text-align: left;
        }

        .attestation-assets {
            justify-content: flex-start;
        }

        .attestation-sign-line {
            margin-left: 0;
        }
    }

    @media print {
        .navbar, .alert, .print-actions, footer:not(.attestation-footer) {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .container,
        .container-fluid {
            max-width: none !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .attestation-document {
            border: 0;
            border-radius: 0;
            box-shadow: none;
            min-height: auto;
            padding: 34px 44px;
        }
    }
</style>
@endsection
