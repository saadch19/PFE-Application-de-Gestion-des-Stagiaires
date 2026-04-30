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
        'status',
        'processed_by',
        'absence_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'absence_generated_at' => 'datetime',
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
}
