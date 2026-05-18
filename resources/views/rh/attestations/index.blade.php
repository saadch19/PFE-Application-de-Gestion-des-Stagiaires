@extends('layouts.app')

@section('title', 'Attestations')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Attestations</h1>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('rh.attestations.index') }}" class="btn btn-sm {{ $status === '' ? 'btn-primary' : 'btn-outline-primary' }}">
                Toutes
            </a>
            @foreach($statusFilters as $key => $filter)
                <a href="{{ route('rh.attestations.index', ['status' => $key]) }}" class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ $filter['label'] }}
                </a>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Stage</th>
                        <th>Workflow</th>
                        <th>État</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attestations as $requestItem)
                        @php $internship = $requestItem->intern->internships->sortByDesc('end_date')->first(); @endphp
                        <tr>
                            <td>{{ $requestItem->intern->user?->full_name ?? $requestItem->intern->cin }}</td>
                            <td>
                                <div>{{ $internship?->title ?? '-' }}</div>
                                <small class="text-muted">{{ $internship?->department ?? '-' }}</small>
                            </td>
                            <td>
                                @include('partials.attestation-timeline', ['requestItem' => $requestItem])
                            </td>
                            <td>
                                @statusBadge($requestItem->workflow_status)
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex justify-content-end align-items-center gap-2 flex-nowrap">
                                    @if($requestItem->workflow_status === 'transmise_rh')
                                        <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-primary text-nowrap">Générer attestation</a>
                                        <form action="{{ route('requests.rh-complete', $requestItem) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-success text-nowrap" type="submit">Envoyer message</button>
                                        </form>
                                    @else
                                        <a href="{{ route('interns.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Voir</a>
                                    @endif

                                    @if($requestItem->workflow_status !== 'transmise_rh')
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete', 'attestation_imprimee', 'attestation_recuperee'], true))
                                                    <li><a class="dropdown-item" href="{{ route('attestations.show', $requestItem->intern) }}">Imprimer</a></li>
                                                @endif
                                                @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete'], true))
                                                    <li>
                                                        <form action="{{ route('requests.rh-printed', $requestItem) }}" method="POST" class="m-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button class="dropdown-item" type="submit">Marquer imprimée</button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete', 'attestation_imprimee'], true))
                                                    <li>
                                                        <form action="{{ route('requests.rh-recovered', $requestItem) }}" method="POST" class="m-0">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button class="dropdown-item" type="submit">Récupérée</button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete', 'attestation_imprimee', 'attestation_recuperee'], true))
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('requests.rh-archive', $requestItem) }}" method="POST" class="m-0" onsubmit="return confirm('Archiver cette attestation ?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button class="dropdown-item text-warning" type="submit">Archiver</button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucune attestation à traiter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $attestations->links() }}
    </div>
</div>
@endsection
