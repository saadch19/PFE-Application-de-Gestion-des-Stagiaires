@extends('layouts.app')

@section('title', 'Rapports valides')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Rapports valides</h1>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Stagiaire</th>
                        <th>Rapport</th>
                        <th>Validations</th>
                        <th>Commentaires</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $requestItem)
                        <tr>
                            <td>{{ $requestItem->intern->user?->full_name ?? $requestItem->intern->cin }}</td>
                            <td>
                                @if($requestItem->report_path)
                                    <a href="{{ route('requests.report', $requestItem) }}">{{ $requestItem->report_original_name ?? 'Rapport PDF' }}</a>
                                @else
                                    <span class="text-muted">Aucun PDF</span>
                                @endif
                            </td>
                            <td>
                                <div>Encadrant : {{ $requestItem->supervisor_validated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                                <div>RC : {{ $requestItem->rc_validated_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($requestItem->message, 80) }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex justify-content-end gap-1 flex-nowrap">
                                    @if($requestItem->report_path)
                                        <a href="{{ route('requests.report', $requestItem) }}" class="btn btn-sm btn-outline-secondary text-nowrap">Voir PDF</a>
                                    @endif
                                    <a href="{{ route('attestations.show', $requestItem->intern) }}" class="btn btn-sm btn-outline-primary text-nowrap">Generer attestation</a>
                                    <a href="{{ route('messages.create') }}" class="btn btn-sm btn-outline-info text-nowrap">Envoyer message</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucun rapport valide.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reports->links() }}
    </div>
</div>
@endsection
