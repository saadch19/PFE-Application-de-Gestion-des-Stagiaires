@extends('layouts.app')

@section('title', 'Nouvelle demande')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Envoyer une demande</h1>

        <form action="{{ route('requests.store') }}" method="POST" class="vstack gap-3">
            @csrf

            <div>
                <label for="type" class="form-label">Type de demande</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">Selectionner</option>
                    <option value="prolongation" @selected(old('type') === 'prolongation')>Prolongation</option>
                    <option value="attestation" @selected(old('type') === 'attestation')>Attestation</option>
                    <option value="absence" @selected(old('type') === 'absence')>Absence</option>
                    <option value="autre" @selected(old('type') === 'autre')>Autre</option>
                </select>
            </div>

            <div id="absenceReasonField" class="{{ old('type') === 'absence' ? '' : 'd-none' }}">
                <label for="motif_absence" class="form-label">Motif d'absence</label>
                <textarea
                    class="form-control"
                    id="motif_absence"
                    name="motif_absence"
                    rows="3"
                    placeholder="Exemple : maladie, rendez-vous, urgence personnelle..."
                    @if(old('type') === 'absence') required @endif
                >{{ old('motif_absence') }}</textarea>
            </div>

            <div>
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Envoyer</button>
                <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type');
        const absenceReasonField = document.getElementById('absenceReasonField');
        const absenceReasonInput = document.getElementById('motif_absence');

        function toggleAbsenceReason() {
            const isAbsence = typeSelect.value === 'absence';

            absenceReasonField.classList.toggle('d-none', ! isAbsence);
            absenceReasonInput.required = isAbsence;

            if (! isAbsence) {
                absenceReasonInput.value = '';
            }
        }

        typeSelect.addEventListener('change', toggleAbsenceReason);
        toggleAbsenceReason();
    });
</script>
@endpush
