<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Vérifie si l'utilisateur est authentifié
        if (Auth::check()) {
            // Vérifie si le rôle de l'utilisateur correspond au rôle demandé
            if (Auth::user()->role === $role) {
                return $next($request); // L'utilisateur a le bon rôle, donc on passe à la suite
            } else {
                return response()->json(['message' => 'Accès interdit.'], 403); // Si le rôle ne correspond pas
            }
        }

        // Si l'utilisateur n'est pas authentifié
        return response()->json(['message' => 'Non autorisé.'], 401);
    }
}