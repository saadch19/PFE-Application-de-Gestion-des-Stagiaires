@extends('layouts.app')

@section('title', 'Modifier utilisateur')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Modifier utilisateur</h1>

        <form action="{{ route('users.update', $user) }}" method="POST" class="vstack gap-3">
            @csrf
            @method('PUT')
            @include('users._form')

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Mettre a jour</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
