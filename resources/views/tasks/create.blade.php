@extends('layouts.app')

@section('title', 'Nouvelle tache')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Creer une tache</h1>

        <form action="{{ route('tasks.store') }}" method="POST" class="vstack gap-3">
            @csrf
            @include('tasks._form')

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Enregistrer</button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
