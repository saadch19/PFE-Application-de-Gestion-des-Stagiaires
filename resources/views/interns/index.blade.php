@extends('layouts.app')

@section('title', 'Stagiaires')

@section('content')
@php
    $isSupervisor = auth()->user()->hasRole('Encadrant');
    $isHr = auth()->user()->hasRole('Responsable RH');
    $canManageInterns = auth()->user()->hasRole('Administrateur', 'Responsable de competence');
    $highlightInternId = $highlightInternId ?? null;
@endphp

<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Gestion des stagiaires</h1>
            @if(auth()->user()->hasRole('Responsable de competence'))
                <a href="{{ route('interns.create-intern') }}" class="btn btn-success btn-sm">
                    <i class="bi bi-person-plus me-1"></i> Nouveau stagiaire
                </a>
            @endif
        </div>

        <form method="GET" id="internsSearchForm" class="row g-2 mb-3" onsubmit="event.preventDefault();">
            <div class="col-md-6">
                <input type="text" name="search" id="internsSearchInput" value="{{ $search }}" class="form-control" placeholder="Rechercher (CIN, école, spécialité)" autocomplete="off">
            </div>
            @unless($isSupervisor)
                <div class="col-md-3 d-flex align-items-center">
                    <div class="form-check mt-2 mt-md-0">
                        <input class="form-check-input" type="checkbox" value="1" id="archived" name="archived" @checked($showArchived)>
                        <label class="form-check-label" for="archived">Afficher uniquement les archives</label>
                    </div>
                </div>
            @endunless
        </form>
        @push('scripts')
        <script>
        (function() {
            let timer;
            const input = document.getElementById('internsSearchInput');
            const form  = document.getElementById('internsSearchForm');
            const archivedCb = document.getElementById('archived');
            const container = document.getElementById('internsTableContainer');
            if (!form || !container) return;

            function loadTable(url) {
                container.style.opacity = '0.5';
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContainer = doc.getElementById('internsTableContainer');
                    if (newContainer) {
                        container.innerHTML = newContainer.innerHTML;
                        // Re-initialize tooltips inside the updated container
                        document.querySelectorAll('#internsTableContainer [title]').forEach(function (el) {
                            new bootstrap.Tooltip(el, { trigger: 'hover' });
                        });
                    }
                    container.style.opacity = '1';
                })
                .catch(err => {
                    console.error(err);
                    container.style.opacity = '1';
                });
            }

            if (input) {
                input.addEventListener('input', function() {
                    clearTimeout(timer);
                    timer = setTimeout(() => {
                        const val = input.value;
                        const url = new URL(window.location.href);
                        url.searchParams.set('search', val);
                        url.searchParams.delete('page');
                        
                        loadTable(url.toString());
                        history.pushState(null, '', url.toString());
                    }, 400);
                });
            }

            if (archivedCb) {
                archivedCb.addEventListener('change', function() {
                    const url = new URL(window.location.href);
                    if (archivedCb.checked) {
                        url.searchParams.set('archived', '1');
                    } else {
                        url.searchParams.delete('archived');
                    }
                    url.searchParams.delete('page');
                    loadTable(url.toString());
                    history.pushState(null, '', url.toString());
                });
            }

            container.addEventListener('click', function(e) {
                const link = e.target.closest('.pagination a');
                if (link) {
                    e.preventDefault();
                    const url = link.getAttribute('href');
                    loadTable(url);
                    history.pushState(null, '', url);
                }
            });

            window.addEventListener('popstate', function() {
                const url = new URL(window.location.href);
                if (input) input.value = url.searchParams.get('search') || '';
                if (archivedCb) archivedCb.checked = url.searchParams.get('archived') === '1';
                loadTable(window.location.href);
            });
        })();
        </script>
        @endpush

        <div id="internsTableContainer">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>CIN</th>
                            <th>Compte</th>
                            <th>École / Spécialité</th>
                            <th>Période</th>
                            <th>État</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($interns as $intern)
                            @php
                                $isCompleted = $intern->end_date !== null && $intern->end_date->lt(today()->subDay());
                                $hasAssignedInternship = $intern->internships
                                    ->contains(fn ($internship) => $internship->supervisor_id !== null);
                                $attestationRequest = $intern->requests
                                    ->where('type', 'attestation')
                                    ->sortByDesc('created_at')
                                    ->first();
                                $hasSupervisorValidation = $attestationRequest?->supervisor_validated_at !== null;
                                $hasRcValidation = $attestationRequest?->rc_validated_at !== null;
                                $canShowAttestation = ! $isHr || ($isCompleted && $hasSupervisorValidation && $hasRcValidation);
                                $showHighlight = (int) $highlightInternId === (int) $intern->id;
                            @endphp
                            <tr @class(['table-success' => $showHighlight])>
                                <td>{{ $intern->cin }}</td>
                                <td>{{ $intern->user?->full_name ?? 'Non lié' }}</td>
                                <td>
                                    <div>{{ $intern->school }}</div>
                                    <small class="text-muted">{{ $intern->specialty }}</small>
                                </td>
                                <td>{{ $intern->start_date?->format('d/m/Y') }} - {{ $intern->end_date?->format('d/m/Y') }}</td>
                                <td>
                                    @statusBadge($intern->is_archived ? 'archive' : ($isCompleted ? 'termine' : ($hasAssignedInternship ? 'en_cours' : 'en_attente')))
                                </td>
                                <td class="text-end">
                                    <a href="{{ $isSupervisor ? route('supervisor.interns.show', $intern) : route('interns.show', $intern) }}" class="btn btn-sm btn-outline-secondary" title="Voir"><i class="bi bi-eye"></i></a>

                                    @unless($isSupervisor)
                                        @unless($isHr)
                                            <a href="{{ route('interns.edit', $intern) }}" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>

                                            @if($intern->is_archived)
                                                <form action="{{ route('interns.restore', $intern) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-outline-success" type="submit" title="Restaurer"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                </form>
                                            @elseif($isCompleted)
                                                <form action="{{ route('interns.archive', $intern) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-outline-warning" type="submit" title="Archiver"><i class="bi bi-archive"></i></button>
                                                </form>
                                            @endif
                                        @endunless

                                        @if($canShowAttestation)
                                            <a href="{{ route('attestations.show', $intern) }}" class="btn btn-sm btn-outline-info" title="{{ $isHr ? 'Générer attestation' : 'Attestation' }}"><i class="bi bi-award"></i></a>
                                        @endif
                                    @endunless
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
</div>
@endsection
