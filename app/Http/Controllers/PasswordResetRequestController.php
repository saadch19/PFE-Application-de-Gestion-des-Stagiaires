<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use App\Support\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetRequestController extends Controller
{
    public function index(): View
    {
        $requests = PasswordResetRequest::query()
            ->with(['user', 'processedBy'])
            ->orderByRaw("CASE WHEN status = 'en_attente' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15);

        return view('admin.password-reset-requests', compact('requests'));
    }

    public function process(Request $request, PasswordResetRequest $passwordResetRequest): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole('Administrateur')) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:acceptee,refusee'],
        ]);

        if ($validated['action'] === 'acceptee') {
            // Apply the pending password to the user account
            $passwordResetRequest->user->update([
                'password_hash' => $passwordResetRequest->pending_password_hash,
            ]);
        }

        $passwordResetRequest->update([
            'status'       => $validated['action'],
            'processed_by' => $user->id,
            'processed_at' => now(),
        ]);

        // Notify the user about the outcome
        NotificationService::passwordResetProcessed($passwordResetRequest->fresh('user'));

        $label = $validated['action'] === 'acceptee' ? 'acceptée et mot de passe mis à jour' : 'refusée';

        return back()->with('success', "Demande {$label}.");
    }
}
