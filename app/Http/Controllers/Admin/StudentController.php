<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\UsuarioModel;
use App\DataAccessModels\RiskAlertModel;
use App\DataAccessModels\LearningPathModel;
use App\DataAccessModels\EstadisticasModel;
use App\DataAccessModels\RutaAprendizajeModel; // <-- ¡ESTA FALTABA!
use App\DataAccessModels\ProgresoModel; // <-- ¡Y ESTA OTRA TAMBIÉN FALTABA!
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class StudentController extends Controller
{
    protected $usuarioModel;
    protected $riskAlertModel;
    protected $rutaAprendizajeModel;
    protected $progresoModel;        
    protected $learningPathModel; 
    protected $estadisticasModel;

    /**
     * Constructor - Inyección de PDO
     */
    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
        $this->rutaAprendizajeModel = new RutaAprendizajeModel();
        $this->progresoModel = new ProgresoModel();
        $this->estadisticasModel = new EstadisticasModel();
        $this->learningPathModel = new LearningPathModel(); 
        $this->riskAlertModel = new RiskAlertModel();
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
 * Mostrar detalles de un estudiante específico
 * @param int $id
 * @return \Illuminate\View\View
 */
public function show($id)
{
    try {
        // ✅ Obtener datos del estudiante usando PDO
        $studentData = $this->usuarioModel->obtenerUsuario($id);
        
        if (!$studentData) {
            abort(404, 'Estudiante no encontrado.');
        }
        
        $student = (object) $studentData;
        
        // ✅ Agregar campos que podrían faltar
        if (!isset($student->status)) {
            $student->status = $student->active ? 'active' : 'inactive';
        }
        
        if (!isset($student->last_activity) || !$student->last_activity) {
            $student->last_activity = $student->updated_at ?? null;
        }
        
        // Convertir fechas a Carbon si existen
        if ($student->last_activity) {
            $student->last_activity = \Carbon\Carbon::parse($student->last_activity);
        }
        if (isset($student->created_at) && $student->created_at) {
            $student->created_at = \Carbon\Carbon::parse($student->created_at);
        }
        
        // ✅ Obtener progreso usando SP
        $studentProgress = $this->usuarioModel->obtenerProgresoEstudiante($id);
        $student->studentProgress = collect($studentProgress)->map(function($p) {
            $progress = (object) $p;
            
            // Asegurar campos necesarios
            if (!isset($progress->status)) {
                $progress->status = $progress->completed ?? false ? 'completed' : 'in_progress';
            }
            
            return $progress;
        });
        
        // ✅ Obtener rutas de aprendizaje con contenidos usando SP
        $learningPaths = $this->learningPathModel->obtenerRutasConContenidos($id);
        $student->learningPaths = collect($learningPaths)->map(function($path) {
            $path = (object) $path;
            
            // ✅ Asegurar que exista el campo status
            if (!isset($path->status)) {
                // Determinar status basado en progreso
                if (isset($path->progress_percentage)) {
                    if ($path->progress_percentage >= 100) {
                        $path->status = 'completed';
                    } elseif ($path->progress_percentage > 0) {
                        $path->status = 'active';
                    } else {
                        $path->status = 'inactive';
                    }
                } elseif (isset($path->completed) && $path->completed) {
                    $path->status = 'completed';
                } elseif (isset($path->in_progress) && $path->in_progress) {
                    $path->status = 'active';
                } else {
                    $path->status = 'inactive';
                }
            }
            
            // Asegurar progress_percentage
            if (!isset($path->progress_percentage)) {
                $path->progress_percentage = 0;
            }
            
            // Mapear contenidos si existen
            if (isset($path->contents)) {
                $path->contents = collect($path->contents)->map(function($c) {
                    $content = (object) $c;
                    
                    // Asegurar status en contenidos
                    if (!isset($content->status)) {
                        $content->status = $content->completed ?? false ? 'completed' : 'pending';
                    }
                    
                    return $content;
                });
            } else {
                $path->contents = collect([]);
            }
            
            return $path;
        });
        
        // ✅ Obtener alertas de riesgo usando SP
        $riskAlerts = $this->riskAlertModel->obtenerAlertasEstudiante($id);
        $student->riskAlerts = collect($riskAlerts)->map(function($a) {
            $alert = (object) $a;
            
            // Convertir fechas si existen
            if (isset($alert->created_at) && $alert->created_at) {
                $alert->created_at = \Carbon\Carbon::parse($alert->created_at);
            }
            
            // Asegurar campos
            if (!isset($alert->status)) {
                $alert->status = 'active';
            }
            if (!isset($alert->priority)) {
                $alert->priority = 'medium';
            }
            
            return $alert;
        });
        
        // ✅ Calcular nivel de riesgo usando SP
        $riskLevel = $this->riskAlertModel->calcularNivelRiesgo($id);
        $student->riskLevel = (object) array_merge([
            'level' => 'low',
            'score' => 0,
            'factors' => []
        ], $riskLevel ?: []);
        
        // ✅ Obtener recomendaciones usando SP
        $recommendations = $this->usuarioModel->obtenerRecomendaciones($id);
        $student->recommendations = collect($recommendations)->map(function($r) {
            $recommendation = (object) $r;
            
            // Asegurar campos necesarios
            if (!isset($recommendation->status)) {
                $recommendation->status = 'pending';
            }
            if (!isset($recommendation->priority)) {
                $recommendation->priority = 'medium';
            }
            
            return $recommendation;
        });
        
        // ✅ Obtener historial de diagnósticos usando SP
        $diagnosticHistory = $this->usuarioModel->obtenerHistorialDiagnosticos($id);
        $student->diagnosticHistory = collect($diagnosticHistory)->map(function($d) {
            $diagnostic = (object) $d;
            
            // Convertir fechas
            if (isset($diagnostic->completed_at) && $diagnostic->completed_at) {
                $diagnostic->completed_at = \Carbon\Carbon::parse($diagnostic->completed_at);
            }
            
            // Asegurar score
            if (!isset($diagnostic->score)) {
                $diagnostic->score = 0;
            }
            
            return $diagnostic;
        });
        
        // ✅ Obtener todas las estadísticas usando SP
        $stats = $this->estadisticasModel->obtenerEstadisticasEstudiante($id);
        
        // Asegurar estructura completa de stats con valores por defecto
        $student->stats = (object) [
            'progreso_general' => (object) array_merge([
                'total_modules' => 0,
                'completed_modules' => 0,
                'in_progress_modules' => 0,
                'completion_rate' => 0,
                'average_score' => 0
            ], $stats['progreso_general'] ?? []),
            
            'diagnosticos' => (object) array_merge([
                'total_diagnostics' => 0,
                'completed_diagnostics' => 0,
                'average_score' => 0,
                'passed' => 0,
                'failed' => 0
            ], $stats['diagnosticos'] ?? []),
            
            'rutas' => (object) array_merge([
                'total_paths' => 0,
                'active_paths' => 0,
                'completed_paths' => 0,
                'completion_rate' => 0
            ], $stats['rutas'] ?? []),
            
            'recomendaciones' => (object) array_merge([
                'total_recommendations' => 0,
                'completed_recommendations' => 0,
                'pending_recommendations' => 0
            ], $stats['recomendaciones'] ?? [])
        ];
        
        return view('admin.students.show', compact('student'));
        
    } catch (\Exception $e) {
        \Log::error("Error en show student: " . $e->getMessage(), [
            'student_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()
            ->route('admin.students.index')
            ->with('error', 'Error al cargar el estudiante: ' . $e->getMessage());
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