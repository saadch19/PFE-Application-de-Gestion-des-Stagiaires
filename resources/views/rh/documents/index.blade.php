@extends('layouts.app')

@section('title', 'Documents RH')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 fade-in">
    <div>
        <h1 class="h3 mb-1">Documents RH</h1>
        <p class="text-muted mb-0">Generer les attestations validees et informer les stagiaires.</p>
    </div>
</div>

<div class="card card-soft fade-in mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Attestations a traiter</h2>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Rapport</th>
                        <th>Validation</th>
                        <th>Etat RH</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentRequests as $requestItem)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $requestItem->intern->user?->full_name ?? $requestItem->intern->cin }}</div>
                                <small class="text-muted">{{ $requestItem->created_at?->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                @if($requestItem->report_path)
                                    <a href="{{ route('requests.report', $requestItem) }}">{{ $requestItem->report_original_name ?? 'Rapport PDF' }}</a>
                                @else
                                    <span class="text-muted">Aucun rapport</span>
                                @endif
                            </td>
                            <td>
                                <div>Encadrant : {{ $requestItem->supervisorValidator?->full_name ?? '-' }}</div>
                                <div>RC : {{ $requestItem->rcValidator?->full_name ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $requestItem->workflow_status === 'attestation_prete' ? 'text-bg-success' : 'text-bg-warning' }}">
                                    {{ $requestItem->workflow_status === 'attestation_prete' ? 'Attestation prête' : 'À imprimer' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex justify-content-end gap-1 flex-nowrap">
                                    <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-primary text-nowrap">Generer / imprimer</a>
                                    @if($requestItem->workflow_status !== 'attestation_prete')
                                        <form action="{{ route('requests.rh-complete', $requestItem) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-success text-nowrap" type="submit">Informer stagiaire</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucune attestation transmise au RH.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $documentRequests->links() }}
    </div>
</div>

@endsection
