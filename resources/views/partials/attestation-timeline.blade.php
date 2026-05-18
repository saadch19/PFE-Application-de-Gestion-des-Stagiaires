@php
    $steps = [
        ['key' => 'report_path', 'label' => 'Rapport envoye', 'done' => $requestItem->report_path !== null],
        ['key' => 'supervisor_validated_at', 'label' => 'Valide encadrant', 'done' => $requestItem->supervisor_validated_at !== null],
        ['key' => 'rc_validated_at', 'label' => 'Valide RC', 'done' => $requestItem->rc_validated_at !== null],
        ['key' => 'sent_to_rh_at', 'label' => 'Transmis RH', 'done' => $requestItem->sent_to_rh_at !== null],
        ['key' => 'attestation_generee', 'label' => 'Generee', 'done' => in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete', 'attestation_imprimee', 'attestation_recuperee', 'attestation_archivee'], true)],
        ['key' => 'attestation_imprimee', 'label' => 'Imprimee', 'done' => in_array($requestItem->workflow_status, ['attestation_imprimee', 'attestation_recuperee', 'attestation_archivee'], true)],
        ['key' => 'attestation_recuperee', 'label' => 'Recuperee', 'done' => in_array($requestItem->workflow_status, ['attestation_recuperee', 'attestation_archivee'], true)],
        ['key' => 'attestation_archivee', 'label' => 'Archivee', 'done' => $requestItem->workflow_status === 'attestation_archivee'],
    ];
@endphp

<div class="attestation-timeline">
    @foreach($steps as $step)
        <div class="attestation-timeline-step {{ $step['done'] ? 'is-done' : '' }}">
            <span class="attestation-timeline-dot"></span>
            <span class="attestation-timeline-label">{{ $step['label'] }}</span>
        </div>
    @endforeach
</div>
