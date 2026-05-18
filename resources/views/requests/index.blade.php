@extends('layouts.app')

@section('title', 'Demandes')

@section('content')
@php
    $user = auth()->user();
    $canProcess = $user->hasRole('Administrateur', 'Responsable de competence');
    $canSupervisorValidate = $user->hasRole('Encadrant');
    $canRcValidate = $user->hasRole('Responsable de competence');
    $canRhComplete = $user->hasRole('Responsable RH');
    $canCreate = $user->hasRole('Stagiaire') && $user->intern !== null;
    $workflowLabels = [
        'attente_validation_encadrant' => 'Attente encadrant',
        'attente_validation_rc' => 'Attente RC',
        'transmise_rh' => 'Transmise RH',
        'attestation_prete' => 'Attestation prete',
    ];
@endphp

<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Demandes</h1>
            @if($canCreate)
                <a href="{{ route('requests.create') }}" class="btn btn-success btn-sm">Nouvelle demande</a>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Statut</th>
                        <th>Traitee par</th>
                        <th class="text-end" style="min-width: 250px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $requestItem)
                        <tr>
                            <td>{{ $requestItem->intern->user?->full_name ?? $requestItem->intern->cin }}</td>
                            <td>
                                {{ $requestItem->type === 'retard_attestation' ? 'Retard attestation' : ucfirst($requestItem->type) }}
                                @if($requestItem->type === 'absence' && $requestItem->absence_generated_at !== null)
                                    <span class="badge text-bg-success ms-1">Absence creee</span>
                                @endif
                                @if($requestItem->type === 'attestation' && $requestItem->report_path)
                                    <div>
                                        <a href="{{ route('requests.report', $requestItem) }}" class="small">Rapport PDF</a>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($requestItem->type === 'absence' && $requestItem->motif_absence)
                                    <div><strong>Motif :</strong> {{ \Illuminate\Support\Str::limit($requestItem->motif_absence, 60) }}</div>
                                @endif
                                <div>{{ \Illuminate\Support\Str::limit($requestItem->message, 80) }}</div>
                            </td>
                            <td>
                                <span class="badge text-bg-info">{{ $requestItem->status }}</span>
                                @if($requestItem->workflow_status)
                                    <div class="small text-muted mt-1">{{ $workflowLabels[$requestItem->workflow_status] ?? $requestItem->workflow_status }}</div>
                                @endif
                            </td>
                            <td>{{ $requestItem->processedBy?->full_name ?? '-' }}</td>
                            <td>
                                <div class="d-flex justify-content-end gap-2 flex-nowrap">
                                    @if($requestItem->type === 'attestation' && $canSupervisorValidate && $requestItem->workflow_status === 'attente_validation_encadrant')
                                        <form action="{{ route('requests.supervisor-validate', $requestItem) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Valider stage</button>
                                        </form>
                                    @elseif($requestItem->type === 'attestation' && $canRcValidate && $requestItem->workflow_status === 'attente_validation_rc')
                                        <form action="{{ route('requests.rc-validate', $requestItem) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Valider rapport</button>
                                        </form>
                                    @elseif($requestItem->type === 'attestation' && $canRhComplete && $requestItem->workflow_status === 'transmise_rh')
                                        <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-primary text-nowrap">Generer</a>
                                        <form action="{{ route('requests.rh-complete', $requestItem) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Informer stagiaire</button>
                                        </form>
                                    @elseif($canProcess && $requestItem->status === 'en_attente' && $requestItem->type !== 'attestation')
                                        <form action="{{ route('requests.process', $requestItem) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="acceptee">
                                            <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Accepter</button>
                                        </form>
                                        <form action="{{ route('requests.process', $requestItem) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="refusee">
                                            <button type="submit" class="btn btn-sm btn-outline-warning text-nowrap">Refuser</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune demande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $requests->links() }}
    </div>
</div>
@endsection
