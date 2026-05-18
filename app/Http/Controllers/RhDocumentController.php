<?php

namespace App\Http\Controllers;

use App\Models\InternshipRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RhDocumentController extends Controller
{
    public function reports(): View
    {
        $reports = InternshipRequest::query()
            ->with(['intern.user', 'intern.internships.supervisor', 'intern.internships.responsible', 'processedBy', 'supervisorValidator', 'rcValidator', 'rhProcessor'])
            ->where('type', 'attestation')
            ->whereNotNull('supervisor_validated_at')
            ->whereNotNull('rc_validated_at')
            ->latest()
            ->paginate(12);

        return view('rh.reports.index', compact('reports'));
    }

    public function attestations(): View
    {
        $attestations = InternshipRequest::query()
            ->with(['intern.user', 'intern.internships.supervisor', 'intern.internships.responsible', 'processedBy', 'supervisorValidator', 'rcValidator', 'rhProcessor'])
            ->where('type', 'attestation')
            ->whereIn('workflow_status', ['transmise_rh', 'attestation_prete'])
            ->orderByRaw("CASE WHEN workflow_status = 'transmise_rh' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12);

        return view('rh.attestations.index', compact('attestations'));
    }

    public function archives(): View
    {
        $attestations = InternshipRequest::query()
            ->with(['intern.user', 'rhProcessor'])
            ->where('type', 'attestation')
            ->where('workflow_status', 'attestation_prete')
            ->latest('rh_processed_at')
            ->paginate(12);

        return view('rh.archives.index', compact('attestations'));
    }

    public function profile(): View
    {
        return view('rh.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rh_signature' => ['nullable', 'image', 'max:2048'],
            'company_stamp' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        if ($request->hasFile('rh_signature')) {
            if ($user->rh_signature_path) {
                Storage::disk('public')->delete($user->rh_signature_path);
            }

            $validated['rh_signature_path'] = $request->file('rh_signature')->store('rh-assets', 'public');
        }

        if ($request->hasFile('company_stamp')) {
            if ($user->company_stamp_path) {
                Storage::disk('public')->delete($user->company_stamp_path);
            }

            $validated['company_stamp_path'] = $request->file('company_stamp')->store('rh-assets', 'public');
        }

        $user->update(array_filter([
            'rh_signature_path' => $validated['rh_signature_path'] ?? null,
            'company_stamp_path' => $validated['company_stamp_path'] ?? null,
        ]));

        return back()->with('success', 'Signature/cachet mis a jour.');
    }

    public function asset(Request $request, User $user, string $type): StreamedResponse
    {
        $path = $type === 'signature'
            ? $user->rh_signature_path
            : $user->company_stamp_path;

        if (! in_array($type, ['signature', 'cachet'], true) || $path === null || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
