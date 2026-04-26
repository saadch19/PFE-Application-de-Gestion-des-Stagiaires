<div class="row g-3">
    <div class="col-md-6">
        <label for="user_id" class="form-label">Compte stagiaire lie (optionnel)</label>
        <select class="form-select" id="user_id" name="user_id">
            <option value="">Aucun</option>
            @foreach($users as $linkedUser)
                <option value="{{ $linkedUser->id }}" @selected((string) old('user_id', $intern->user_id ?? '') === (string) $linkedUser->id)>
                    {{ $linkedUser->full_name }} ({{ $linkedUser->email }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="cin" class="form-label">CIN</label>
        <input type="text" class="form-control" id="cin" name="cin" value="{{ old('cin', $intern->cin ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label for="phone" class="form-label">Telephone</label>
        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $intern->phone ?? '') }}">
    </div>

    <div class="col-md-6">
        <label for="school" class="form-label">Ecole</label>
        <input type="text" class="form-control" id="school" name="school" value="{{ old('school', $intern->school ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="specialty" class="form-label">Specialite</label>
        <input type="text" class="form-control" id="specialty" name="specialty" value="{{ old('specialty', $intern->specialty ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="start_date" class="form-label">Date debut</label>
        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', isset($intern) ? $intern->start_date?->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="end_date" class="form-label">Date fin</label>
        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', isset($intern) ? $intern->end_date?->format('Y-m-d') : '') }}" required>
    </div>
</div>
