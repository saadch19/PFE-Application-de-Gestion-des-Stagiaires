@extends('layouts.app')

@section('title', 'Compte oublie')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6 fade-in">
        <div class="card card-soft">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3">Identifiant ou mot de passe oublie</h1>
                <p class="text-muted mb-4">Choisissez ce que vous voulez récupérer.</p>

                <div class="d-grid gap-3">
                    <a href="{{ route('forgot.identifier') }}" class="btn btn-outline-success">
                        Identifiant oublie
                    </a>
                    <a href="{{ route('forgot.password') }}" class="btn btn-success">
                        Mot de passe oublie
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-link">
                        Retour a la connexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
