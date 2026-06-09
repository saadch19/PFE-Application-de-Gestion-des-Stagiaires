<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'intern_id',
        'generated_by',
        'week_start',
        'week_end',
        'week_score',
        'engagement_score',
        'task_completion_rate',
        'overall_sentiment',
        'overall_rating',
        'report_json',
    ];

    protected function casts(): array
    {
        return [
            'week_start'           => 'date',
            'week_end'             => 'date',
            'week_score'           => 'integer',
            'engagement_score'     => 'integer',
            'task_completion_rate'  => 'integer',
            'report_json'          => 'array',
        ];
    }

    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
