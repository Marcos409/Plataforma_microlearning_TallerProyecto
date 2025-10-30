<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\UsuarioModel;
use App\DataAccessModels\RiskAlertModel;
use App\DataAccessModels\LearningPathModel;
use App\DataAccessModels\EstadisticasModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class StudentController extends Controller
{
    protected $usuarioModel;
    protected $riskAlertModel;        
    protected $learningPathModel; 
    protected $estadisticasModel;

    /**
     * Constructor - Inyección de PDO
     */
    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Listar estudiantes con filtros
     */
    public function index(Request $request)
    {
        // Obtener todos los estudiantes (role_id = 3)
        $studentsData = $this->usuarioModel->obtenerEstudiantes();
        
        // Convertir a colección para aplicar filtros
        $students = collect($studentsData);

        // Aplicar filtro de búsqueda
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $students = $students->filter(function($student) use ($search) {
                return str_contains(strtolower($student['name']), $search) ||
                       str_contains(strtolower($student['email']), $search) ||
                       str_contains(strtolower($student['student_code'] ?? ''), $search);
            });
        }

        // Aplicar filtro de carrera
        if ($request->filled('career')) {
            $students = $students->filter(function($student) use ($request) {
                return $student['career'] === $request->career;
            });
        }

        // Aplicar filtro de semestre
        if ($request->filled('semester')) {
            $students = $students->filter(function($student) use ($request) {
                return $student['semester'] == $request->semester;
            });
        }

        // Ordenar por nombre
        $students = $students->sortBy('name')->values();

        // Obtener carreras únicas para el filtro
        $careers = collect($studentsData)
            ->pluck('career')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Paginación manual
        $perPage = 15;
        $currentPage = $request->input('page', 1);
        $total = $students->count();
        
        $paginatedStudents = new \Illuminate\Pagination\LengthAwarePaginator(
            $students->forPage($currentPage, $perPage),
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.students.index', [
            'students' => $paginatedStudents,
            'careers' => $careers
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('admin.students.create');
    }

    /**
     * Crear nuevo estudiante
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'student_code' => 'required|string|max:20|unique:users',
            'career' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:12',
            'phone' => 'nullable|string|max:15',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'Debe ser un correo electrónico válido',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'student_code.required' => 'El código de estudiante es obligatorio',
            'student_code.unique' => 'Este código de estudiante ya está en uso',
            'career.required' => 'La carrera es obligatoria',
            'semester.required' => 'El semestre es obligatorio',
            'semester.min' => 'El semestre debe ser mayor a 0',
            'semester.max' => 'El semestre no puede ser mayor a 12',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        try {
            // Rol de estudiante = 3
            $studentRoleId = 3;

            // Crear estudiante usando PDO
            $userId = $this->usuarioModel->crearUsuario(
                $validated['name'],
                $validated['email'],
                Hash::make($validated['password']),
                $studentRoleId,
                $validated['student_code'],
                $validated['career']
            );

            // Si se creó exitosamente, actualizar campos adicionales
            if ($userId) {
                // Actualizar semestre y teléfono usando sp_actualizar_usuario_completo
                $this->usuarioModel->actualizarUsuarioCompleto(
                    $userId,
                    $validated['name'],
                    $validated['email'],
                    $studentRoleId,
                    $validated['student_code'],
                    $validated['career'],
                    $validated['semester'],
                    $validated['phone'] ?? null
                );

                return redirect()
                    ->route('admin.students.index')
                    ->with('success', 'Estudiante creado exitosamente.');
            } else {
                throw new \Exception('No se pudo crear el estudiante');
            }

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al crear el estudiante: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar detalles del estudiante
     */
    public function show($id)
    {
        try {
            // Obtener datos básicos del estudiante
            $student = $this->usuarioModel->obtenerUsuario($id);
            
            if (!$student) {
                return redirect()
                    ->route('admin.students.index')
                    ->with('error', 'Estudiante no encontrado');
            }

            $student = (object) $student;

            // Obtener progreso usando SP
            $studentProgress = $this->usuarioModel->obtenerProgresoEstudiante($id);
            $student->studentProgress = collect($studentProgress)->map(fn($p) => (object) $p);

            // Obtener rutas de aprendizaje con contenidos usando SP
            $learningPaths = $this->learningPathModel->obtenerRutasConContenidos($id);
            $student->learningPaths = collect($learningPaths)->map(function($path) {
                $path = (object) $path;
                if (isset($path->contents)) {
                    $path->contents = collect($path->contents)->map(fn($c) => (object) $c);
                }
                return $path;
            });

            // Obtener alertas de riesgo usando SP
            $riskAlerts = $this->riskAlertModel->obtenerAlertasEstudiante($id);
            $student->riskAlerts = collect($riskAlerts)->map(fn($a) => (object) $a);

            // Calcular nivel de riesgo usando SP
            $riskLevel = $this->riskAlertModel->calcularNivelRiesgo($id);
            $student->riskLevel = (object) $riskLevel;

            // Obtener recomendaciones usando SP
            $recommendations = $this->usuarioModel->obtenerRecomendaciones($id);
            $student->recommendations = collect($recommendations)->map(fn($r) => (object) $r);

            // Obtener historial de diagnósticos usando SP
            $diagnosticHistory = $this->usuarioModel->obtenerHistorialDiagnosticos($id);
            $student->diagnosticHistory = collect($diagnosticHistory)->map(fn($d) => (object) $d);

            // Obtener todas las estadísticas usando SP
            $stats = $this->estadisticasModel->obtenerEstadisticasEstudiante($id);
            $student->stats = (object) [
                'progreso_general' => (object) ($stats['progreso_general'] ?? []),
                'diagnosticos' => (object) ($stats['diagnosticos'] ?? []),
                'rutas' => (object) ($stats['rutas'] ?? []),
                'recomendaciones' => (object) ($stats['recomendaciones'] ?? [])
            ];

            return view('admin.students.show', compact('student'));

        } catch (\Exception $e) {
            error_log("Error en show student: " . $e->getMessage());
            return redirect()
                ->route('admin.students.index')
                ->with('error', 'Error al cargar el estudiante');
        }
    }
    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        try {
            $student = $this->usuarioModel->obtenerUsuario($id);
            
            if (!$student) {
                return redirect()
                    ->route('admin.students.index')
                    ->with('error', 'Estudiante no encontrado');
            }

            // Convertir a objeto
            $student = (object) $student;

            return view('admin.students.edit', compact('student'));

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.students.index')
                ->with('error', 'Error al cargar el estudiante');
        }
    }

    /**
     * Actualizar estudiante
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'student_code' => 'required|string|max:20|unique:users,student_code,' . $id,
            'career' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:12',
            'phone' => 'nullable|string|max:15',
            'active' => 'boolean',
        ], [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo electrónico es obligatorio',
            'email.unique' => 'Este correo electrónico ya está en uso',
            'student_code.required' => 'El código de estudiante es obligatorio',
            'student_code.unique' => 'Este código de estudiante ya está en uso',
            'career.required' => 'La carrera es obligatoria',
            'semester.required' => 'El semestre es obligatorio',
        ]);

        try {
            // Rol de estudiante = 3
            $studentRoleId = 3;

            // Actualizar usando PDO
            $result = $this->usuarioModel->actualizarUsuarioCompleto(
                $id,
                $validated['name'],
                $validated['email'],
                $studentRoleId,
                $validated['student_code'],
                $validated['career'],
                $validated['semester'],
                $validated['phone'] ?? null
            );

            // Actualizar estado si se proporcionó
            if ($request->has('active')) {
                $this->usuarioModel->cambiarEstadoUsuario($id, $request->active ? 1 : 0);
            }

            return redirect()
                ->route('admin.students.index')
                ->with('success', 'Estudiante actualizado exitosamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al actualizar el estudiante')
                ->withInput();
        }
    }

    /**
     * Eliminar estudiante
     */
    public function destroy($id)
    {
        try {
            $result = $this->usuarioModel->eliminarUsuario($id);

            if ($result) {
                return redirect()
                    ->route('admin.students.index')
                    ->with('success', 'Estudiante eliminado exitosamente.');
            } else {
                throw new \Exception('No se pudo eliminar el estudiante');
            }

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.students.index')
                ->with('error', 'Error al eliminar el estudiante');
        }
    }

    /**
     * Exportar estudiantes a CSV
     */
    public function export(Request $request)
    {
        // Obtener estudiantes
        $studentsData = $this->usuarioModel->obtenerEstudiantes();
        $students = collect($studentsData);

        // Aplicar filtro de carrera si existe
        if ($request->filled('career')) {
            $students = $students->filter(function($student) use ($request) {
                return $student['career'] === $request->career;
            });
        }

        $filename = 'estudiantes_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'Código', 'Nombre', 'Email', 'Carrera', 
                'Semestre', 'Teléfono', 'Estado'
            ]);

            // Datos
            foreach ($students as $student) {
                fputcsv($file, [
                    $student['student_code'] ?? 'N/A',
                    $student['name'],
                    $student['email'],
                    $student['career'] ?? 'N/A',
                    $student['semester'] ?? 'N/A',
                    $student['phone'] ?? 'N/A',
                    ($student['active'] ?? 1) ? 'Activo' : 'Inactivo'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}