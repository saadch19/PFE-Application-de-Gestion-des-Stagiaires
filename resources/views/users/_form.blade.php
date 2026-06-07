@php
    $editing = isset($user);
    $stagiaire = $roles->firstWhere('name', 'Stagiaire');
    $stagiaireRoleId = $stagiaire?->id ?? 0;

    // If editing, check if this user has an intern record
    $intern = ($editing && $user->role_id == $stagiaireRoleId)
        ? \App\Models\Intern::where('user_id', $user->id)->first()
        : null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="full_name" class="form-label">Nom complet</label>
        <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">Mot de passe {{ $editing ? '(laisser vide pour conserver)' : '' }}</label>
        <input type="password" class="form-control" id="password" name="password" {{ $editing ? '' : 'required' }}>
    </div>

    <div class="col-md-4">
        <label for="role_id" class="form-label">Role</label>
        <select class="form-select" id="role_id" name="role_id" required>
            <option value="">Sélectionner</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected((string) old('role_id', $user->role_id ?? '') === (string) $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked((bool) old('is_active', $user->is_active ?? true))>
            <label class="form-check-label" for="is_active">Actif</label>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     Intern-specific fields — shown only when "Stagiaire" role is selected
════════════════════════════════════════════════════════════════════════ --}}
<div id="intern-fields" class="mt-3" style="display: none;">
    <hr>
    <h5 class="mb-3"><i class="bi bi-person-badge"></i> Informations du stagiaire</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label for="cin" class="form-label">CIN</label>
            <input type="text" class="form-control" id="cin" name="cin" value="{{ old('cin', $intern->cin ?? '') }}">
        </div>

        <div class="col-md-4">
            <label for="phone" class="form-label">Téléphone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $intern->phone ?? '') }}">
        </div>

        <div class="col-md-4">
            <label for="school" class="form-label">École</label>
            <input type="text" class="form-control" id="school" name="school" value="{{ old('school', $intern->school ?? '') }}">
        </div>

        <div class="col-md-6">
            <label for="specialty" class="form-label">Spécialité</label>
            <input type="text" class="form-control" id="specialty" name="specialty" value="{{ old('specialty', $intern->specialty ?? '') }}">
        </div>

        <div class="col-md-3">
            <label for="start_date" class="form-label">Date début</label>
            <input type="text" class="form-control js-date" id="start_date" name="start_date"
                   value="{{ old('start_date', isset($intern) && $intern->start_date ? $intern->start_date->format('d/m/Y') : '') }}"
                   placeholder="jj/mm/aaaa" autocomplete="off">
        </div>

        <div class="col-md-3">
            <label for="end_date" class="form-label">Date fin</label>
            <input type="text" class="form-control js-date" id="end_date" name="end_date"
                   value="{{ old('end_date', isset($intern) && $intern->end_date ? $intern->end_date->format('d/m/Y') : '') }}"
                   placeholder="jj/mm/aaaa" autocomplete="off">
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role_id');
    const internFields = document.getElementById('intern-fields');
    const stagiaireRoleId = '{{ $stagiaireRoleId }}';

    function toggleInternFields() {
        const isIntern = roleSelect.value === stagiaireRoleId;
        internFields.style.display = isIntern ? 'block' : 'none';

        // Toggle required on intern-specific fields
        internFields.querySelectorAll('input').forEach(function (input) {
            if (['cin', 'school', 'specialty', 'start_date', 'end_date'].includes(input.name)) {
                input.required = isIntern;
            }
        });
    }

    roleSelect.addEventListener('change', toggleInternFields);
    toggleInternFields(); // Run on page load
});
</script>
