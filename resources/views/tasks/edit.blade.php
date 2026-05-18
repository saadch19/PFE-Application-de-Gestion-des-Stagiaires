@extends('layouts.app')

@section('title', 'Modifier tâche')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Modifier tâche</h1>

        <form action="{{ route('tasks.update', $task) }}" method="POST" class="vstack gap-3">
            @csrf
            @method('PUT')
            @include('tasks._form')

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
