<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class Intern extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cin',
        'school',
        'specialty',
        'phone',
        'start_date',
        'end_date',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_archived' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function internships(): BelongsToMany
    {
        return $this->belongsToMany(Internship::class, 'internship_intern')
            ->withTimestamps();
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(InternshipRequest::class);
    }

    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class)->orderBy('log_date');
    }

    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WeeklyReport::class)->orderByDesc('week_start');
    }

    public function evaluationTasks(): Collection
    {
        $this->loadMissing('internships.tasks');

        if ($this->user_id === null) {
            return collect();
        }

        return $this->internships
            ->flatMap(fn (Internship $internship) => $internship->tasks)
            ->filter(fn (Task $task): bool => (int) $task->assigned_to === (int) $this->user_id)
            ->unique('id')
            ->values();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Absence count — journal-based + official absences
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Count absence days combining:
     *  1. Admin/RC-recorded absences (always count)
     *  2. Missing daily logs on working days (Mon–Fri) = absent by default
     */
    public function absenceCount(): int
    {
        if (! $this->start_date || ! $this->end_date) {
            return 0;
        }

        $periodEnd = min(today(), $this->end_date);
        if ($this->start_date->gt($periodEnd)) {
            return 0;
        }

        // Get all working days (Mon–Fri) in the internship period
        $workingDays = CarbonPeriod::create($this->start_date, $periodEnd)
            ->filter(fn (Carbon $date) => $date->isWeekday());

        // Load official absences (recorded by admin/RC) — these always override
        $officialAbsences = $this->absences()
            ->pluck('date_absence')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->flip()
            ->all();

        // Load daily logs where the intern checked in AND left a comment
        $presentDays = $this->dailyLogs()
            ->where('is_present', true)
            ->whereNotNull('daily_note')
            ->where('daily_note', '!=', '')
            ->pluck('log_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->flip()
            ->all();

        $absentCount = 0;
        foreach ($workingDays as $day) {
            $dateStr = $day->format('Y-m-d');

            // Official absence always takes priority
            if (isset($officialAbsences[$dateStr])) {
                $absentCount++;
                continue;
            }

            // No daily log entry = absent
            if (! isset($presentDays[$dateStr])) {
                $absentCount++;
            }
        }

        return $absentCount;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Performance score — AI-report-based
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Calculate the intern's performance score.
     *
     * If AI weekly reports exist → average their week_score.
     * If no reports → return "no data" state.
     */
    public function performanceScore(): array
    {
        $reports = $this->weeklyReports;

        if ($reports->isEmpty()) {
            return [
                'score'    => null,
                'presence' => null,
                'tasks'    => null,
                'deadlines' => null,
                'label'    => 'Aucun rapport IA',
                'badge'    => 'secondary',
                'has_data' => false,
            ];
        }

        $avgScore      = (int) round($reports->avg('week_score'));
        $avgEngagement = round($reports->avg('engagement_score'), 1);
        $avgCompletion = (int) round($reports->avg('task_completion_rate'));
        $reportCount   = $reports->count();

        // Derive sub-scores from the AI data for display consistency
        // Engagement (0–10) → mapped to /40
        $presenceScore = (int) round(($avgEngagement / 10) * 40);
        // Task completion (0–100) → mapped to /40
        $taskScore     = (int) round(($avgCompletion / 100) * 40);
        // Remainder → deadlines
        $deadlineScore = max(0, $avgScore - $presenceScore - $taskScore);
        $deadlineScore = min(20, $deadlineScore);

        return [
            'score'        => $avgScore,
            'presence'     => $presenceScore,
            'tasks'        => $taskScore,
            'deadlines'    => $deadlineScore,
            'label'        => $this->scoreLabel($avgScore),
            'badge'        => $this->scoreBadge($avgScore),
            'has_data'     => true,
            'report_count' => $reportCount,
            'avg_engagement' => $avgEngagement,
            'avg_completion' => $avgCompletion,
        ];
    }

    public function smartAlerts(): array
    {
        $alerts = [];

        if ($this->absenceCount() > 3) {
            $alerts[] = [
                'type' => 'absence',
                'message' => 'Attention : stagiaire avec plusieurs absences',
            ];
        }

        foreach ($this->overdueTasks() as $task) {
            $alerts[] = [
                'type' => 'task',
                'message' => 'Tâche en retard : difficulté possible du stagiaire',
                'task' => $task,
            ];
        }

        // Alert if latest AI report shows negative/concerning sentiment
        $latestReport = $this->weeklyReports->first();
        if ($latestReport && in_array($latestReport->overall_sentiment, ['negative', 'concerning'])) {
            $alerts[] = [
                'type' => 'sentiment',
                'message' => 'Sentiment négatif détecté dans le dernier rapport IA (semaine du ' . $latestReport->week_start->format('d/m') . ')',
            ];
        }

        return $alerts;
    }

    public function overdueTasks(): Collection
    {
        return $this->evaluationTasks()
            ->filter(fn (Task $task) => $task->due_date !== null
                && $task->status !== 'termine'
                && $task->due_date->lt(today()))
            ->values();
    }

    private function scoreLabel(int $score): string
    {
        if ($score >= 80) {
            return 'Bon stagiaire';
        }

        if ($score >= 50) {
            return 'Stagiaire moyen';
        }

        return 'Stagiaire en difficulte';
    }

    private function scoreBadge(int $score): string
    {
        if ($score >= 80) {
            return 'success';
        }

        if ($score >= 50) {
            return 'warning';
        }

        return 'danger';
    }
}
