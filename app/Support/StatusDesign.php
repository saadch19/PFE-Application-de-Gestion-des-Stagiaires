<?php

namespace App\Support;

class StatusDesign
{
    public static function badge(string $status): array
    {
        return match ($status) {
            'en_attente', 'attente_validation_encadrant', 'attente_validation_rc', 'transmise_rh', 'en_attente_generation' => [
                'label' => 'En attente',
                'class' => 'text-bg-warning',
            ],
            'en_cours', 'a_faire', 'planifie', 'attestation_generee', 'attestation_prete', 'attestation_imprimee' => [
                'label' => self::label($status),
                'class' => 'text-bg-primary',
            ],
            'acceptee', 'termine', 'terminee', 'valide', 'attestation_recuperee' => [
                'label' => self::label($status),
                'class' => 'text-bg-success',
            ],
            'refusee', 'refuse', 'en_retard' => [
                'label' => self::label($status),
                'class' => 'text-bg-danger',
            ],
            'archive', 'archived', 'attestation_archivee' => [
                'label' => 'Archive',
                'class' => 'text-bg-dark',
            ],
            default => [
                'label' => self::label($status),
                'class' => 'text-bg-secondary',
            ],
        };
    }

    private static function label(string $status): string
    {
        return match ($status) {
            'a_faire' => 'A faire',
            'en_cours' => 'En cours',
            'planifie' => 'Planifie',
            'termine', 'terminee' => 'Termine',
            'acceptee' => 'Valide',
            'refusee' => 'Refuse',
            'attestation_generee', 'attestation_prete' => 'Generee',
            'attestation_imprimee' => 'Imprimee',
            'attestation_recuperee' => 'Recuperee',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
