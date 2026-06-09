<?php

namespace App\Http\Controllers;

use App\Models\Intern;
use App\Models\WeeklyReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AiSummaryController extends Controller
{
    private string $agentBaseUrl;

    public function __construct()
    {
        // FastAPI microservice URL — configure in .env as AI_AGENT_URL
        $this->agentBaseUrl = rtrim(config('services.ai_agent.url', 'http://localhost:8001'), '/');
    }

    /**
     * Show the weekly summary page for a specific intern (Encadrant view).
     */
    public function weeklyReport(Intern $intern): View
    {
        $intern->load(['user', 'internships.tasks']);

        // Load previous reports for the sidebar
        $previousReports = WeeklyReport::query()
            ->where('intern_id', $intern->id)
            ->orderByDesc('week_start')
            ->get();

        return view('supervisor.weekly-summary', compact('intern', 'previousReports'));
    }

    /**
     * AJAX: Trigger the agent and return JSON report, then save it to DB.
     */
    public function generate(Request $request, Intern $intern): JsonResponse
    {
        // Prevent PHP from killing the request while the AI is thinking
        set_time_limit(0);

        $validated = $request->validate([
            'week_start' => ['nullable', 'date_format:Y-m-d'],
            'week_end'   => ['nullable', 'date_format:Y-m-d'],
        ]);

        try {
            $response = Http::timeout(120)->post("{$this->agentBaseUrl}/api/weekly-summary", [
                'intern_id'  => $intern->id,
                'week_start' => $validated['week_start'] ?? null,
                'week_end'   => $validated['week_end']   ?? null,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'error'   => "Le service IA a retourné une erreur ({$response->status()}).",
                ], 502);
            }

            $data = $response->json();

            // If the AI succeeded, save/upsert the report in the database
            if (! empty($data['success']) && ! empty($data['report'])) {
                $report = $data['report'];

                WeeklyReport::updateOrCreate(
                    [
                        'intern_id'  => $intern->id,
                        'week_start' => $data['week_start'],
                    ],
                    [
                        'generated_by'        => auth()->id(),
                        'week_end'            => $data['week_end'],
                        'week_score'          => $report['week_score'] ?? 0,
                        'engagement_score'    => $report['engagement_score'] ?? 0,
                        'task_completion_rate' => $report['task_completion_rate'] ?? 0,
                        'overall_sentiment'   => $report['overall_sentiment'] ?? 'neutral',
                        'overall_rating'      => $report['overall_rating'] ?? null,
                        'report_json'         => $report,
                    ]
                );
            }

            return response()->json($data);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Impossible de joindre le service IA. Vérifiez que le serveur FastAPI est démarré.',
            ], 503);
        }
    }

    /**
     * AJAX: Return a previously saved report by its ID.
     */
    public function savedReport(WeeklyReport $report): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'intern_id'  => $report->intern_id,
            'week_start' => $report->week_start->format('Y-m-d'),
            'week_end'   => $report->week_end->format('Y-m-d'),
            'report'     => $report->report_json,
        ]);
    }
}
