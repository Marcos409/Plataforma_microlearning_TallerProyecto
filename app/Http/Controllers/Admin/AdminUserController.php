<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AdminUserController extends Controller
{
    /**
     * Display a listing of all users
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        $users = $query->orderBy('name')->paginate(15);
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
        $request->validate([
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

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'student_code' => $request->student_code,
            'career' => $request->career,
            'semester' => $request->semester,
            'phone' => $request->phone,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['role', 'studentProgress', 'learningPaths', 'recommendations']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */

     public function update(Request $request, User $user)
     {
         $rules = [
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
             'role_id' => 'required|exists:roles,id',
             'student_code' => 'nullable|string|max:20|unique:users,student_code,' . $user->id,
             'career' => 'nullable|string|max:255',
             'semester' => 'nullable|integer|min:1|max:12',
             'phone' => 'nullable|string|max:15',
             'active' => 'boolean',
         ];
     
         // Solo validar contraseña si se proporciona
         if ($request->filled('password')) {
             $rules['password'] = 'string|min:8|confirmed';
         }
     
         $validated = $request->validate($rules);
     
         // Preparar datos para actualizar
         $dataToUpdate = [
             'name' => $validated['name'],
             'email' => $validated['email'],
             'role_id' => $validated['role_id'],
             'student_code' => $validated['student_code'] ?? null,
             'career' => $validated['career'] ?? null,
             'semester' => $validated['semester'] ?? null,
             'phone' => $validated['phone'] ?? null,
             'active' => $request->boolean('active', true),
         ];
     
         // Si se proporciona contraseña, hashearla
         if ($request->filled('password')) {
             $dataToUpdate['password'] = Hash::make($request->password);
         }
     
         $user->update($dataToUpdate);
     
         return redirect()->route('admin.users.index')
             ->with('success', 'Usuario actualizado exitosamente.');
     }



    /**public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'student_code' => 'nullable|string|max:20|unique:users,student_code,' . $user->id,
            'career' => 'nullable|string|max:255',
            'semester' => 'nullable|integer|min:1|max:12',
            'phone' => 'nullable|string|max:15',
            'active' => 'boolean',
        ]);

        $user->update($request->only([
            'name', 'email', 'role_id', 'student_code', 'career', 
            'semester', 'phone', 'active'
        ]));

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }*/


    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        // No permitir eliminar al admin principal
        if ($user->id === 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No se puede eliminar el administrador principal.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Update user role via AJAX
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $user->update(['role_id' => $request->role_id]);

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
        $users = User::whereNull('role_id')->orWhere('role_id', 0)
                    ->orderBy('created_at', 'desc')
                    ->paginate(15); // Agregué paginación para consistencia
        $roles = Role::all();
        
        return view('admin.users.pending', compact('users', 'roles'));
    }


    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);
    
        // Debug: verifica los datos
    
        $user->update(['role_id' => $request->role_id]);
    
        // Verifica que se guardó
        $user->refresh();
    
        return redirect()->route('admin.users.pending')
            ->with('success', "Rol asignado correctamente a {$user->name}");
    }





    /**
     * Assign roles to multiple users
     */
    public function bulkAssignRole(Request $request)
{
    $request->validate([
        'user_ids' => 'required|array',
        'user_ids.*' => 'exists:users,id',
        'role_id' => 'required|exists:roles,id'
    ]);

    User::whereIn('id', $request->user_ids)
        ->update(['role_id' => $request->role_id]);

    $count = count($request->user_ids);
    
    return redirect()->route('admin.users.pending')
        ->with('success', "Roles asignados exitosamente a {$count} usuarios.");
}


    /**
     * Export users to CSV
     */
    public function export(Request $request)
{
    $query = User::with('role');

    // Filtros
    if ($request->filled('role')) {
        $query->where('role_id', $request->role);
    } elseif ($request->has('students_only')) {
        $query->where('role_id', 3);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('student_code', 'like', "%{$search}%");
        });
    }

    $users = $query->get();
    
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
        
        // BOM para UTF-8 (necesario para Excel)
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados - usar punto y coma como delimitador
        fputcsv($file, [
            'ID',
            'Nombre',
            'Email',
            'Rol',
            'Código',
            'Carrera',
            'Semestre',
            'Teléfono',
            'Estado',
            'Fecha Registro'
        ], ';'); // ← Punto y coma como delimitador

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
            ], ';'); // ← Punto y coma como delimitador
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
    
}