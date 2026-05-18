@extends('layouts.app')

@section('title', 'Archives RH')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Archives des attestations</h1>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Date generation</th>
                        <th>Traitee par</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attestations as $requestItem)
                        <tr>
                            <td>{{ $requestItem->intern->user?->full_name ?? $requestItem->intern->cin }}</td>
                            <td>{{ $requestItem->rh_processed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $requestItem->rhProcessor?->full_name ?? '-' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex justify-content-end gap-1 flex-nowrap">
                                    <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-primary text-nowrap">Voir attestation</a>
                                    <a href="{{ route('rh.archives.download', $requestItem) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Telecharger PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucune attestation archivée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $attestations->links() }}
    </div>
</div>
@endsection
