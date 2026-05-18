<div class="row g-3">
    <div class="col-md-6">
        <label for="title" class="form-label">Titre</label>
        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $internship->title ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="department" class="form-label">Departement</label>
        <input type="text" class="form-control" id="department" name="department" value="{{ old('department', $internship->department ?? '') }}" required>
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $internship->description ?? '') }}</textarea>
    </div>

    <div class="col-md-4">
        <label for="intern_ids" class="form-label">Stagiaires</label>
        @php
            $selectedInterns = old('intern_ids', isset($internship) ? $internship->interns->pluck('id')->all() : []);
        @endphp
        <select class="form-select js-interns" id="intern_ids" name="intern_ids[]" multiple required>
            @foreach($interns as $intern)
                <option value="{{ $intern->id }}" @selected(in_array($intern->id, (array) $selectedInterns, true))>
                    {{ $intern->cin }} - {{ $intern->user?->full_name ?? 'Sans compte' }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Vous pouvez selectionner plusieurs stagiaires.</small>
    </div>

    <div class="col-md-4">
        <label for="supervisor_id" class="form-label">Encadrant</label>
        <select class="form-select" id="supervisor_id" name="supervisor_id">
            <option value="">Aucun</option>
            @foreach($supervisors as $supervisor)
                <option value="{{ $supervisor->id }}" @selected((string) old('supervisor_id', $internship->supervisor_id ?? '') === (string) $supervisor->id)>
                    {{ $supervisor->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label for="responsible_id" class="form-label">Responsable</label>
        <select class="form-select" id="responsible_id" name="responsible_id">
            <option value="">Aucun</option>
            @foreach($responsibles as $responsible)
                <option value="{{ $responsible->id }}" @selected((string) old('responsible_id', $internship->responsible_id ?? '') === (string) $responsible->id)>
                    {{ $responsible->full_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label for="start_date" class="form-label">Date debut</label>
        <input type="text" class="form-control js-date" id="start_date" name="start_date" value="{{ old('start_date', isset($internship) ? $internship->start_date?->format('d/m/Y') : '') }}" placeholder="jj/mm/aaaa" autocomplete="off" required>
    </div>

    <div class="col-md-4">
        <label for="end_date" class="form-label">Date fin</label>
        <input type="text" class="form-control js-date" id="end_date" name="end_date" value="{{ old('end_date', isset($internship) ? $internship->end_date?->format('d/m/Y') : '') }}" placeholder="jj/mm/aaaa" autocomplete="off" required>
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label">Statut</label>
        <select class="form-select" id="status" name="status" required>
            @foreach(['planifie' => 'Planifié', 'en_cours' => 'En cours', 'termine' => 'Terminé'] as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $internship->status ?? 'planifie') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
