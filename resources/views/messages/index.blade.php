@extends('layouts.app')

@section('title', 'Messagerie')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Messagerie interne</h1>
            <a href="{{ route('messages.create') }}" class="btn btn-success btn-sm">Nouveau message</a>
        </div>

        <ul class="nav nav-pills mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab !== 'sent' ? 'active' : '' }}" href="{{ route('messages.index', ['tab' => 'inbox']) }}">
                    Boite de reception
                    @if($unreadCount > 0)
                        <span class="badge text-bg-light ms-1">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'sent' ? 'active' : '' }}" href="{{ route('messages.index', ['tab' => 'sent']) }}">Messages envoyes</a>
            </li>
        </ul>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Objet</th>
                        <th>{{ $tab === 'sent' ? 'Destinataire' : 'Expediteur' }}</th>
                        <th>Date</th>
                        <th>Etat</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td>{{ $message->subject }}</td>
                            <td>{{ $tab === 'sent' ? $message->receiver?->full_name : $message->sender?->full_name }}</td>
                            <td>{{ $message->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($tab === 'sent')
                                    -
                                @else
                                    <span class="badge {{ $message->is_read ? 'text-bg-secondary' : 'text-bg-success' }}">
                                        {{ $message->is_read ? 'Lu' : 'Non lu' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('messages.show', $message) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                                @if($tab !== 'sent' && ! $message->is_read)
                                    <button class="btn btn-sm btn-outline-success mark-read" data-url="{{ route('messages.read', $message) }}">Marquer lu</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aucun message.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $messages->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('.mark-read').on('click', function () {
            const url = $(this).data('url');

            $.ajax({
                url,
                method: 'PATCH',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            }).done(function () {
                window.location.reload();
            }).fail(function () {
                alert('Impossible de marquer le message comme lu.');
            });
        });
    });
</script>
@endpush
