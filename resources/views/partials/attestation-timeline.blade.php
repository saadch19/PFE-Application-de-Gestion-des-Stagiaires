@php
    $steps = [
        ['key' => 'report_path', 'label' => 'Rapport envoyé', 'done' => $requestItem->report_path !== null],
        ['key' => 'supervisor_validated_at', 'label' => 'Validé encadrant', 'done' => $requestItem->supervisor_validated_at !== null],
        ['key' => 'rc_validated_at', 'label' => 'Validé RC', 'done' => $requestItem->rc_validated_at !== null],
        ['key' => 'sent_to_rh_at', 'label' => 'Transmis RH', 'done' => $requestItem->sent_to_rh_at !== null],
        ['key' => 'attestation_generee', 'label' => 'Générée', 'done' => in_array($requestItem->workflow_status, ['attestation_generee', 'attestation_prete', 'attestation_imprimee', 'attestation_recuperee', 'attestation_archivee'], true)],
        ['key' => 'attestation_imprimee', 'label' => 'Imprimée', 'done' => in_array($requestItem->workflow_status, ['attestation_imprimee', 'attestation_recuperee', 'attestation_archivee'], true)],
        ['key' => 'attestation_recuperee', 'label' => 'Récupérée', 'done' => in_array($requestItem->workflow_status, ['attestation_recuperee', 'attestation_archivee'], true)],
        ['key' => 'attestation_archivee', 'label' => 'Archivée', 'done' => $requestItem->workflow_status === 'attestation_archivee'],
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
