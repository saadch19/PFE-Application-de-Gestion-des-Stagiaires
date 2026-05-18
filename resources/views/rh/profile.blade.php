@extends('layouts.app')

@section('title', 'Profil RH')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Signature RH et cachet entreprise</h1>

        <form action="{{ route('rh.profile.update') }}" method="POST" enctype="multipart/form-data" class="vstack gap-3">
            @csrf

            <div>
                <label for="rh_signature" class="form-label">Signature RH</label>
                <input type="file" name="rh_signature" id="rh_signature" class="form-control" accept="image/*">
                @if($user->rh_signature_path)
                    <img src="{{ route('rh.profile.asset', [$user, 'signature']) }}" alt="Signature RH" class="mt-2 border rounded p-1" style="max-height: 90px;">
                @endif
            </div>

            <div>
                <label for="company_stamp" class="form-label">Cachet entreprise</label>
                <input type="file" name="company_stamp" id="company_stamp" class="form-control" accept="image/*">
                @if($user->company_stamp_path)
                    <img src="{{ route('rh.profile.asset', [$user, 'cachet']) }}" alt="Cachet entreprise" class="mt-2 border rounded p-1" style="max-height: 90px;">
                @endif
            </div>

            <button class="btn btn-success" type="submit">Enregistrer</button>
        </form>
    </div>
</div>
@endsection
