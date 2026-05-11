@extends('layouts.app')

@section('title', 'Mot de passe oublie')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6 fade-in">
        <div class="card card-soft">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3">Mot de passe oublie</h1>
                <p class="text-muted mb-4">Reinitialisez votre mot de passe avec votre nom complet. L'identifiant est optionnel si votre nom correspond a un seul compte.</p>

                <form action="{{ route('forgot.password.perform') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nom complet</label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Identifiant (optionnel)</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Reinitialiser</button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('forgot.options') }}" class="small">Retour aux choix</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
