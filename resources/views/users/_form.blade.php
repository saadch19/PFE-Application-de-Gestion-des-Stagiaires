@php $editing = isset($user); @endphp

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
            <option value="">Selectionner</option>
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
