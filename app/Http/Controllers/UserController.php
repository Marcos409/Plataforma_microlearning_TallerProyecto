<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
// use Spatie\Permission\Models\Role; // Comentado temporalmente

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filtros opcionales
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);
        
        // Roles básicos - puedes cambiar esto por tu sistema
        $roles = ['admin', 'teacher', 'student'];

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = ['admin', 'teacher', 'student'];
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,teacher,student'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Agregar campo role a la tabla users
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = ['admin', 'teacher', 'student'];
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,teacher,student'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Actualizar contraseña solo si se proporciona
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Display users with pending role assignment.
     */
    public function pending()
    {
        $users = User::whereNull('role')->latest()->paginate(15);
        $roles = ['admin', 'teacher', 'student'];
        
        return view('users.pending', compact('users', 'roles'));
    }

    /**
     * Bulk assign roles to multiple users.
     */
    public function bulkAssignRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'role' => 'required|in:admin,teacher,student'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $count = User::whereIn('id', $request->user_ids)
                    ->update(['role' => $request->role]);

        return redirect()->back()
            ->with('success', "Rol '{$request->role}' asignado a {$count} usuarios.");
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,teacher,student'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update(['role' => $request->role]);

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado exitosamente.'
        ]);
    }

    /**
     * Export users to CSV.
     */
    public function export()
    {
        $users = User::all();
        
        $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Encabezados del CSV
            fputcsv($file, ['ID', 'Nombre', 'Email', 'Rol', 'Fecha de Registro', 'Email Verificado']);
            
            // Datos de usuarios
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role ?? 'Sin asignar',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->email_verified_at ? 'Sí' : 'No'
                ]);
            }
            
            fclose($file);
        }, 200, $headers);
    }
}