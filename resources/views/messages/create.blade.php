@extends('layouts.app')

@section('title', 'Nouveau message')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Nouveau message</h1>

        <form action="{{ route('messages.store') }}" method="POST" class="vstack gap-3">
            @csrf

            <div>
                <label for="receiver_id" class="form-label">Destinataire</label>
                <select class="form-select" id="receiver_id" name="receiver_id" required>
                    <option value="">Selectionner</option>
                    @foreach($users as $receiver)
                        <option value="{{ $receiver->id }}" @selected((string) old('receiver_id') === (string) $receiver->id)>
                            {{ $receiver->full_name }} ({{ $receiver->role?->name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="subject" class="form-label">Objet</label>
                <input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject') }}" required>
            </div>

            <div>
                <label for="body" class="form-label">Message</label>
                <textarea class="form-control" id="body" name="body" rows="6" required>{{ old('body') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Envoyer</button>
                <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
