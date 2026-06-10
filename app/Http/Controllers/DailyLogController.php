<?php

namespace App\Http\Controllers;

use App\Models\DailyLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyLogController extends Controller
{
    /**
     * Show the intern's daily journal page for the requested week.
     */
    public function index(Request $request): View
    {
        $user   = auth()->user();
        $intern = $user->intern;

        if (! $intern) {
            abort(403, 'Aucun profil stagiaire associé à ce compte.');
        }

        // Determine week to display (default = current week)
        $weekStart = $request->filled('week')
            ? Carbon::parse($request->query('week'))->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Load existing logs for this week, keyed by date string
        $logs = DailyLog::query()
            ->where('intern_id', $intern->id)
            ->whereBetween('log_date', [$weekStart, $weekEnd])
            ->get()
            ->keyBy(fn (DailyLog $l) => $l->log_date->format('Y-m-d'));

        // Load in-progress tasks assigned to the intern
        $inProgressTasks = \App\Models\Task::query()
            ->with(['internship', 'assignedBy', 'assignedTo'])
            ->where('assigned_to', $user->id)
            ->where('status', 'en_cours')
            ->latest()
            ->get();

        return view('interns.daily-log', compact('intern', 'logs', 'weekStart', 'weekEnd', 'inProgressTasks'));
    }

    /**
     * AJAX: Save (upsert) a single day's log entry.
     */
    public function store(Request $request): JsonResponse
    {
        $user   = auth()->user();
        $intern = $user->intern;

        if (! $intern) {
            abort(403);
        }

        $validated = $request->validate([
            'log_date'   => ['required', 'date', 'before_or_equal:today'],
            'is_present' => ['required', 'boolean'],
            'daily_note' => ['nullable', 'string', 'max:3000'],
        ]);

        // Can't log for dates before the internship started
        if ($intern->start_date && Carbon::parse($validated['log_date'])->lt($intern->start_date)) {
            return response()->json(['message' => 'Date antérieure au début du stage.'], 422);
        }

        $log = DailyLog::updateOrCreate(
            [
                'intern_id' => $intern->id,
                'log_date'  => $validated['log_date'],
            ],
            [
                'is_present' => $validated['is_present'],
                'daily_note' => $validated['daily_note'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Journal mis à jour.',
            'log_id'  => $log->id,
        ]);
    }
}
