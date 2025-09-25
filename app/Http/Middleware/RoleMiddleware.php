<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        /** @var User $user */
        $user = Auth::user();
        
        // Verificar si el usuario tiene el rol requerido
        switch ($role) {
            case 'admin':
                if (!$user->isAdmin()) {
                    abort(403, 'No tienes permisos para acceder a esta área.');
                }
                break;
            case 'teacher':
                if (!$user->isTeacher()) {
                    abort(403, 'No tienes permisos para acceder a esta área.');
                }
                break;
            case 'student':
                if (!$user->isStudent()) {
                    abort(403, 'No tienes permisos para acceder a esta área.');
                }
                break;
            default:
                abort(403, 'Rol no válido.');
        }

        return $next($request);
    }
}