<?php

namespace App\Http\Controllers\Admin; 

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereHas('role', function($q) {
            $q->where('name', 'Estudiante');
        })->with('role');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('career')) {
            $query->where('career', $request->career);
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        $students = $query->orderBy('name')->paginate(15);
        $careers = User::distinct()->pluck('career')->filter();

        return view('admin.students.index', compact('students', 'careers'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'student_code' => 'required|string|max:20|unique:users',
            'career' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:12',
            'phone' => 'nullable|string|max:15',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $studentRole = Role::where('name', 'Estudiante')->first();

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'student_code' => $request->student_code,
            'career' => $request->career,
            'semester' => $request->semester,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $studentRole->id,
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Estudiante creado exitosamente.');
    }

    public function show(User $student)
    {
        $student->load(['studentProgress', 'learningPaths.contents', 'riskAlerts', 'recommendations']);
        
        return view('admin.students.show', compact('student'));
    }

    public function edit(User $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, User $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->id,
            'student_code' => 'required|string|max:20|unique:users,student_code,' . $student->id,
            'career' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:12',
            'phone' => 'nullable|string|max:15',
            'active' => 'boolean',
        ]);

        $student->update($request->only([
            'name', 'email', 'student_code', 'career', 
            'semester', 'phone', 'active'
        ]));

        return redirect()->route('admin.students.index')
            ->with('success', 'Estudiante actualizado exitosamente.');
    }

    public function destroy(User $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Estudiante eliminado exitosamente.');
    }

    public function export(Request $request)
    {
        $query = User::whereHas('role', function($q) {
            $q->where('name', 'Estudiante');
        })->with('role');

        if ($request->filled('career')) {
            $query->where('career', $request->career);
        }

        $students = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="estudiantes.csv"',
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Código', 'Nombre', 'Email', 'Carrera', 'Semestre', 'Teléfono', 'Estado']);

            foreach ($students as $student) {
                fputcsv($file, [
                    $student->student_code,
                    $student->name,
                    $student->email,
                    $student->career,
                    $student->semester,
                    $student->phone,
                    $student->active ? 'Activo' : 'Inactivo'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
