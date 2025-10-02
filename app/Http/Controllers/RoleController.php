<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * Mostrar la vista de gestión de roles
     */
    public function index()
    {
        $roles = Role::withCount('users')->get();
        $usersWithoutRole = User::whereNull('role_id')->count();
        
        return view('roles.index', compact('roles', 'usersWithoutRole'));
    }

    /**
     * Obtener usuarios (para AJAX)
     */
    public function getUsers(Request $request): JsonResponse
    {
        $query = User::with('role');
        
        // Filtrar por rol si se especifica
        if ($request->has('role')) {
            $roleFilter = $request->get('role');
            
            if ($roleFilter === 'null') {
                $query->whereNull('role_id');
            } elseif (!empty($roleFilter)) {
                $query->where('role_id', $roleFilter);
            }
        }
        
        $users = $query->orderBy('name')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'student_code' => $user->student_code,
                'career' => $user->career,
                'semester' => $user->semester,
                'role_id' => $user->role_id,
                'role_name' => $user->role ? $user->role->name : 'Sin asignar',
                'active' => $user->active,
            ];
        });
        
        return response()->json(['users' => $users]);
    }

    /**
     * Asignar rol a un usuario
     */
    public function assignRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $role = Role::findOrFail($request->role_id);
            
            $user->update(['role_id' => $request->role_id]);
            
            return response()->json([
                'success' => true,
                'message' => "Rol '{$role->name}' asignado correctamente a {$user->name}",
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role_name' => $role->name
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignación masiva de roles
     */
    public function assignMassiveRoles(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        try {
            $role = Role::findOrFail($request->role_id);
            $updated = User::whereIn('id', $request->user_ids)
                          ->update(['role_id' => $request->role_id]);
            
            return response()->json([
                'success' => true,
                'message' => "Rol '{$role->name}' asignado a {$updated} usuario(s)",
                'updated_count' => $updated
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en asignación masiva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover rol de un usuario
     */
    public function removeRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $user->update(['role_id' => null]);
            
            return response()->json([
                'success' => true,
                'message' => "Rol removido de {$user->name}",
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role_name' => 'Sin asignar'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al remover el rol: ' . $e->getMessage()
            ], 500);
        }
    }
}