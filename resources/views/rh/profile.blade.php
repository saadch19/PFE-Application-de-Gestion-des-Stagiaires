@extends('layouts.app')

@section('title', 'Profil RH')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Profil RH</h1>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted">Nom RH</div>
                    <div class="fw-semibold">{{ $user->full_name }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted">Email</div>
                    <div class="fw-semibold text-break">{{ $user->email }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="text-muted">Departement</div>
                    <div class="fw-semibold">Ressources Humaines</div>
                </div>
            </div>
        </div>

        <h2 class="h5 mb-3">Signature RH et cachet entreprise</h2>

        <form action="{{ route('rh.profile.update') }}" method="POST" enctype="multipart/form-data" class="vstack gap-3">
            @csrf

            <div>
                <label for="rh_signature" class="form-label">Signature RH</label>
                <input type="file" name="rh_signature" id="rh_signature" class="form-control" accept="image/*">
                <div class="text-muted small mt-1">Signature actuelle</div>
                @if($user->rh_signature_path)
                    <img src="{{ route('rh.profile.asset', [$user, 'signature']) }}" alt="Signature RH" class="mt-2 border rounded p-1" style="max-height: 90px;">
                @else
                    <div class="text-muted mt-2">Aucune signature importee.</div>
                @endif
            </div>

            <div>
                <label for="company_stamp" class="form-label">Cachet entreprise</label>
                <input type="file" name="company_stamp" id="company_stamp" class="form-control" accept="image/*">
                <div class="text-muted small mt-1">Cachet actuel</div>
                @if($user->company_stamp_path)
                    <img src="{{ route('rh.profile.asset', [$user, 'cachet']) }}" alt="Cachet entreprise" class="mt-2 border rounded p-1" style="max-height: 90px;">
                @else
                    <div class="text-muted mt-2">Aucun cachet importe.</div>
                @endif
            </div>

            <button class="btn btn-success" type="submit">Enregistrer</button>
        </form>
    </div>
</div>
@endsection
