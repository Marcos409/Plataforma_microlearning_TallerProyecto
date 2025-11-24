<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            try {
                $userId = Auth::id();
                $now = now();
                
                // 1. Actualizar updated_at del usuario
                DB::table('users')
                    ->where('id', $userId)
                    ->update(['updated_at' => $now]);
                
                // 2. Registrar en system_usage_logs
                $action = $this->determineAction($request);
                $module = $this->determineModule($request);
                
                DB::table('system_usage_logs')->insert([
                    'user_id' => $userId,
                    'action' => $action,
                    'module' => $module,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => $now
                ]);
                
            } catch (\Exception $e) {
                // Si falla, no interrumpir la petición
                \Log::error('Error en UpdateUserActivity: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
    
    /**
     * Determinar la acción basada en la ruta
     */
    private function determineAction(Request $request)
    {
        $route = $request->route();
        if (!$route) return 'page_view';
        
        $routeName = $route->getName() ?? '';
        $method = $request->method();
        
        // Detectar acciones específicas
        if (str_contains($routeName, 'login')) return 'login';
        if (str_contains($routeName, 'logout')) return 'logout';
        if (str_contains($routeName, 'content.show')) return 'content_viewed';
        if (str_contains($routeName, 'content.complete')) return 'content_completed';
        if (str_contains($routeName, 'diagnostic')) return 'diagnostic_activity';
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') return 'data_modified';
        
        return 'page_view';
    }
    
    /**
     * Determinar el módulo basado en la ruta
     */
    private function determineModule(Request $request)
    {
        $path = $request->path();
        
        if (str_contains($path, 'dashboard')) return 'dashboard';
        if (str_contains($path, 'content')) return 'content';
        if (str_contains($path, 'diagnostic')) return 'diagnostic';
        if (str_contains($path, 'progress')) return 'progress';
        if (str_contains($path, 'admin')) return 'admin';
        if (str_contains($path, 'teacher')) return 'teacher';
        
        return 'general';
    }
}