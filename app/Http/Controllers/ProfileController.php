<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole('Administrateur')) {
            $validated = $request->validate([
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ]);

            $payload = [];
        } else {
            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ]);

            $payload = [
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
            ];
        }

        if (! empty($validated['password'])) {
            $payload['password_hash'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return back()->with('success', 'Profil mis à jour.');
    }
}
