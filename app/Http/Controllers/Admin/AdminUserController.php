<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
// ELIMINADO: use App\Models\User; // ❌ Ya no se usa para las consultas
use App\Contracts\UserDAOInterface; // ✅ NUEVO: Importamos el Contrato DAO
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Mantenemos solo para Route Model Binding y tipos

class AdminUserController extends Controller
{
    protected UserDAOInterface $userDAO; // ✅ Almacena la instancia de EloquentUserDAO

    /**
     * Inyección de dependencia del UserDAOInterface.
     */
    public function __construct(UserDAOInterface $userDAO)
    {
        $this->userDAO = $userDAO;
    }

    /**
     * Display a listing of all users
     */
    public function index(Request $request)
    {
        // 1. Delegamos toda la lógica de filtrado y paginación al DAO.
        $filters = $request->only(['search', 'role', 'active']);
        
        $users = $this->userDAO->getUsersWithFilters($filters, 15);

        $roles = Role::all();

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
        
        // 2. Usamos el DAO para crear el usuario. La lógica de persistencia está encapsulada.
        $this->userDAO->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // El hash se hace antes de pasar al DAO
            'role_id' => $validated['role_id'],
            'student_code' => $validated['student_code'] ?? null,
            'career' => $validated['career'] ?? null,
            'semester' => $validated['semester'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified user (Utiliza Route Model Binding de Laravel)
     */
    public function show(User $user)
    {
        // Se mantiene la carga de relaciones en la instancia obtenida por el Route Model Binding.
        $user->load(['role', 'studentProgress', 'learningPaths', 'recommendations']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user (Utiliza Route Model Binding de Laravel)
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user (Utiliza Route Model Binding de Laravel)
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            // ... (Reglas de validación, no se modifican)
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'student_code' => 'nullable|string|max:20|unique:users,student_code,' . $user->id,
            'career' => 'nullable|string|max:255',
            'semester' => 'nullable|integer|min:1|max:12',
            'phone' => 'nullable|string|max:15',
            'active' => 'boolean',
        ];
      
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
      
        $validated = $request->validate($rules);
      
        // Preparar datos para actualizar
        $dataToUpdate = array_merge($validated, [
            'active' => $request->boolean('active', true),
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            'student_code' => $validated['student_code'] ?? null,
            'career' => $validated['career'] ?? null,
            'semester' => $validated['semester'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);
        unset($dataToUpdate['password_confirmation']);
      
        // 3. Utilizamos el DAO para la actualización. Pasamos el ID del usuario.
        $this->userDAO->update($user->id, $dataToUpdate);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        if ($user->id === 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No se puede eliminar el administrador principal.');
        }

        // 4. Usamos el DAO para eliminar.
        $this->userDAO->delete($user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Update user role via AJAX
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);
        
        // 5. Usamos el DAO para la actualización simple del rol
        $this->userDAO->update($user->id, ['role_id' => $request->role_id]);
        
        $user->refresh(); // Se refresca el modelo para obtener la relación 'role' actualizada
        
        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado exitosamente',
            'role_name' => $user->role->name
        ]);
    }

    /**
     * Show users pending role assignment
     */
    public function pending()
    {
        // 6. Delegamos la consulta de usuarios pendientes al DAO
        $users = $this->userDAO->getUsersPendingRole();
        $roles = Role::all();
        
        return view('admin.users.pending', compact('users', 'roles'));
    }


    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);
    
        // 7. Usamos el DAO para asignar el rol
        $this->userDAO->update($user->id, ['role_id' => $request->role_id]);
    
        $user->refresh();
    
        return redirect()->route('admin.users.pending')
            ->with('success', "Rol asignado correctamente a {$user->name}");
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

        // 8. Delegamos la actualización masiva al DAO
        $count = $this->userDAO->bulkUpdateRoles($validated['user_ids'], $validated['role_id']);
    
        return redirect()->route('admin.users.pending')
            ->with('success', "Roles asignados exitosamente a {$count} usuarios.");
    }


    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        // 9. Delegamos la consulta de exportación al DAO
        $filters = $request->only(['search', 'role']);
        if ($request->has('students_only')) {
            $filters['role'] = 3; // Asumimos role_id 3 es 'estudiante'
        }
        
        $users = $this->userDAO->getUsersForExport($filters);
        
        // ... (El resto de la lógica de exportación, manejo de CSV y headers, se mantiene)
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
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role->name ?? 'Sin rol',
                    $user->student_code ?? '',
                    $user->career ?? '',
                    $user->semester ?? '',
                    $user->phone ?? '',
                    $user->active ? 'Activo' : 'Inactivo',
                    $user->created_at->format('d/m/Y H:i')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}