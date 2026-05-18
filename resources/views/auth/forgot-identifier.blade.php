@extends('layouts.app')

@section('title', 'Identifiant oublie')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6 fade-in">
        <div class="card card-soft">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 mb-3">Identifiant oublie</h1>
                <p class="text-muted mb-4">Entrez votre nom complet pour retrouver votre identifiant.</p>

                <form action="{{ route('forgot.identifier.perform') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nom complet</label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" class="form-control" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Rechercher</button>
                </form>

                @if(session('identifier_results'))
                    <div class="alert alert-success mt-4 mb-0">
                        <div class="fw-semibold mb-2">Identifiant trouvé :</div>
                        <ul class="mb-0">
                            @foreach(session('identifier_results') as $result)
                                <li>{{ $result }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="text-center mt-3">
                    <a href="{{ route('forgot.options') }}" class="small">Retour aux choix</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
