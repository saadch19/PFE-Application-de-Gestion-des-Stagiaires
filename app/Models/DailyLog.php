<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_id',
        'log_date',
        'is_present',
        'daily_note',
    ];

    protected function casts(): array
    {
        return [
            'log_date'   => 'date',
            'is_present' => 'boolean',
        ];
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }
}
