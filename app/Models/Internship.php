<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Internship extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'department',
        'start_date',
        'end_date',
        'status',
        'supervisor_id',
        'responsible_id',
        'grade',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'grade' => 'float',
        ];
    }

    public function interns(): BelongsToMany
    {
        return $this->belongsToMany(Intern::class, 'internship_intern')
            ->withTimestamps();
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
