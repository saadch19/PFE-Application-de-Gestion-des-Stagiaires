@extends('layouts.app')

@section('title', 'Demandes')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = $user->hasRole('Administrateur');
    $canProcess = $user->hasRole('Administrateur', 'Responsable de competence');
    $canSupervisorValidate = ! $isAdmin && $user->hasRole('Encadrant');
    $canRcValidate = ! $isAdmin && $user->hasRole('Responsable de competence');
    $canRhComplete = $user->hasRole('Administrateur', 'Responsable RH');
    $canCreate = $user->hasRole('Stagiaire') && $user->intern !== null;
    $workflowLabels = [
        'attente_validation_encadrant' => 'Attente encadrant',
        'attente_validation_rc' => 'Attente RC',
        'transmise_rh' => 'Transmise RH',
        'attestation_prete' => 'Attestation prete',
        'attestation_generee' => 'Attestation generee',
        'attestation_imprimee' => 'Attestation imprimee',
        'attestation_recuperee' => 'Attestation recuperee',
        'attestation_archivee' => 'Attestation archivee',
    ];
    $printableAttestationStatuses = ['attestation_generee', 'attestation_prete', 'attestation_imprimee', 'attestation_recuperee'];
    $adminGeneratableAttestationStatuses = ['attente_validation_encadrant', 'attente_validation_rc', 'transmise_rh'];
@endphp

<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Demandes</h1>
            @if($canCreate)
                <a href="{{ route('requests.create') }}" class="btn btn-success btn-sm">Nouvelle demande</a>
            @endif
        </div>

        @if($isAdmin)
            <ul class="nav nav-tabs mb-4" id="requestsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request()->has('resets_page') ? '' : 'active' }}" id="stages-tab" data-bs-toggle="tab" data-bs-target="#stages-content" type="button" role="tab" aria-controls="stages-content" aria-selected="{{ request()->has('resets_page') ? 'false' : 'true' }}">
                        <i class="bi bi-file-earmark-text me-1"></i> Demandes de stage / absence
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ request()->has('resets_page') ? 'active' : '' }}" id="resets-tab" data-bs-toggle="tab" data-bs-target="#resets-content" type="button" role="tab" aria-controls="resets-content" aria-selected="{{ request()->has('resets_page') ? 'true' : 'false' }}">
                        <i class="bi bi-key me-1"></i> Réinitialisations de mot de passe
                        @if($pendingResetsCount > 0)
                            <span class="badge text-bg-warning ms-1">{{ $pendingResetsCount }}</span>
                        @endif
                    </button>
                </li>
            </ul>
        @endif

        <div class="tab-content" id="requestsTabsContent">
            <!-- Stage/absence requests tab -->
            <div class="tab-pane fade {{ ($isAdmin && request()->has('resets_page')) ? '' : 'show active' }}" id="stages-content" role="tabpanel" aria-labelledby="stages-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Stagiaire</th>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Statut</th>
                                <th>Traitée par</th>
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
                                        @statusBadge($requestItem->status)
                                        @if($requestItem->workflow_status)
                                            <div class="small text-muted mt-1">{{ $workflowLabels[$requestItem->workflow_status] ?? $requestItem->workflow_status }}</div>
                                            @if($requestItem->type === 'attestation')
                                                <div class="mt-2">
                                                    @include('partials.attestation-timeline', ['requestItem' => $requestItem])
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $requestItem->processedBy?->full_name ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2 flex-nowrap">
                                            @if($requestItem->type === 'attestation')
                                                @if($canSupervisorValidate && $requestItem->workflow_status === 'attente_validation_encadrant')
                                                    <form action="{{ route('requests.supervisor-validate', $requestItem) }}" method="POST" class="d-inline-flex gap-1 align-items-center">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input
                                                            type="number"
                                                            name="supervisor_grade"
                                                            class="form-control form-control-sm"
                                                            min="0"
                                                            max="20"
                                                            placeholder="Note /20"
                                                            required
                                                            style="width: 95px;"
                                                        >
                                                        <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Donner note</button>
                                                    </form>
                                                @elseif($canRcValidate && $requestItem->workflow_status === 'attente_validation_rc')
                                                    <form action="{{ route('requests.rc-validate', $requestItem) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Valider rapport</button>
                                                    </form>
                                                @endif

                                                @if($canRhComplete && in_array($requestItem->workflow_status, $adminGeneratableAttestationStatuses, true))
                                                    <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-primary text-nowrap">Voir</a>
                                                    <form action="{{ route('requests.rh-complete', $requestItem) }}" method="POST" class="d-inline-flex gap-1 align-items-center">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if($requestItem->supervisor_grade === null)
                                                            <input
                                                                type="number"
                                                                name="supervisor_grade"
                                                                class="form-control form-control-sm"
                                                                min="0"
                                                                max="20"
                                                                placeholder="Note /20"
                                                                required
                                                                style="width: 95px;"
                                                            >
                                                        @endif
                                                        <button type="submit" class="btn btn-sm btn-outline-success text-nowrap">Generer</button>
                                                    </form>
                                                @elseif($canRhComplete && in_array($requestItem->workflow_status, $printableAttestationStatuses, true))
                                                    <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-primary text-nowrap">Voir / Imprimer</a>
                                                    @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete'], true))
                                                        <form action="{{ route('requests.rh-printed', $requestItem) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary text-nowrap">Marquer imprimee</button>
                                                        </form>
                                                    @endif
                                                @endif
                                            @elseif($canProcess && $requestItem->status === 'en_attente')
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

            <!-- Password resets requests tab (only for admin) -->
            @if($isAdmin)
                <div class="tab-pane fade {{ request()->has('resets_page') ? 'show active' : '' }}" id="resets-content" role="tabpanel" aria-labelledby="resets-tab">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Demandé le</th>
                                    <th>Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($passwordResets as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->user?->full_name ?? '—' }}</td>
                                        <td>{{ $item->user?->email ?? '—' }}</td>
                                        <td>{{ $item->user?->role?->name ?? '—' }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($item->status === 'en_attente')
                                                <span class="badge text-bg-warning">En attente</span>
                                            @elseif($item->status === 'acceptee')
                                                <span class="badge text-bg-success">Acceptée</span>
                                                <div class="small text-muted mt-1">par {{ $item->processedBy?->full_name ?? '—' }} le {{ $item->processed_at?->format('d/m/Y H:i') }}</div>
                                            @else
                                                <span class="badge text-bg-danger">Refusée</span>
                                                <div class="small text-muted mt-1">par {{ $item->processedBy?->full_name ?? '—' }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($item->status === 'en_attente')
                                                <form action="{{ route('admin.password-reset.process', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="action" value="acceptee">
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Accepter et appliquer le nouveau mot de passe">
                                                        <i class="bi bi-check-lg"></i> Accepter
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.password-reset.process', $item) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="action" value="refusee">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Refuser la demande">
                                                        <i class="bi bi-x-lg"></i> Refuser
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted small">Traitée</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune demande de réinitialisation.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $passwordResets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
