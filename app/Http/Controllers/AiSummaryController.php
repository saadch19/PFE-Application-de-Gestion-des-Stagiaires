<?php

namespace App\Http\Controllers;

use App\Models\Intern;
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

        return view('supervisor.weekly-summary', compact('intern'));
    }

    /**
     * AJAX: Trigger the agent and return JSON report.
     */
    public function generate(Request $request, Intern $intern): JsonResponse
    {
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

            return response()->json($response->json());

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Impossible de joindre le service IA. Vérifiez que le serveur FastAPI est démarré.',
            ], 503);
        }
    }
}
