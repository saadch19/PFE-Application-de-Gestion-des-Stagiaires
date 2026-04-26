@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5 fade-in">
        <div class="card card-soft">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3">Connexion</h1>
                <p class="text-muted mb-4">Accedez a la plateforme de gestion des stagiaires.</p>

                <form action="{{ route('login.perform') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Se souvenir de moi
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Se connecter</button>
                </form>

                <div class="mt-4 small text-muted">
                    Compte demo admin: admin@internships.local / password123
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
