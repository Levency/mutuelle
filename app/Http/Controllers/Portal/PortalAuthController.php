<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Authentification du portail membre : nom + prénom + code d'accès.
 * Totalement indépendante de l'authentification de l'admin Filament
 * (guard "web") — utilise le guard "member" dédié.
 */
class PortalAuthController extends Controller
{
    public function showLogin()
    {
        return view('portal.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'  => ['required', 'string', 'max:100'],
            'last_name'   => ['required', 'string', 'max:100'],
            'access_code' => ['required', 'string', 'max:20'],
        ], [], [
            'first_name'  => 'prénom',
            'last_name'   => 'nom',
            'access_code' => "code d'accès",
        ]);

        $throttleKey = Str::lower($validated['first_name'] . '|' . $validated['last_name']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'access_code' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        $matched = Member::findForPortalLogin($validated['first_name'], $validated['last_name'])
            ->first(fn (Member $member) => $member->status === 'active'
                && $member->hasAccessCode()
                && Hash::check($validated['access_code'], $member->access_code));

        if (! $matched) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'access_code' => 'Identifiants invalides. Vérifiez votre nom, prénom et code d\'accès.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('member')->login($matched);
        $request->session()->regenerate();
        $matched->recordPortalLogin($request->ip());

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
