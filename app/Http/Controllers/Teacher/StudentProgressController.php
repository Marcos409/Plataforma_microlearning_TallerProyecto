<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentProgressController extends Controller
{
    /**
     * Display a listing of students for the teacher.
     */
    public function index()
    {
        // Obtener todos los estudiantes usando la misma estructura que AdminStudentController
        $students = User::whereHas('role', function($q) {
            $q->where('name', 'Estudiante'); // o 'student' según tu BD
        })->with('role')->get();
        
        return view('teacher.students.index', compact('students'));
    }

    /**
     * Display the specified student's progress.
     */
    public function show(User $student)
    {
        // Verificar que el usuario sea estudiante
        if (!$student->isStudent()) {
            abort(404);
        }

        // Aquí puedes obtener el progreso del estudiante
        // $progress = $student->progress; // Dependiendo de tu modelo de datos
        
        return view('teacher.students.show', compact('student'));
    }

    /**
     * Recommend content to a student.
     */
    public function recommendContent(Request $request, User $student)
    {
        // Verificar que el usuario sea estudiante
        if (!$student->isStudent()) {
            abort(404);
        }

        // Lógica para recomendar contenido
        // Esto dependerá de tu modelo de datos

        return redirect()->back()->with('success', 'Contenido recomendado exitosamente.');
    }
}