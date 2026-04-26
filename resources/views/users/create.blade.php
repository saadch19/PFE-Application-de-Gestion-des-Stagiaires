@extends('layouts.app')

@section('title', 'Nouvel utilisateur')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Creer un utilisateur</h1>

        <form action="{{ route('users.store') }}" method="POST" class="vstack gap-3">
            @csrf
            @include('users._form')

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Enregistrer</button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
