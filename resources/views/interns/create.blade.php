@extends('layouts.app')

@section('title', 'Nouveau stagiaire')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Ajouter un stagiaire</h1>

        <form action="{{ route('interns.store') }}" method="POST" class="vstack gap-3">
            @csrf
            @include('interns._form')

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Enregistrer</button>
                <a href="{{ route('interns.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
