<div class="row g-3">
    <div class="col-md-6">
        <label for="intern_id" class="form-label">Stagiaire</label>
        <select class="form-select" id="intern_id" name="intern_id" required>
            <option value="">Selectionner</option>
            @foreach($interns as $intern)
                <option value="{{ $intern->id }}" @selected((string) old('intern_id', $absence->intern_id ?? '') === (string) $intern->id)>
                    {{ $intern->cin }} - {{ $intern->user?->full_name ?? 'Sans compte' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="date_absence" class="form-label">Date absence</label>
        <input type="date" class="form-control" id="date_absence" name="date_absence" value="{{ old('date_absence', isset($absence) ? $absence->date_absence?->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" value="1" id="justified" name="justified" @checked((bool) old('justified', $absence->justified ?? false))>
            <label class="form-check-label" for="justified">Justifiee</label>
        </div>
    </div>

    <div class="col-12">
        <label for="reason" class="form-label">Motif</label>
        <input type="text" class="form-control" id="reason" name="reason" value="{{ old('reason', $absence->reason ?? '') }}" required>
    </div>
</div>
