<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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

    public function evaluationTasks(): Collection
    {
        $this->loadMissing('internships.tasks');

        return $this->internships
            ->flatMap(fn (Internship $internship) => $internship->tasks)
            ->unique('id')
            ->values();
    }

    public function absenceCount(): int
    {
        if ($this->relationLoaded('absences')) {
            return $this->absences->count();
        }

        return $this->absences()->count();
    }

    public function performanceScore(): array
    {
        $tasks = $this->evaluationTasks();
        $absenceCount = $this->absenceCount();

        $presenceScore = max(0, 40 - ($absenceCount * 10));

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'termine')->count();
        $taskScore = $totalTasks === 0
            ? 40
            : (int) round(($completedTasks / $totalTasks) * 40);

        $deadlineTasks = $tasks->filter(function (Task $task): bool {
            return $task->due_date !== null
                && ($task->status === 'termine' || $task->due_date->lt(today()));
        });

        $onTimeTasks = $deadlineTasks->filter(function (Task $task): bool {
            return $task->status === 'termine'
                && $task->updated_at !== null
                && $task->updated_at->toDateString() <= $task->due_date->toDateString();
        })->count();

        $deadlineScore = $deadlineTasks->count() === 0
            ? 20
            : (int) round(($onTimeTasks / $deadlineTasks->count()) * 20);

        $score = min(100, $presenceScore + $taskScore + $deadlineScore);

        return [
            'score' => $score,
            'presence' => $presenceScore,
            'tasks' => $taskScore,
            'deadlines' => $deadlineScore,
            'label' => $this->scoreLabel($score),
            'badge' => $this->scoreBadge($score),
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
                'message' => 'Tache en retard : difficulte possible du stagiaire',
                'task' => $task,
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
