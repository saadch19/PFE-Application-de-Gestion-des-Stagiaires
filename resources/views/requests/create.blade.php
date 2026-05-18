@extends('layouts.app')

@section('title', 'Nouvelle demande')

@section('content')
<div class="card card-soft fade-in">
    <div class="card-body">
        <h1 class="h4 mb-3">Envoyer une demande</h1>

        <form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data" class="vstack gap-3">
            @csrf

            <div>
                <label for="type" class="form-label">Type de demande</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">Selectionner</option>
                    <option value="prolongation" @selected(old('type') === 'prolongation')>Prolongation</option>
                    <option value="attestation" @selected(old('type') === 'attestation')>Attestation</option>
                    <option value="retard_attestation" @selected(old('type') === 'retard_attestation')>Retard d'attestation</option>
                    <option value="absence" @selected(old('type') === 'absence')>Absence</option>
                    <option value="autre" @selected(old('type') === 'autre')>Autre</option>
                </select>
            </div>

            <div id="reportField" class="{{ old('type') === 'attestation' ? '' : 'd-none' }}">
                <label for="rapport_stage" class="form-label">Rapport de stage PDF</label>
                <input
                    type="file"
                    class="form-control"
                    id="rapport_stage"
                    name="rapport_stage"
                    accept="application/pdf,.pdf"
                    @if(old('type') === 'attestation') required @endif
                >
                <small class="text-muted">Le rapport sera transmis a l'encadrant puis au responsable de competence.</small>
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
        const reportField = document.getElementById('reportField');
        const reportInput = document.getElementById('rapport_stage');

        function toggleAbsenceReason() {
            const isAbsence = typeSelect.value === 'absence';
            const isAttestation = typeSelect.value === 'attestation';

            absenceReasonField.classList.toggle('d-none', ! isAbsence);
            absenceReasonInput.required = isAbsence;
            reportField.classList.toggle('d-none', ! isAttestation);
            reportInput.required = isAttestation;

            if (! isAbsence) {
                absenceReasonInput.value = '';
            }

            if (! isAttestation) {
                reportInput.value = '';
            }
        }

        typeSelect.addEventListener('change', toggleAbsenceReason);
        toggleAbsenceReason();
    });
</script>
@endpush
