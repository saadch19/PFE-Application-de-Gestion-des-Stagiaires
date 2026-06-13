<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Support\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Connexion réussie.');
        }

        return back()
            ->withErrors(['email' => 'Identifiants invalides ou compte inactif.'])
            ->onlyInput('email');
    }

    public function showForgotOptions(): View
    {
        return view('auth.forgot-options');
    }

    public function showForgotIdentifier(): View
    {
        return view('auth.forgot-identifier');
    }

    public function forgotIdentifier(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
        ]);

        $users = User::query()
            ->where('is_active', true)
            ->where('full_name', 'like', '%' . $validated['full_name'] . '%')
            ->orderBy('full_name')
            ->limit(5)
            ->get(['full_name', 'email']);

        if ($users->isEmpty()) {
            return back()
                ->withErrors(['full_name' => 'Aucun compte actif ne correspond à ce nom.'])
                ->onlyInput('full_name');
        }

        return back()
            ->with('identifier_results', $users->map(fn (User $user): string => "{$user->full_name} : {$user->email}")->all())
            ->withInput();
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email'     => ['nullable', 'email', 'max:120'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $query = User::query()
            ->where('is_active', true)
            ->where('full_name', $validated['full_name']);

        if (! empty($validated['email'])) {
            $query->where('email', $validated['email']);
        }

        $users = $query->limit(2)->get();

        if ($users->isEmpty()) {
            return back()
                ->withErrors(['full_name' => 'Aucun compte actif ne correspond aux informations saisies.'])
                ->onlyInput('full_name', 'email');
        }

        if ($users->count() > 1) {
            return back()
                ->withErrors(['email' => 'Plusieurs comptes portent ce nom. Saisissez aussi votre identifiant.'])
                ->onlyInput('full_name', 'email');
        }

        $user = $users->first();

        // Cancel any previous pending reset for this user
        PasswordResetRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'en_attente')
            ->delete();

        // Create a new pending reset request — admin must approve it
        PasswordResetRequest::query()->create([
            'user_id'               => $user->id,
            'pending_password_hash' => Hash::make($validated['password']),
            'status'                => 'en_attente',
        ]);

        // Notify all admins about the pending reset
        NotificationService::passwordResetRequested();

        return redirect()
            ->route('login')
            ->with('success', 'Demande de réinitialisation envoyée. Votre mot de passe sera mis à jour après validation par un administrateur.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Déconnexion effectuée.');
    }
}
