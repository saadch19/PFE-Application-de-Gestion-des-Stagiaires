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

        <form method="GET" id="absencesSearchForm" class="row g-2 mb-3" onsubmit="event.preventDefault();">
            <div class="col-sm-10 col-md-7">
                <input type="text" name="search" id="absencesSearchInput" value="{{ $search ?? '' }}" class="form-control" placeholder="Rechercher (stagiaire, CIN, motif)" autocomplete="off">
            </div>
        </form>
        @push('scripts')
        <script>
        (function() {
            let timer;
            const input = document.getElementById('absencesSearchInput');
            const form  = document.getElementById('absencesSearchForm');
            const container = document.getElementById('absencesTableContainer');
            if (!input || !form || !container) return;

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
                    const newContainer = doc.getElementById('absencesTableContainer');
                    if (newContainer) {
                        container.innerHTML = newContainer.innerHTML;
                        // Re-initialize tooltips inside the updated container
                        document.querySelectorAll('#absencesTableContainer [title]').forEach(function (el) {
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
                input.value = url.searchParams.get('search') || '';
                loadTable(window.location.href);
            });
        })();
        </script>
        @endpush

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
                        <div class="text-muted">Absences non justifiées</div>
                        <div class="display-6 fw-semibold">{{ $absenceStats['unjustified'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div id="absencesTableContainer">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Stagiaire</th>
                            <th>Date</th>
                            <th>Motif</th>
                            <th>Justifiée</th>
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
                                        <a href="{{ route('absences.edit', $absence) }}" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('absences.destroy', $absence) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Supprimer" data-confirm="Supprimer cette alerte ?"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                @endunless
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isHr ? 5 : 6 }}" class="text-center text-muted">Aucune absence enregistrée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $absences->links() }}
        </div>
    </div>
</div>
@endsection
