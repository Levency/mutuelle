<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Évite d'afficher la page de connexion du portail à un membre déjà connecté.
 */
class RedirectIfMemberAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('member')->check()) {
            return redirect()->route('portal.dashboard');
        }

        return $next($request);
    }
}
