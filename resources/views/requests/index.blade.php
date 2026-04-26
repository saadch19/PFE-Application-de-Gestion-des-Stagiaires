@extends('layouts.app')

@section('title', 'Demandes')

@section('content')
@php
    $user = auth()->user();
    $canProcess = $user->hasRole('Administrateur', 'Responsable de competence');
    $canCreate = $user->hasRole('Stagiaire') && $user->intern !== null;
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
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $requestItem)
                        <tr>
                            <td>{{ $requestItem->intern->user?->full_name ?? $requestItem->intern->cin }}</td>
                            <td>{{ ucfirst($requestItem->type) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($requestItem->message, 80) }}</td>
                            <td><span class="badge text-bg-info">{{ $requestItem->status }}</span></td>
                            <td>{{ $requestItem->processedBy?->full_name ?? '-' }}</td>
                            <td class="text-end">
                                @if($canProcess && $requestItem->status === 'en_attente')
                                    <form action="{{ route('requests.process', $requestItem) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="acceptee">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Accepter</button>
                                    </form>
                                    <form action="{{ route('requests.process', $requestItem) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="refusee">
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Refuser</button>
                                    </form>
                                @endif

                                <form action="{{ route('requests.destroy', $requestItem) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette demande ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
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
