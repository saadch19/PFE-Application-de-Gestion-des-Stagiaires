<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipRequest extends Model
{
    use HasFactory;

    protected $table = 'requests';

    protected $fillable = [
        'intern_id',
        'type',
        'motif_absence',
        'message',
        'report_path',
        'report_original_name',
        'status',
        'workflow_status',
        'processed_by',
        'supervisor_validated_by',
        'supervisor_validated_at',
        'supervisor_grade',
        'rc_validated_by',
        'rc_validated_at',
        'sent_to_rh_at',
        'rh_processed_by',
        'rh_processed_at',
        'attestation_printed_at',
        'attestation_recovered_at',
        'attestation_archived_at',
        'absence_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'absence_generated_at' => 'datetime',
            'supervisor_validated_at' => 'datetime',
            'rc_validated_at' => 'datetime',
            'sent_to_rh_at' => 'datetime',
            'rh_processed_at' => 'datetime',
            'attestation_printed_at' => 'datetime',
            'attestation_recovered_at' => 'datetime',
            'attestation_archived_at' => 'datetime',
        ];
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function supervisorValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_validated_by');
    }

    public function rcValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rc_validated_by');
    }

    public function rhProcessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rh_processed_by');
    }
}
