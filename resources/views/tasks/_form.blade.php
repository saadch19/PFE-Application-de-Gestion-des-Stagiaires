<div class="row g-3">
    <div class="col-md-6">
        <label for="title" class="form-label">Titre</label>
        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $task->title ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="internship_id" class="form-label">Stage (optionnel)</label>
        <select class="form-select" id="internship_id" name="internship_id">
            <option value="">Aucun</option>
            @foreach($internships as $internship)
                <option value="{{ $internship->id }}" @selected((string) old('internship_id', $task->internship_id ?? '') === (string) $internship->id)>
                    {{ $internship->title }} - {{ $internship->intern->user?->full_name ?? $internship->intern->cin }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="assigned_to" class="form-label">Assigner a</label>
        <select class="form-select" id="assigned_to" name="assigned_to" required>
            <option value="">Selectionner</option>
            @foreach($users as $assignee)
                <option value="{{ $assignee->id }}" @selected((string) old('assigned_to', $task->assigned_to ?? '') === (string) $assignee->id)>
                    {{ $assignee->full_name }} ({{ $assignee->role?->name }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="due_date" class="form-label">Date limite</label>
        <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date', isset($task) ? $task->due_date?->format('Y-m-d') : '') }}">
    </div>

    <div class="col-md-3">
        <label for="status" class="form-label">Statut</label>
        <select class="form-select" id="status" name="status" required>
            @foreach(['a_faire' => 'A faire', 'en_cours' => 'En cours', 'termine' => 'Termine'] as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $task->status ?? 'a_faire') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label for="details" class="form-label">Details</label>
        <textarea class="form-control" id="details" name="details" rows="3">{{ old('details', $task->details ?? '') }}</textarea>
    </div>
</div>
