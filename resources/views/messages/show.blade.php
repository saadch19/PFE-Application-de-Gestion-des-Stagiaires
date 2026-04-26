@extends('layouts.app')

@section('title', 'Message')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h1 class="h4 mb-1">{{ $message->subject }}</h1>
                <div class="text-muted small">
                    De: {{ $message->sender?->full_name }} | A: {{ $message->receiver?->full_name }} | {{ $message->created_at?->format('d/m/Y H:i') }}
                </div>
            </div>
            <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary btn-sm">Retour</a>
        </div>

        <div class="border rounded p-3 bg-light-subtle" style="white-space: pre-wrap;">{{ $message->body }}</div>
    </div>
</div>
@endsection
