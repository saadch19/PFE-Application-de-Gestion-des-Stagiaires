@php $linkedUser = $intern->user ?? null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="full_name" class="form-label">Nom complet du stagiaire</label>
        <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $linkedUser?->full_name ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Identifiant / email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $linkedUser?->email ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">Mot de passe {{ isset($intern) ? '(laisser vide pour conserver)' : '' }}</label>
        <input type="password" class="form-control" id="password" name="password" {{ isset($intern) ? '' : 'required' }}>
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" {{ isset($intern) ? '' : 'required' }}>
    </div>

    <div class="col-md-4">
        <label for="cin" class="form-label">CIN</label>
        <input type="text" class="form-control" id="cin" name="cin" value="{{ old('cin', $intern->cin ?? '') }}" required>
    </div>

    <div class="col-md-4">
        <label for="phone" class="form-label">Telephone</label>
        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $intern->phone ?? '') }}">
    </div>

    <div class="col-md-4">
        <label for="school" class="form-label">Ecole</label>
        <input type="text" class="form-control" id="school" name="school" value="{{ old('school', $intern->school ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="specialty" class="form-label">Specialite</label>
        <input type="text" class="form-control" id="specialty" name="specialty" value="{{ old('specialty', $intern->specialty ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="start_date" class="form-label">Date debut</label>
        <input type="text" class="form-control js-date" id="start_date" name="start_date" value="{{ old('start_date', isset($intern) ? $intern->start_date?->format('d/m/Y') : '') }}" placeholder="jj/mm/aaaa" autocomplete="off" required>
    </div>

    <div class="col-md-6">
        <label for="end_date" class="form-label">Date fin</label>
        <input type="text" class="form-control js-date" id="end_date" name="end_date" value="{{ old('end_date', isset($intern) ? $intern->end_date?->format('d/m/Y') : '') }}" placeholder="jj/mm/aaaa" autocomplete="off" required>
    </div>
</div>
