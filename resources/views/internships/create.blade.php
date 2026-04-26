@extends('layouts.app')

@section('title', 'Nouveau stage')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Creer un stage</h1>

        <form action="{{ route('internships.store') }}" method="POST" class="vstack gap-3">
            @csrf
            @include('internships._form')

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Enregistrer</button>
                <a href="{{ route('internships.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
