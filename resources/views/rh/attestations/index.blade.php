@extends('layouts.app')

@section('title', 'Attestations')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Attestations</h1>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Stage</th>
                        <th>Workflow</th>
                        <th>Etat</th>
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
                                <div class="d-inline-flex justify-content-end gap-1 flex-nowrap">
                                    <a href="{{ route('interns.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Voir</a>
                                    @if($requestItem->workflow_status === 'transmise_rh')
                                        <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-primary text-nowrap">Generer attestation</a>
                                    @endif
                                    @if($requestItem->workflow_status === 'transmise_rh')
                                        <form action="{{ route('requests.rh-complete', $requestItem) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-success text-nowrap" type="submit">Envoyer message</button>
                                        </form>
                                    @endif
                                    @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete', 'attestation_imprimee', 'attestation_recuperee'], true))
                                        <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-dark text-nowrap">Imprimer</a>
                                    @endif
                                    @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete'], true))
                                        <form action="{{ route('requests.rh-printed', $requestItem) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-primary text-nowrap" type="submit">Marquer imprimee</button>
                                        </form>
                                    @endif
                                    @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete', 'attestation_imprimee'], true))
                                        <form action="{{ route('requests.rh-recovered', $requestItem) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success text-nowrap" type="submit">Recuperee</button>
                                        </form>
                                    @endif
                                    @if(in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete', 'attestation_imprimee', 'attestation_recuperee'], true))
                                        <form action="{{ route('requests.rh-archive', $requestItem) }}" method="POST" class="m-0" onsubmit="return confirm('Archiver cette attestation ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning text-nowrap" type="submit">Archiver</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucune attestation a traiter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $attestations->links() }}
    </div>
</div>
@endsection
