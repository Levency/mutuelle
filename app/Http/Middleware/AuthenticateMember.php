<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège les pages du portail membre. Complètement indépendant du panneau
 * admin Filament : un membre non connecté au guard "member" est renvoyé vers
 * la page de connexion du portail, jamais vers /admin.
 */
class AuthenticateMember
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('member')->check()) {
            return redirect()->route('portal.login');
        }

        $member = Auth::guard('member')->user();

        // Un membre suspendu/inactif perd immédiatement l'accès au portail,
        // même si sa session était déjà ouverte.
        if ($member->status !== 'active') {
            Auth::guard('member')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portal.login')
                ->withErrors(['access_code' => "Votre compte n'est plus actif. Contactez un administrateur."]);
        }

        return $next($request);
    }
}
