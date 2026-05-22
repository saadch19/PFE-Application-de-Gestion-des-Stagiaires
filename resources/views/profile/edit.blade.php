@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
@php
    $canEditIdentity = $user->hasRole('Administrateur');
@endphp

<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Mon profil</h1>

        <form action="{{ route('profile.update') }}" method="POST" class="vstack gap-3">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="full_name" class="form-label">Nom complet</label>
                    <input
                        type="text"
                        class="form-control"
                        id="full_name"
                        name="full_name"
                        value="{{ old('full_name', $user->full_name) }}"
                        @if($canEditIdentity) required @else readonly disabled @endif
                    >
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        @if($canEditIdentity) required @else readonly disabled @endif
                    >
                    @unless($canEditIdentity)
                        <small class="text-muted">Votre nom et votre email sont gérés par l'administration.</small>
                    @endunless
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">Nouveau mot de passe (optionnel)</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </div>
</div>
@endsection
