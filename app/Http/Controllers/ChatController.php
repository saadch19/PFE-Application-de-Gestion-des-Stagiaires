<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    private string $agentBaseUrl;

    public function __construct()
    {
        $this->agentBaseUrl = rtrim(config('services.ai_agent.url', 'http://localhost:8001'), '/');
    }

    /**
     * Proxy chat messages to the FastAPI agent.
     */
    public function send(Request $request): JsonResponse
    {
        // Prevent PHP from killing the request after 30 seconds if the LLM is slow
        set_time_limit(0);

        $validated = $request->validate([
            'message'    => ['required', 'string', 'max:2000'],
            'session_id' => ['nullable', 'string', 'max:100'],
            'model'      => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();

        try {
            $response = Http::timeout(120)->post("{$this->agentBaseUrl}/api/chat", [
                'user_id'    => $user->id,
                'user_role'  => $user->role?->name ?? 'Stagiaire',
                'message'    => $validated['message'],
                'session_id' => $validated['session_id'] ?? null,
                'model'      => $validated['model'] ?? null,
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

    /**
     * Get available chat models from the agent.
     */
    public function models(): JsonResponse
    {
        try {
            $response = Http::timeout(10)->get("{$this->agentBaseUrl}/api/chat/models");
            return response()->json($response->json());
        } catch (\Throwable) {
            return response()->json([
                'models'  => [],
                'default' => 'deepseek/deepseek-v4-flash',
            ]);
        }
    }

    /**
     * Clear a chat session.
     */
    public function clearSession(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');
        if (! $sessionId) {
            return response()->json(['success' => true]);
        }

        try {
            Http::timeout(10)->delete("{$this->agentBaseUrl}/api/chat/session/{$sessionId}");
        } catch (\Throwable) {
            // Swallow — session clearing is best-effort
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get conversation history for a session.
     */
    public function history(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');
        if (! $sessionId) {
            return response()->json(['messages' => []]);
        }

        try {
            $response = Http::timeout(10)->get("{$this->agentBaseUrl}/api/chat/session/{$sessionId}/history");
            return response()->json($response->json());
        } catch (\Throwable) {
            return response()->json(['messages' => []]);
        }
    }

    /**
     * Get all chat sessions for the authenticated user.
     */
    public function sessions(): JsonResponse
    {
        $user = auth()->user();
        try {
            $response = Http::timeout(10)->get("{$this->agentBaseUrl}/api/chat/sessions/{$user->id}");
            return response()->json($response->json());
        } catch (\Throwable) {
            return response()->json(['sessions' => []]);
        }
    }

    /**
     * Rename a chat session.
     */
    public function renameSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|integer',
            'title'      => 'required|string|max:255',
        ]);

        $sessionId = $request->input('session_id');
        $title     = $request->input('title');

        try {
            $response = Http::timeout(10)->put("{$this->agentBaseUrl}/api/chat/session/{$sessionId}/title", [
                'title' => $title
            ]);
            return response()->json($response->json());
        } catch (\Throwable) {
            return response()->json(['success' => false], 500);
        }
    }
}
