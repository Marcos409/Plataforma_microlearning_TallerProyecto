<?php
// app/Http/Controllers/Admin/AdminUserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\UsuarioModel;
use App\Models\Role; // Solo para listar roles (opcional, puedes cambiarlo después)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminUserController extends Controller
{
    protected $usuarioModel;

    /**
     * Constructor - Inyecta el modelo PDO
     */
    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

/**
 * Display a listing of all users
 */
public function index(Request $request)
{
    // Obtener filtros
    $search = $request->get('search');
    $roleId = $request->get('role');
    $active = $request->get('active');

    // ✅ Llamar al stored procedure sp_buscar_usuarios
    $usersData = $this->usuarioModel->buscarUsuarios($search, $roleId, $active);

    // ✅ Convertir arrays a objetos y preparar datos para la vista
    $usersCollection = collect($usersData)->map(function($user) {
        $userObj = (object) $user;
        
        // Crear objeto role para mantener compatibilidad con la vista
        $userObj->role = (object) [
            'id' => $userObj->role_id ?? null,
            'name' => $userObj->role_name ?? 'Sin rol'
        ];
        
        // ✅ Convertir fechas a objetos Carbon
        if (isset($userObj->created_at)) {
            $userObj->created_at = \Carbon\Carbon::parse($userObj->created_at);
        }
        
        if (isset($userObj->updated_at)) {
            $userObj->updated_at = \Carbon\Carbon::parse($userObj->updated_at);
        }
        
        return $userObj;
    });

    // ✅ AGREGAR PAGINACIÓN MANUAL
    $perPage = 15;
    $currentPage = $request->input('page', 1);
    $total = $usersCollection->count();
    
    $users = new \Illuminate\Pagination\LengthAwarePaginator(
        $usersCollection->forPage($currentPage, $perPage),
        $total,
        $perPage,
        $currentPage,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    // ✅ Obtener roles usando PDO (en lugar de Eloquent)
    $rolesData = $this->usuarioModel->listarRoles();
    $roles = collect($rolesData)->map(function($role) {
        return (object) $role;
    });

    return view('admin.users.index', compact('users', 'roles'));
}

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role_id' => 'required|exists:roles,id',
            'student_code' => 'nullable|string|max:20|unique:users',
            'career' => 'nullable|string|max:255',
            'semester' => 'nullable|integer|min:1|max:12',
            'phone' => 'nullable|string|max:15',
            'password' => 'required|string|min:8|confirmed',
            'active' => 'boolean',
        ]);

        // Hashear contraseña
        $password = Hash::make($validated['password']);

        // Llamar al stored procedure sp_crear_usuario
        $userId = $this->usuarioModel->crearUsuario(
            $validated['name'],
            $validated['email'],
            $password,
            $validated['role_id'],
            $validated['student_code'] ?? null,
            $validated['career'] ?? null
        );

        if (!$userId) {
            return redirect()->back()
                ->with('error', 'Error al crear usuario. El email podría estar duplicado.')
                ->withInput();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified user
     */

    public function show($id)
    {
        // Llamar al stored procedure sp_obtener_usuario
        $userData = $this->usuarioModel->obtenerUsuario($id);

        if (!$userData) {
            abort(404, 'Usuario no encontrado');
        }

        // Convertir el array a objeto stdClass (Soluciona el error de "Attempt to read property 'name' on array")
        $user = (object) $userData; 
        
        // ✅ 1. Crear la propiedad 'role' anidada (Soluciona el error "Undefined property: stdClass::$role")
        $user->role = (object) [
            'id' => $user->role_id ?? null,
            'name' => $user->role_name ?? 'Sin rol'
        ];
        
        // ✅ 2. Convertir fechas a objetos Carbon (Soluciona el error "Call to a member function format() on string" en línea 77)
        
        // created_at
        if (isset($user->created_at)) {
            $user->created_at = Carbon::parse($user->created_at);
        }
        
        // updated_at (Si la vista lo usa)
        if (isset($user->updated_at)) {
            $user->updated_at = Carbon::parse($user->updated_at);
        }

        // email_verified_at (Si la vista lo usa, soluciona el error anterior de "Undefined property" si es NULL, 
        // y el error de formato si existe)
        if (isset($user->email_verified_at)) {
            $user->email_verified_at = Carbon::parse($user->email_verified_at);
        } else {
            // Si no existe y se usa en la vista, se asegura de que sea null para evitar errores
            $user->email_verified_at = null; 
        }

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        // Llamar al stored procedure sp_obtener_usuario
        $userData = $this->usuarioModel->obtenerUsuario($id);

        if (!$userData) {
            abort(404, 'Usuario no encontrado');
        }

        // 1. Convertir el array a objeto stdClass (SOLUCIÓN para "Attempt to read property...")
        $user = (object) $userData; 
        
        // 2. Crear la propiedad 'role' anidada (Necesario si la vista usa $user->role->name)
        $user->role = (object) [
            'id' => $user->role_id ?? null,
            'name' => $user->role_name ?? 'Sin rol'
        ];
        
        // 3. Convertir fechas a objetos Carbon (Para evitar "Call to a member function format() on string")
        if (isset($user->created_at)) {
            $user->created_at = Carbon::parse($user->created_at);
        }
        if (isset($user->updated_at)) {
            $user->updated_at = Carbon::parse($user->updated_at);
        }
        if (isset($user->email_verified_at) && $user->email_verified_at) {
            $user->email_verified_at = Carbon::parse($user->email_verified_at);
        } else {
            $user->email_verified_at = null; 
        }

        // 4. Obtener todos los roles para el dropdown de edición
        $roles = Role::all();

        // Si estás usando el ID 4 como "Sin rol" / "Pendiente", debes asegurarte de que exista en la DB.
        // Si no, y necesitas que aparezca la opción "Sin rol" (ej. para desasignar rol):
        /*
        $opcionSinRol = (object) ['id' => 0, 'name' => 'Sin rol']; 
        $roles = $roles->prepend($opcionSinRol);
        */
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        // Obtener usuario actual para validaciones
        $user = $this->usuarioModel->obtenerUsuario($id);

        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'student_code' => 'nullable|string|max:20|unique:users,student_code,' . $id,
            'career' => 'nullable|string|max:255',
            'semester' => 'nullable|integer|min:1|max:12',
            'phone' => 'nullable|string|max:15',
            'active' => 'boolean',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        // Llamar al stored procedure sp_actualizar_usuario
        $updated = $this->usuarioModel->actualizarUsuario(
            $id,
            $validated['name'],
            $validated['email'],
            $validated['role_id'],
            $validated['student_code'] ?? null,
            $validated['career'] ?? null
        );

        // Si hay contraseña nueva, actualizarla por separado
        if ($request->filled('password')) {
            // Aquí necesitarías un SP adicional: sp_actualizar_password
            // Por ahora lo dejamos comentado
            // $this->usuarioModel->actualizarPassword($id, Hash::make($request->password));
        }

        if (!$updated) {
            return redirect()->back()
                ->with('error', 'Error al actualizar usuario.')
                ->withInput();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy($id)
    {
        // Validar que no sea el admin principal
        if ($id == 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No se puede eliminar el administrador principal.');
        }

        // Llamar al stored procedure sp_eliminar_usuario
        $deleted = $this->usuarioModel->eliminarUsuario($id);

        if (!$deleted) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Error al eliminar usuario.');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Update user role via AJAX
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);

        // Obtener usuario actual
        $user = $this->usuarioModel->obtenerUsuario($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Actualizar solo el rol
        $updated = $this->usuarioModel->actualizarUsuario(
            $id,
            $user['name'],
            $user['email'],
            $request->role_id,
            $user['student_code'] ?? null,
            $user['career'] ?? null
        );

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar rol'
            ], 500);
        }

        // Obtener datos actualizados
        $userUpdated = $this->usuarioModel->obtenerUsuario($id);

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado exitosamente',
            'role_name' => $userUpdated['role_name'] ?? 'Sin rol'
        ]);
    }
/**
 * Show users pending role assignment
 */
public function pending(Request $request) // 1. Recibir el objeto Request
{
    // Obtener la data sin paginar (igual que antes)
    $usersData = $this->usuarioModel->buscarUsuarios(null, 4, null);
    
    // 2. Convertir arrays a objetos y aplicar transformaciones
    $usersCollection = collect($usersData)->map(function($user) {
        $userObj = (object) $user;
        
        // Crear objeto role para mantener compatibilidad con la vista
        $userObj->role = (object) [
            'id' => $userObj->role_id ?? null,
            'name' => $userObj->role_name ?? 'Sin rol'
        ];
        
        // Convertir fechas a objetos Carbon (Asegúrate de importar \Carbon\Carbon)
        if (isset($userObj->created_at)) {
            $userObj->created_at = \Carbon\Carbon::parse($userObj->created_at);
        }
        
        if (isset($userObj->updated_at)) {
            $userObj->updated_at = \Carbon\Carbon::parse($userObj->updated_at);
        }
        
        return $userObj;
    });

    // 3. AGREGAR PAGINACIÓN MANUAL
    $perPage = 15; // El número de elementos por página que desees
    $currentPage = $request->input('page', 1);
    $total = $usersCollection->count();
    
    // 4. Crear el objeto Paginator
    $users = new \Illuminate\Pagination\LengthAwarePaginator(
        $usersCollection->forPage($currentPage, $perPage),
        $total,
        $perPage,
        $currentPage,
        ['path' => $request->url(), 'query' => $request->query()]
    );
    
    // Obtener roles (sin cambios)
    $roles = Role::all();

    // 5. Devolver la vista con el objeto Paginator ($users)
    return view('admin.users.pending', compact('users', 'roles'));
}

    /**
     * Assign role to a single user
     */
    public function assignRole(Request $request, $id)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);

        // Obtener usuario
        $user = $this->usuarioModel->obtenerUsuario($id);

        if (!$user) {
            return redirect()->route('admin.users.pending')
                ->with('error', 'Usuario no encontrado.');
        }

        // Actualizar rol
        $updated = $this->usuarioModel->actualizarUsuario(
            $id,
            $user['name'],
            $user['email'],
            $request->role_id,
            $user['student_code'] ?? null,
            $user['career'] ?? null
        );

        if (!$updated) {
            return redirect()->route('admin.users.pending')
                ->with('error', 'Error al asignar rol.');
        }

        return redirect()->route('admin.users.pending')
            ->with('success', "Rol asignado correctamente a {$user['name']}");
    }

    /**
     * Assign roles to multiple users
     */
    public function bulkAssignRole(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        // Aquí necesitas un SP: sp_actualizar_rol_masivo
        // Si no lo tienes, tendrás que actualizar uno por uno
        $count = 0;
        foreach ($validated['user_ids'] as $userId) {
            $user = $this->usuarioModel->obtenerUsuario($userId);
            
            if ($user) {
                $updated = $this->usuarioModel->actualizarUsuario(
                    $userId,
                    $user['name'],
                    $user['email'],
                    $validated['role_id'],
                    $user['student_code'] ?? null,
                    $user['career'] ?? null
                );
                
                if ($updated) {
                    $count++;
                }
            }
        }

        return redirect()->route('admin.users.pending')
            ->with('success', "Roles asignados exitosamente a {$count} usuarios.");
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        // Obtener filtros
        $search = $request->get('search');
        $roleId = $request->get('role');
        
        if ($request->has('students_only')) {
            $roleId = 3; // Asumimos role_id 3 es 'estudiante'
        }

        // Llamar al SP para obtener usuarios
        $users = $this->usuarioModel->buscarUsuarios($search, $roleId, null);

        $filename = 'usuarios_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'ID', 'Nombre', 'Email', 'Rol', 'Código', 'Carrera', 'Semestre', 'Teléfono', 'Estado', 'Fecha Registro'
            ], ';');

            foreach ($users as $user) {
                fputcsv($file, [
                    $user['id'],
                    $user['name'],
                    $user['email'],
                    $user['role_name'] ?? 'Sin rol',
                    $user['student_code'] ?? '',
                    $user['career'] ?? '',
                    $user['semester'] ?? '',
                    $user['phone'] ?? '',
                    $user['active'] ? 'Activo' : 'Inactivo',
                    date('d/m/Y H:i', strtotime($user['created_at']))
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Toggle user active status via AJAX
     */
    public function toggleActive($id)
    {
        $user = $this->usuarioModel->obtenerUsuario($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Cambiar estado (si tienes sp_cambiar_estado_usuario, úsalo)
        $newStatus = !$user['active'];
        $updated = $this->usuarioModel->cambiarEstadoUsuario($id, $newStatus);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'active' => $newStatus
        ]);
    }
}