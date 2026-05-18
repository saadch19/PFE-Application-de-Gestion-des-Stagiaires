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
                @php
                    $internLabels = $internship->interns
                        ->map(fn ($intern) => $intern->user?->full_name ?? $intern->cin)
                        ->join(', ');
                    $internUserIds = $internship->interns
                        ->map(fn ($intern) => $intern->user_id)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                @endphp
                <option
                    value="{{ $internship->id }}"
                    data-start-date="{{ $internship->start_date?->format('Y-m-d') }}"
                    data-end-date="{{ $internship->end_date?->format('Y-m-d') }}"
                    data-user-ids="{{ implode(',', $internUserIds) }}"
                    @selected((string) old('internship_id', $task->internship_id ?? '') === (string) $internship->id)
                >
                    {{ $internship->title }}{{ $internLabels ? ' - ' . $internLabels : '' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="assigned_to" class="form-label">Assigner à</label>
        <select class="form-select" id="assigned_to" name="assigned_to" required>
            <option value="">Sélectionner</option>
            @foreach($users as $assignee)
                <option
                    value="{{ $assignee->id }}"
                    data-user-id="{{ $assignee->id }}"
                    data-end-date="{{ $assignee->intern?->end_date?->format('Y-m-d') }}"
                    @selected((string) old('assigned_to', $task->assigned_to ?? '') === (string) $assignee->id)
                >
                    {{ $assignee->full_name }} ({{ $assignee->role?->name }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="due_date" class="form-label">Date limite</label>
        <input type="text" class="form-control js-date" id="due_date" name="due_date" value="{{ old('due_date', isset($task) ? $task->due_date?->format('d/m/Y') : '') }}" placeholder="jj/mm/aaaa" autocomplete="off">
    </div>

    <div class="col-md-3">
        <label for="status" class="form-label">Statut</label>
        <select class="form-select" id="status" name="status" required>
            @foreach(['a_faire' => 'À faire', 'en_cours' => 'En cours', 'termine' => 'Terminé'] as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $task->status ?? 'a_faire') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <label for="details" class="form-label">Details</label>
        <textarea class="form-control" id="details" name="details" rows="3">{{ old('details', $task->details ?? '') }}</textarea>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        const $internship = $('#internship_id');
        const $assignee = $('#assigned_to');
        const $dueDate = $('#due_date');

        function getPicker() {
            if (! $dueDate[0]) {
                return null;
            }

            if ($dueDate[0]._flatpickr) {
                return $dueDate[0]._flatpickr;
            }

            if (window.flatpickr) {
                return flatpickr($dueDate[0], {
                    dateFormat: 'd/m/Y',
                    allowInput: true,
                    locale: 'fr'
                });
            }

            return null;
        }

        function applyAssigneeFilter(internshipUserIds) {
            const allowed = new Set(internshipUserIds);
            const hasFilter = internshipUserIds.length > 0;
            const currentValue = String($assignee.val() || '');
            let firstAllowed = '';

            $assignee.find('option').each(function (index) {
                const $option = $(this);
                const optionValue = String($option.val() || '');

                if (index === 0) {
                    return;
                }

                const isAllowed = ! hasFilter || allowed.has(optionValue);

                $option.prop('disabled', ! isAllowed);
                $option.toggle(isAllowed);

                if (isAllowed && firstAllowed === '') {
                    firstAllowed = optionValue;
                }
            });

            if (hasFilter) {
                if (! allowed.has(currentValue)) {
                    $assignee.val(firstAllowed || '');
                }
            } else if (currentValue === '') {
                $assignee.val('');
            }
        }

        function applyDueDateLimit() {
            const selectedInternship = $internship.find(':selected');
            const internshipStartDate = selectedInternship.data('start-date');
            const internshipEndDate = selectedInternship.data('end-date');
            const internshipId = String($internship.val() || '');
            const hasInternship = internshipId !== '';
            const internshipUserIds = String(selectedInternship.data('user-ids') || '')
                .split(',')
                .map(value => value.trim())
                .filter(Boolean);

            applyAssigneeFilter(internshipUserIds);

            if (internshipUserIds.length === 1) {
                $assignee.val(String(internshipUserIds[0]));
            }

            const picker = getPicker();

            if (picker) {
                const minDate = hasInternship && internshipStartDate
                    ? flatpickr.parseDate(internshipStartDate, 'Y-m-d')
                    : null;
                const maxDate = hasInternship && internshipEndDate
                    ? flatpickr.parseDate(internshipEndDate, 'Y-m-d')
                    : null;

                picker.set('minDate', minDate);
                picker.set('maxDate', maxDate);

                if (picker.selectedDates.length > 0) {
                    const selected = picker.selectedDates[0];

                    if (minDate && selected < minDate) {
                        picker.setDate(minDate, true);
                    } else if (maxDate && selected > maxDate) {
                        picker.setDate(maxDate, true);
                    }
                }
            }
        }

        $internship.on('change', applyDueDateLimit);
        $assignee.on('change', applyDueDateLimit);
        applyDueDateLimit();
    });
</script>
@endpush
