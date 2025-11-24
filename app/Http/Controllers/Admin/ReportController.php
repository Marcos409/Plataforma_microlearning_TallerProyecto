<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\UsuarioModel;
use App\DataAccessModels\DiagnosticoModel;
use App\DataAccessModels\ProgresoModel;
use App\DataAccessModels\ContenidoModel;
use App\DataAccessModels\AnalisisModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ReportController extends Controller
{
    protected $usuarioModel;
    protected $diagnosticoModel;
    protected $progresoModel;
    protected $contenidoModel;
    protected $analisisModel;
    protected $pdo;

    /**
     * Constructor - Inyección de modelos PDO
     * ✅ ARREGLADO: Obtiene PDO desde la conexión de Laravel
     */
    public function __construct()
    {
        // ✅ Obtener PDO desde la conexión activa de Laravel
        $this->pdo = DB::connection()->getPdo();
        
        $this->usuarioModel = new UsuarioModel();
        $this->diagnosticoModel = new DiagnosticoModel($this->pdo);
        $this->progresoModel = new ProgresoModel();
        $this->contenidoModel = new ContenidoModel();
        $this->analisisModel = new AnalisisModel();
    }

    /**
     * Vista principal de reportes
     */
    public function index()
    {
        // ✅ Métricas generales usando PDO
        $totalStudents = $this->usuarioModel->contarPorRol(3);
        $totalTeachers = $this->usuarioModel->contarPorRol(2);
        $totalContent = $this->contenidoModel->contarContenidosPorTipo();
        $completedActivities = $this->progresoModel->obtenerTotalActividadesCompletadas();
        
        return view('admin.reports.index', compact(
            'totalStudents',
            'totalTeachers',
            'totalContent',
            'completedActivities'
        ));
    }

    /**
 * Reporte de estudiantes
 */
public function students(Request $request)
{
    // ✅ Obtener estudiantes con filtros usando PDO
    $search = $request->get('search');
    $status = $request->get('status');
    
    $studentsData = $this->usuarioModel->obtenerEstudiantesFiltros($search, $status);
    
    // Enriquecer con métricas
    $students = collect($studentsData)->map(function($student) {
        $student = (object) $student;
        
        // Métricas del estudiante
        $student->total_diagnostics = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
        $student->completed_activities = $this->usuarioModel->contarActividadesCompletadas($student->id);
        
        $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
        $student->average_score = $performance['percentage'];
        
        // ✅ AGREGADO: Asegurar que last_login_at exista
        if (!isset($student->last_login_at)) {
            $student->last_login_at = null;
        }
        
        // ✅ AGREGADO: Calcular nivel de riesgo
        $score = $student->average_score ?? 0;
        if ($score >= 70) {
            $student->risk_level = 0; // Bajo
        } elseif ($score >= 50) {
            $student->risk_level = 1; // Medio
        } elseif ($score >= 30) {
            $student->risk_level = 2; // Alto
        } else {
            $student->risk_level = 3; // Crítico
        }
        
        return $student;
    });
    
    // Paginación manual
    $perPage = 20;
    $currentPage = $request->input('page', 1);
    $total = $students->count();
    
    $paginatedStudents = new \Illuminate\Pagination\LengthAwarePaginator(
        $students->forPage($currentPage, $perPage),
        $total,
        $perPage,
        $currentPage,
        ['path' => $request->url(), 'query' => $request->query()]
    );
    
    return view('admin.reports.students', [
        'students' => $paginatedStudents,
        'search' => $search,
        'status' => $status
    ]);
}

    /**
     * Reporte de rendimiento académico
     */
    public function performance(Request $request)
    {
        // ✅ Rendimiento por materia usando PDO
        $performanceBySubject = $this->diagnosticoModel->obtenerRendimientoPorMateria();
        
        // ✅ Top 10 estudiantes
        $allStudents = $this->usuarioModel->obtenerEstudiantes();
        
        $topStudents = collect($allStudents)->map(function($student) {
            $student = (object) $student;
            $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
            
            $student->total_responses = $performance['total_responses'];
            $student->correct_responses = $performance['correct_responses'];
            $student->average_score = $performance['percentage'];
            
            return $student;
        })
        ->filter(fn($s) => $s->total_responses > 0)
        ->sortByDesc('average_score')
        ->take(10)
        ->values();
        
        // Rendimiento mensual (últimos 6 meses)
        $performanceByMonth = $this->getPerformanceByMonth();
        
        return view('admin.reports.performance', compact(
            'performanceBySubject',
            'topStudents',
            'performanceByMonth'
        ));
    }

        /**
     * Reporte de estudiantes en riesgo
     */
    public function risk(Request $request)
    {
        // ✅ Obtener estudiantes usando PDO
        $studentsData = $this->usuarioModel->obtenerEstudiantes();
        $students = collect($studentsData)->map(function($s) {
            $student = (object) $s;
            
            // ✅ Convertir fechas a Carbon
            $student->last_activity = isset($student->last_activity) && $student->last_activity
                ? \Carbon\Carbon::parse($student->last_activity)
                : null;
            
            $student->created_at = isset($student->created_at) && $student->created_at
                ? \Carbon\Carbon::parse($student->created_at)
                : \Carbon\Carbon::now();
            
            // ✅ Agregar TODAS las métricas necesarias
            $student->total_diagnostics = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
            
            $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
            $student->average_score = $performance['percentage'];
            $student->total_responses = $performance['total_responses'] ?? 0;
            $student->correct_responses = $performance['correct_responses'] ?? 0;
            
            // ✅ Propiedades adicionales que la vista podría necesitar
            $student->diagnosticResponses = $student->total_diagnostics; // Alias
            
            return $student;
        });
        
        // Calcular nivel de riesgo
        $atRiskStudents = $students->map(function($student) {
            $riskLevel = $this->calculateRiskLevel($student);
            
            if ($riskLevel >= 1) {
                $student->risk_level = $riskLevel;
                $student->risk_factors = $this->identifyRiskFactors($student);
                return $student;
            }
            
            return null;
        })
        ->filter()
        ->sortByDesc('risk_level')
        ->values();
        
        return view('admin.reports.risk', compact('atRiskStudents'));
    }
    /**
     * Reporte de contenidos
     */
    public function content(Request $request)
    {
        // ✅ Estadísticas de contenidos usando PDO
        $statistics = $this->contenidoModel->obtenerEstadisticasContenidos();
        
        // Contenidos más vistos
        $topContent = $this->contenidoModel->contenidosMasVistos(10);
        
        // Contenidos por tipo
        $contentByType = $this->contenidoModel->contarContenidosPorTipo();
        
        return view('admin.reports.content', compact(
            'statistics',
            'topContent',
            'contentByType'
        ));
    }

    /**
     * Reporte de progreso general
     */
    public function progress(Request $request)
    {
        // ✅ Estudiantes por carrera usando PDO
        $studentsByCareer = $this->progresoModel->estudiantesPorCarrera();
        
        // Dashboard docente
        $dashboardData = $this->progresoModel->dashboardDocente();
        
        return view('admin.reports.progress', compact(
            'studentsByCareer',
            'dashboardData'
        ));
    }

    /**
     * Reporte de análisis ML
     */
    public function mlAnalysis(Request $request)
    {
        // ✅ Estadísticas de ML usando PDO
        $statistics = $this->analisisModel->obtenerEstadisticas();
        
        // Estudiantes sin análisis reciente
        $studentsSinAnalisis = $this->analisisModel->obtenerEstudiantesSinAnalisis();
        
        return view('admin.reports.ml-analysis', compact(
            'statistics',
            'studentsSinAnalisis'
        ));
    }

    /**
     * Exportar reporte a CSV
     */
    public function exportCsv(Request $request)
    {
        $type = $request->get('type', 'students');
        
        switch ($type) {
            case 'students':
                return $this->exportStudentsCsv();
            case 'performance':
                return $this->exportPerformanceCsv();
            case 'content':
                return $this->exportContentCsv();
            default:
                return redirect()->back()->with('error', 'Tipo de reporte inválido');
        }
    }

    /**
     * Exportar estudiantes a CSV
     */
    private function exportStudentsCsv()
    {
        $students = $this->usuarioModel->obtenerEstudiantes();
        
        $filename = 'reporte_estudiantes_' . date('Y-m-d_His') . '.csv';
        
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
                'ID', 'Nombre', 'Email', 'Código Estudiante',
                'Carrera', 'Semestre', 'Estado', 'Fecha Registro'
            ]);
            
            // Datos
            foreach ($students as $student) {
                fputcsv($file, [
                    $student['id'],
                    $student['name'],
                    $student['email'],
                    $student['student_code'] ?? 'N/A',
                    $student['career'] ?? 'N/A',
                    $student['semester'] ?? 'N/A',
                    $student['active'] ? 'Activo' : 'Inactivo',
                    $student['created_at']
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    /**
     * Exportar rendimiento a CSV
     */
    private function exportPerformanceCsv()
    {
        $performance = $this->diagnosticoModel->obtenerRendimientoPorMateria();
        
        $filename = 'reporte_rendimiento_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($performance) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'Materia', 'Total Estudiantes', 'Intentos Totales',
                'Promedio', 'Aprobados'
            ]);
            
            foreach ($performance as $row) {
                fputcsv($file, [
                    $row['subject_area'],
                    $row['total_students'],
                    $row['total_attempts'],
                    round($row['avg_score'], 2) . '%',
                    $row['passed_count']
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    /**
     * Exportar contenidos a CSV
     */
    private function exportContentCsv()
    {
        $contents = $this->contenidoModel->listarContenidos(null, null, null);
        
        $filename = 'reporte_contenidos_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($contents) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID', 'Título', 'Área', 'Tipo', 'Dificultad',
                'Duración (min)', 'Vistas', 'Estado', 'Fecha Creación'
            ]);
            
            foreach ($contents as $content) {
                fputcsv($file, [
                    $content['id'],
                    $content['title'],
                    $content['subject_area'],
                    $content['type'],
                    $content['difficulty_level'],
                    $content['duration_minutes'] ?? 'N/A',
                    $content['views'],
                    $content['active'] ? 'Activo' : 'Inactivo',
                    $content['created_at']
                ]);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    /**
     * Generar reporte PDF (placeholder)
     */
    public function exportPdf(Request $request)
    {
        // TODO: Implementar con DomPDF o similar
        return redirect()->back()
            ->with('info', 'Exportación a PDF en desarrollo');
    }

    /**
     * Dashboard de reportes con gráficos
     */
    public function dashboard()
    {
        // Métricas generales
        $totalStudents = $this->usuarioModel->contarPorRol(3);
        $totalTeachers = $this->usuarioModel->contarPorRol(2);
        
        // Rendimiento por materia
        $performanceBySubject = $this->diagnosticoModel->obtenerRendimientoPorMateria();
        
        // Contenidos por tipo
        $contentByType = $this->contenidoModel->contarContenidosPorTipo();
        
        // ML Statistics
        $mlStats = $this->analisisModel->obtenerEstadisticas();
        
        return view('admin.reports.dashboard', compact(
            'totalStudents',
            'totalTeachers',
            'performanceBySubject',
            'contentByType',
            'mlStats'
        ));
    }
 
/**
 * Generar reporte personalizado
 */
public function generateReport(Request $request)
{
    $reportType = $request->input('report_type');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    
    switch ($reportType) {
        case 'performance':
        case 'rendimiento-materia':
            return $this->generatePerformanceReport($startDate, $endDate);
            
        case 'students':
        case 'diagnosticos':
        case 'diagnostics':
            return $this->generateDiagnosticsFullReport($startDate, $endDate);
            
        case 'progreso-general':
        case 'progress':
        case 'general':  // ✅ AGREGADO
            return $this->generateProgressReport($startDate, $endDate);
            
        case 'uso-plataforma':
        case 'usage':
            return $this->generatePlatformUsageReport($startDate, $endDate);
            
        case 'rendimiento-carrera':
        case 'career':
            return $this->generateCareerPerformanceReport($startDate, $endDate);
            
        default:
            return redirect()->back()->with('error', 'Tipo de reporte no válido: ' . $reportType);
    }
}

/**
 * Generar reporte de rendimiento en CSV
 */
private function generatePerformanceReport($startDate, $endDate)
{
    // Obtener datos
    $performanceBySubject = $this->diagnosticoModel->obtenerRendimientoPorMateria();
    
    $filename = 'reporte-rendimiento-' . date('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($performanceBySubject, $startDate, $endDate) {
        $file = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Información del reporte
        fputcsv($file, ['REPORTE DE RENDIMIENTO ACADÉMICO']);
        fputcsv($file, ['Fecha de generación:', date('d/m/Y H:i:s')]);
        if ($startDate && $endDate) {
            fputcsv($file, ['Período:', $startDate . ' - ' . $endDate]);
        }
        fputcsv($file, []); // Línea vacía
        
        // Encabezados
        fputcsv($file, [
            'Materia',
            'Total Estudiantes',
            'Total Intentos',
            'Promedio (%)',
            'Aprobados',
            'Estado'
        ]);
        
        // Datos
        foreach ($performanceBySubject as $subject) {
            $avg = round($subject['avg_score'], 2);
            $estado = $avg >= 70 ? 'Excelente' : ($avg >= 50 ? 'Regular' : 'Crítico');
            
            fputcsv($file, [
                $subject['subject_area'],
                $subject['total_students'] ?? 0,
                $subject['total_attempts'],
                $avg,
                $subject['passed_count'] ?? 0,
                $estado
            ]);
        }
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}

/**
 * Generar reporte de estudiantes en CSV
 */
private function generateStudentsReport($startDate, $endDate)
{
    // Obtener todos los estudiantes sin paginación
    $studentsData = $this->usuarioModel->obtenerEstudiantesFiltros(null, null);
    
    $students = collect($studentsData)->map(function($student) {
        $student = (object) $student;
        $student->total_diagnostics = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
        $student->completed_activities = $this->usuarioModel->contarActividadesCompletadas($student->id);
        
        $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
        $student->average_score = $performance['percentage'];
        
        if (!isset($student->last_login_at)) {
            $student->last_login_at = null;
        }
        
        $score = $student->average_score ?? 0;
        if ($score >= 70) {
            $student->risk_level = 0;
        } elseif ($score >= 50) {
            $student->risk_level = 1;
        } elseif ($score >= 30) {
            $student->risk_level = 2;
        } else {
            $student->risk_level = 3;
        }
        
        return $student;
    });
    
    $filename = 'reporte-estudiantes-' . date('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($students, $startDate, $endDate) {
        $file = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Información del reporte
        fputcsv($file, ['REPORTE DE ESTUDIANTES']);
        fputcsv($file, ['Fecha de generación:', date('d/m/Y H:i:s')]);
        fputcsv($file, ['Total de estudiantes:', $students->count()]);
        if ($startDate && $endDate) {
            fputcsv($file, ['Período:', $startDate . ' - ' . $endDate]);
        }
        fputcsv($file, []); // Línea vacía
        
        // Encabezados
        fputcsv($file, [
            'ID',
            'Nombre',
            'Email',
            'Diagnósticos Completados',
            'Actividades Completadas',
            'Promedio (%)',
            'Nivel de Riesgo',
            'Último Acceso'
        ]);
        
        // Datos
        $riskLevels = ['Bajo', 'Medio', 'Alto', 'Crítico'];
        
        foreach ($students as $student) {
            fputcsv($file, [
                $student->id,
                $student->name,
                $student->email,
                $student->total_diagnostics,
                $student->completed_activities,
                round($student->average_score, 2),
                $riskLevels[$student->risk_level],
                $student->last_login_at 
                    ? date('d/m/Y H:i', strtotime($student->last_login_at)) 
                    : 'Nunca'
            ]);
        }
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}

/**
 * Generar reporte de diagnósticos en CSV
 */
private function generateDiagnosticsReport($startDate, $endDate)
{
    // Obtener datos de diagnósticos
    $diagnostics = $this->diagnosticoModel->obtenerRendimientoPorMateria();
    
    $filename = 'reporte-diagnosticos-' . date('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($diagnostics, $startDate, $endDate) {
        $file = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Información del reporte
        fputcsv($file, ['REPORTE DE DIAGNÓSTICOS']);
        fputcsv($file, ['Fecha de generación:', date('d/m/Y H:i:s')]);
        if ($startDate && $endDate) {
            fputcsv($file, ['Período:', $startDate . ' - ' . $endDate]);
        }
        fputcsv($file, []); // Línea vacía
        
        // Encabezados
        fputcsv($file, [
            'Área/Materia',
            'Total Intentos',
            'Promedio (%)',
            'Total Estudiantes',
            'Aprobados'
        ]);
        
        // Datos
        foreach ($diagnostics as $diagnostic) {
            fputcsv($file, [
                $diagnostic['subject_area'],
                $diagnostic['total_attempts'],
                round($diagnostic['avg_score'], 2),
                $diagnostic['total_students'] ?? 0,
                $diagnostic['passed_count'] ?? 0
            ]);
        }
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}
/**
 * Generar reporte de progreso general en CSV
 */
private function generateProgressReport($startDate, $endDate)
{
    // ✅ Obtener TODOS los estudiantes
    $studentsData = $this->usuarioModel->obtenerEstudiantes();
    
    // Agrupar estudiantes por carrera
    $studentsByCareer = collect($studentsData)->groupBy(function($student) {
        $s = is_array($student) ? (object)$student : $student;
        return $s->career ?? $s->carrera ?? 'Sin Carrera';
    })->map(function($students, $career) {
        $total = $students->count();
        
        $activos = $students->filter(function($student) {
            $s = is_array($student) ? (object)$student : $student;
            $lastActivity = $s->last_activity ?? $s->last_login_at ?? null;
            
            if ($lastActivity) {
                $lastActivityTime = strtotime($lastActivity);
                $thirtyDaysAgo = strtotime('-30 days');
                return $lastActivityTime >= $thirtyDaysAgo;
            }
            return false;
        })->count();
        
        $porcentaje = $total > 0 ? round(($activos / $total) * 100, 2) : 0;
        
        return [
            'career' => $career,
            'total' => $total,
            'activos' => $activos,
            'porcentaje' => $porcentaje
        ];
    })->sortByDesc('total');
    
    // ✅ Calcular métricas generales CON MÁS DETALLE
    $totalStudents = count($studentsData);
    
    // Estudiantes con actividad (cualquier login registrado)
    $studentsWithActivity = collect($studentsData)->filter(function($student) {
        $s = is_array($student) ? (object)$student : $student;
        $lastActivity = $s->last_activity ?? $s->last_login_at ?? $s->last_login ?? null;
        return !empty($lastActivity);
    })->count();
    
    // Contadores
    $atRiskCount = 0;
    $goodProgressCount = 0;
    $mediumProgressCount = 0;
    $studentsWithDiagnostics = 0;
    $studentsWithoutDiagnostics = 0;
    
    foreach ($studentsData as $student) {
        $s = is_array($student) ? (object)$student : $student;
        
        try {
            // Contar diagnósticos del estudiante
            $totalDiagnostics = $this->diagnosticoModel->contarDiagnosticosCompletados($s->id);
            
            if ($totalDiagnostics > 0) {
                $studentsWithDiagnostics++;
                
                // Obtener rendimiento
                $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($s->id);
                $avgScore = $performance['percentage'] ?? 0;
                
                if ($avgScore >= 70) {
                    $goodProgressCount++;
                } elseif ($avgScore >= 50) {
                    $mediumProgressCount++;
                } else {
                    $atRiskCount++;
                }
            } else {
                $studentsWithoutDiagnostics++;
            }
        } catch (\Exception $e) {
            $studentsWithoutDiagnostics++;
            continue;
        }
    }
    
    // Diagnósticos de hoy
    $diagnosticsToday = 0;
    try {
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as count 
            FROM diagnostic_responses 
            WHERE DATE(created_at) = CURDATE()
        ");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $diagnosticsToday = $result['count'] ?? 0;
    } catch (\Exception $e) {
        $diagnosticsToday = 0;
    }
    
    // Diagnósticos totales en el sistema
    $diagnosticsTotal = 0;
    try {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM diagnostic_responses");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $diagnosticsTotal = $result['count'] ?? 0;
    } catch (\Exception $e) {
        $diagnosticsTotal = 0;
    }
    
    $dashboardData = [
        'total_students' => $totalStudents,
        'students_with_activity' => $studentsWithActivity,
        'students_with_diagnostics' => $studentsWithDiagnostics,
        'students_without_diagnostics' => $studentsWithoutDiagnostics,
        'good_progress' => $goodProgressCount,
        'medium_progress' => $mediumProgressCount,
        'at_risk' => $atRiskCount,
        'diagnostics_today' => $diagnosticsToday,
        'diagnostics_total' => $diagnosticsTotal
    ];
    
    $filename = 'reporte-progreso-general-' . date('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($studentsByCareer, $dashboardData, $startDate, $endDate) {
        $file = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        $delimiter = ';';
        
        // ✅ TÍTULO DEL REPORTE
        fputcsv($file, ['REPORTE DE PROGRESO GENERAL', '', '', ''], $delimiter);
        fputcsv($file, ['Fecha de generación', date('d/m/Y H:i:s'), '', ''], $delimiter);
        if ($startDate && $endDate) {
            fputcsv($file, ['Período', $startDate . ' - ' . $endDate, '', ''], $delimiter);
        }
        fputcsv($file, ['', '', '', ''], $delimiter);
        
        // ✅ SECCIÓN: ESTUDIANTES POR CARRERA
        fputcsv($file, ['ESTUDIANTES POR CARRERA', '', '', ''], $delimiter);
        fputcsv($file, ['Carrera', 'Total Estudiantes', 'Activos (últimos 30 días)', 'Porcentaje Actividad'], $delimiter);
        
        if ($studentsByCareer->count() > 0) {
            foreach ($studentsByCareer as $career) {
                fputcsv($file, [
                    $career['career'],
                    $career['total'],
                    $career['activos'],
                    $career['porcentaje'] . '%'
                ], $delimiter);
            }
        } else {
            fputcsv($file, ['No hay datos de estudiantes por carrera', '', '', ''], $delimiter);
        }
        
        fputcsv($file, ['', '', '', ''], $delimiter);
        
        // ✅ SECCIÓN: MÉTRICAS GENERALES
        fputcsv($file, ['MÉTRICAS GENERALES', '', '', ''], $delimiter);
        fputcsv($file, ['Métrica', 'Valor', 'Porcentaje', ''], $delimiter);
        
        $total = $dashboardData['total_students'];
        
        fputcsv($file, [
            'Total de Estudiantes', 
            $dashboardData['total_students'], 
            '100%',
            ''
        ], $delimiter);
        
        fputcsv($file, [
            'Estudiantes con Actividad Registrada', 
            $dashboardData['students_with_activity'],
            $total > 0 ? round(($dashboardData['students_with_activity'] / $total) * 100, 1) . '%' : '0%',
            ''
        ], $delimiter);
        
        fputcsv($file, [
            'Estudiantes con Diagnósticos', 
            $dashboardData['students_with_diagnostics'],
            $total > 0 ? round(($dashboardData['students_with_diagnostics'] / $total) * 100, 1) . '%' : '0%',
            ''
        ], $delimiter);
        
        fputcsv($file, [
            'Estudiantes sin Diagnósticos', 
            $dashboardData['students_without_diagnostics'],
            $total > 0 ? round(($dashboardData['students_without_diagnostics'] / $total) * 100, 1) . '%' : '0%',
            ''
        ], $delimiter);
        
        fputcsv($file, [
            'Con Buen Progreso (≥70%)', 
            $dashboardData['good_progress'],
            $total > 0 ? round(($dashboardData['good_progress'] / $total) * 100, 1) . '%' : '0%',
            ''
        ], $delimiter);
        
        fputcsv($file, [
            'Con Progreso Medio (50-69%)', 
            $dashboardData['medium_progress'],
            $total > 0 ? round(($dashboardData['medium_progress'] / $total) * 100, 1) . '%' : '0%',
            ''
        ], $delimiter);
        
        fputcsv($file, [
            'En Riesgo (<50%)', 
            $dashboardData['at_risk'],
            $total > 0 ? round(($dashboardData['at_risk'] / $total) * 100, 1) . '%' : '0%',
            ''
        ], $delimiter);
        
        fputcsv($file, ['', '', '', ''], $delimiter);
        
        fputcsv($file, ['DIAGNÓSTICOS', '', '', ''], $delimiter);
        fputcsv($file, ['Diagnósticos Realizados Hoy', $dashboardData['diagnostics_today'], '', ''], $delimiter);
        fputcsv($file, ['Total de Diagnósticos en el Sistema', $dashboardData['diagnostics_total'], '', ''], $delimiter);
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}

/**
 * Generar reporte de uso de plataforma en CSV
 */
private function generatePlatformUsageReport($startDate, $endDate)
{
    // Obtener estudiantes con última actividad
    $studentsData = $this->usuarioModel->obtenerEstudiantes();
    
    $students = collect($studentsData)->map(function($student) {
        $student = (object) $student;
        
        // Total de actividades
        $student->total_diagnostics = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
        $student->completed_activities = $this->usuarioModel->contarActividadesCompletadas($student->id);
        
        // Última actividad
        if (!isset($student->last_activity)) {
            $student->last_activity = null;
        }
        
        return $student;
    });
    
    $filename = 'reporte-uso-plataforma-' . date('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($students, $startDate, $endDate) {
        $file = fopen('php://output', 'w');
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($file, ['REPORTE DE USO DE PLATAFORMA']);
        fputcsv($file, ['Fecha de generación:', date('d/m/Y H:i:s')]);
        fputcsv($file, ['Total de usuarios:', $students->count()]);
        if ($startDate && $endDate) {
            fputcsv($file, ['Período:', $startDate . ' - ' . $endDate]);
        }
        fputcsv($file, []);
        
        // Encabezados
        fputcsv($file, [
            'ID',
            'Nombre',
            'Email',
            'Total Diagnósticos',
            'Total Actividades',
            'Última Actividad',
            'Estado'
        ]);
        
        // Datos
        foreach ($students as $student) {
            $lastActivity = $student->last_activity 
                ? date('d/m/Y H:i', strtotime($student->last_activity))
                : 'Sin actividad';
            
            // Determinar estado
            $estado = 'Inactivo';
            if ($student->last_activity) {
                $daysSinceActivity = (time() - strtotime($student->last_activity)) / (60 * 60 * 24);
                if ($daysSinceActivity <= 7) {
                    $estado = 'Muy Activo';
                } elseif ($daysSinceActivity <= 30) {
                    $estado = 'Activo';
                }
            }
            
            fputcsv($file, [
                $student->id,
                $student->name,
                $student->email,
                $student->total_diagnostics,
                $student->completed_activities,
                $lastActivity,
                $estado
            ]);
        }
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}

/**
 * Generar reporte de rendimiento por carrera en CSV
 */
private function generateCareerPerformanceReport($startDate, $endDate)
{
    // Obtener estudiantes agrupados por carrera
    $studentsData = $this->usuarioModel->obtenerEstudiantes();
    
    $careerPerformance = collect($studentsData)->groupBy('career')->map(function($students, $career) {
        $totalStudents = $students->count();
        $totalScore = 0;
        $studentsWithScores = 0;
        
        foreach ($students as $student) {
            $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student['id']);
            if ($performance['percentage'] > 0) {
                $totalScore += $performance['percentage'];
                $studentsWithScores++;
            }
        }
        
        $avgScore = $studentsWithScores > 0 ? $totalScore / $studentsWithScores : 0;
        
        return [
            'career' => $career ?: 'Sin Carrera',
            'total_students' => $totalStudents,
            'students_with_activity' => $studentsWithScores,
            'avg_score' => round($avgScore, 2)
        ];
    });
    
    $filename = 'reporte-rendimiento-carrera-' . date('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($careerPerformance, $startDate, $endDate) {
        $file = fopen('php://output', 'w');
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($file, ['REPORTE DE RENDIMIENTO POR CARRERA']);
        fputcsv($file, ['Fecha de generación:', date('d/m/Y H:i:s')]);
        if ($startDate && $endDate) {
            fputcsv($file, ['Período:', $startDate . ' - ' . $endDate]);
        }
        fputcsv($file, []);
        
        // Encabezados
        fputcsv($file, [
            'Carrera',
            'Total Estudiantes',
            'Estudiantes con Actividad',
            'Promedio General (%)',
            'Estado'
        ]);
        
        // Datos
        foreach ($careerPerformance as $career) {
            $avg = $career['avg_score'];
            $estado = $avg >= 70 ? 'Excelente' : ($avg >= 50 ? 'Regular' : 'Crítico');
            
            fputcsv($file, [
                $career['career'],
                $career['total_students'],
                $career['students_with_activity'],
                $avg,
                $estado
            ]);
        }
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}

/**
 * Generar reporte completo de diagnósticos
 */
private function generateDiagnosticsFullReport($startDate, $endDate)
{
    return $this->generateStudentsReport($startDate, $endDate);
}
    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Calcular nivel de riesgo
     */
    private function calculateRiskLevel($student)
    {
        $riskScore = 0;
        
        $lastActivity = $student->last_activity ?? null;
        
        if (!$lastActivity) {
            $riskScore += 2;
        } elseif (strtotime($lastActivity) < strtotime('-14 days')) {
            $riskScore += 2;
        } elseif (strtotime($lastActivity) < strtotime('-7 days')) {
            $riskScore += 1;
        }
        
        $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
        $avgScore = $performance['percentage'];
        
        if ($avgScore > 0 && $avgScore < 50) {
            $riskScore += 2;
        } elseif ($avgScore > 0 && $avgScore < 70) {
            $riskScore += 1;
        }
        
        $totalActivities = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
        $createdAt = strtotime($student->created_at);
        $fourteenDaysAgo = strtotime('-14 days');
        
        if ($totalActivities < 3 && $createdAt < $fourteenDaysAgo) {
            $riskScore += 1;
        }
        
        return min($riskScore, 3);
    }

    /**
     * Identificar factores de riesgo
     */
    private function identifyRiskFactors($student)
    {
        $factors = [];
        
        $lastActivity = $student->last_activity ?? null;
        
        if (!$lastActivity) {
            $factors[] = 'Nunca ha ingresado a la plataforma';
        } elseif (strtotime($lastActivity) < strtotime('-14 days')) {
            $factors[] = 'Inactividad prolongada (más de 14 días)';
        } elseif (strtotime($lastActivity) < strtotime('-7 days')) {
            $factors[] = 'Inactividad moderada (más de 7 días)';
        }
        
        $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($student->id);
        $avgScore = $performance['percentage'];
        
        if ($avgScore > 0 && $avgScore < 50) {
            $factors[] = 'Rendimiento muy bajo (promedio < 50%)';
        } elseif ($avgScore > 0 && $avgScore < 70) {
            $factors[] = 'Rendimiento bajo (promedio < 70%)';
        }
        
        $totalActivities = $this->diagnosticoModel->contarDiagnosticosCompletados($student->id);
        $createdAt = strtotime($student->created_at);
        $sevenDaysAgo = strtotime('-7 days');
        $fourteenDaysAgo = strtotime('-14 days');
        
        if ($totalActivities === 0 && $createdAt < $sevenDaysAgo) {
            $factors[] = 'Sin actividad registrada';
        } elseif ($totalActivities < 3 && $createdAt < $fourteenDaysAgo) {
            $factors[] = 'Baja participación (menos de 3 actividades)';
        }
        
        if (empty($factors)) {
            $factors[] = 'Requiere seguimiento preventivo';
        }
        
        return $factors;
    }

    /**
     * Obtener rendimiento por mes
     * ✅ ARREGLADO: Ya no recibe PDO como parámetro
     */
    private function getPerformanceByMonth()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    MONTH(dr.created_at) as month,
                    YEAR(dr.created_at) as year,
                    COUNT(*) as total_attempts,
                    SUM(CASE WHEN dr.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
                    ROUND((SUM(CASE WHEN dr.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as avg_score
                FROM diagnostic_responses dr
                WHERE dr.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY year, month
                ORDER BY year DESC, month DESC
            ");
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en getPerformanceByMonth: " . $e->getMessage());
            return [];
        }
    }

/**
 * Generar informe de grupo
 */
public function groupReport(Request $request)
{
    // ... (Filtros y manejo inicial de errores) ...
    $carrera = $request->input('career');
    $semestre = $request->input('semester');
    $formato = $request->input('format', 'view');
    
    $carreras = $this->usuarioModel->obtenerCarreras();
    $semestres = $this->usuarioModel->obtenerSemestres();

    if (!$carrera || !$semestre) {
        if ($formato !== 'view') {
            return redirect()->route('admin.reports.group')->with('error', 'Debe seleccionar carrera y semestre para exportar');
        }
        return view('admin.reports.group', compact('carreras', 'semestres'));
    }

    $studentsData = $this->usuarioModel->obtenerEstudiantesPorGrupo($carrera, $semestre);
    
    if (empty($studentsData)) {
        $errorMessage = 'No se encontraron estudiantes para este grupo';
        if ($formato !== 'view') {
            return redirect()->route('admin.reports.group')->with('error', $errorMessage);
        }
        return view('admin.reports.group', compact('carreras', 'semestres'))->with('error', $errorMessage);
    }
    
    $groupStats = [
        'total_estudiantes' => count($studentsData),
        'promedio_grupo' => 0,
        'aprobados' => 0,
        'reprobados' => 0,
        'en_riesgo' => 0,
        'excelente' => 0,
        'activos_ultimo_mes' => 0,
        'inactivos' => 0,
        'areas_debiles' => [],
        'mejores_estudiantes' => [],
        'estudiantes_riesgo' => []
    ];
    
    $scores = [];
    $areaScores = []; // Almacena [Área => [Puntaje1, Puntaje2, ...]]
    $studentsWithScores = [];
    
    // Analizar cada estudiante
    foreach ($studentsData as $student) {
        $studentObj = (object) $student;
        
        // OBTENER RENDIMIENTO
        $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($studentObj->id);
        $score = (float) ($performance['percentage'] ?? 0); 
        
        // Almacenar el score y los datos del estudiante
        $studentsWithScores[] = [
            'id' => $studentObj->id,
            'nombre' => $studentObj->name,
            'email' => $studentObj->email ?? '',
            'score' => round($score, 2)
        ];

        // 1. Acumular puntuación para el promedio y la distribución
        if ($score > 0) {
            $scores[] = $score;
        }
        
        // 2. Clasificar estudiantes (Distribución de Rendimiento)
        if ($score >= 90) {
            $groupStats['excelente']++;
        } elseif ($score >= 60) {
            $groupStats['aprobados']++;
        } elseif ($score >= 50) {
            $groupStats['reprobados']++;
        } else {
            $groupStats['en_riesgo']++;
            $groupStats['estudiantes_riesgo'][] = [
                'id' => $studentObj->id,
                'nombre' => $studentObj->name,
                'score' => round($score, 2)
            ];
        }
        
        // 3. Verificar actividad (Activos 30 días)
        $lastLogin = $studentObj->last_login_at ?? $studentObj->updated_at ?? null; 
        
        if ($lastLogin && strtotime($lastLogin) >= strtotime('-30 days')) {
            $groupStats['activos_ultimo_mes']++;
        } else {
            $groupStats['inactivos']++;
        }
        
        // 4. Obtener áreas débiles por materia
        $areasMejora = $this->diagnosticoModel->obtenerAreasMejora($studentObj->id);
        foreach ($areasMejora as $area) {
            $subject = $area['subject_area'];
            if (!isset($areaScores[$subject])) {
                $areaScores[$subject] = [];
            }
            // ACUMULAR SCORE DEL ÁREA POR ESTUDIANTE
            $areaScores[$subject][] = (float) ($area['porcentaje'] ?? 0); 
        }
    }
    
    // ✅ 1. CÁLCULO FINAL DEL PROMEDIO DEL GRUPO
    // Si $scores está vacío (todos los estudiantes tienen 0%), el promedio será 0.
    $groupStats['promedio_grupo'] = !empty($scores) ? round(array_sum($scores) / count($scores), 2) : 0;
    
    // ✅ 2. IDENTIFICAR ÁREAS DÉBILES DEL GRUPO (Promedio < 60%)
    foreach ($areaScores as $subject => $scores) {
        if (!empty($scores)) {
             $avgScore = array_sum($scores) / count($scores);
             // Solo se considera área débil si el promedio GRUPAL es menor a 60%
             if ($avgScore < 60) { 
                 $groupStats['areas_debiles'][] = [
                     'materia' => $subject,
                     'promedio' => round($avgScore, 2),
                     'estudiantes_afectados' => count($scores)
                 ];
             }
        }
    }
    
    // Ordenar áreas débiles por promedio
    usort($groupStats['areas_debiles'], function($a, $b) {
        return $a['promedio'] <=> $b['promedio'];
    });
    
    // ✅ 3. OBTENER MEJORES ESTUDIANTES (Top 5)
    $groupStats['mejores_estudiantes'] = collect($studentsWithScores)
        ->filter(function($s) {
            return $s['score'] > 0; // Solo estudiantes con puntuación real
        })
        ->sortByDesc('score')
        ->take(5)
        ->values()
        ->toArray();
    
    // ... (Manejo de formatos CSV/PDF y retorno de la vista) ...
    if ($formato === 'csv') {
        return $this->generateGroupReportCSV($groupStats, $carrera, $semestre, $studentsData);
    }
    
    if ($formato === 'pdf') {
        return $this->generateGroupReportPDF($groupStats, $carrera, $semestre, $studentsData);
    }
    
    $carreras = $this->usuarioModel->obtenerCarreras();
    $semestres = $this->usuarioModel->obtenerSemestres();
    
    return view('admin.reports.group', compact(
        'groupStats',
        'carrera',
        'semestre',
        'carreras',
        'semestres',
        'studentsData'
    ));
}
/**
 * Generar reporte de grupo en CSV
 */
private function generateGroupReportCSV($groupStats, $carrera, $semestre, $studentsData)
{
    $filename = 'informe-grupo-' . ($carrera ?? 'todas') . '-' . ($semestre ?? 'todos') . '-' . date('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($groupStats, $carrera, $semestre, $studentsData) {
        $file = fopen('php://output', 'w');
        
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        $delimiter = ';';
        
        // Título
        fputcsv($file, ['INFORME DE GRUPO', '', '', ''], $delimiter);
        fputcsv($file, ['Carrera', $carrera ?? 'Todas', '', ''], $delimiter);
        fputcsv($file, ['Semestre', $semestre ?? 'Todos', '', ''], $delimiter);
        fputcsv($file, ['Fecha', date('d/m/Y H:i:s'), '', ''], $delimiter);
        fputcsv($file, ['', '', '', ''], $delimiter);
        
        // Estadísticas generales
        fputcsv($file, ['ESTADÍSTICAS GENERALES', '', '', ''], $delimiter);
        fputcsv($file, ['Métrica', 'Valor', 'Porcentaje', ''], $delimiter);
        
        $total = $groupStats['total_estudiantes'];
        fputcsv($file, ['Total Estudiantes', $total, '100%', ''], $delimiter);
        fputcsv($file, ['Promedio del Grupo', $groupStats['promedio_grupo'] . '%', '', ''], $delimiter);
        fputcsv($file, ['Excelente (≥90%)', $groupStats['excelente'], round(($groupStats['excelente']/$total)*100, 1).'%', ''], $delimiter);
        fputcsv($file, ['Aprobados (60-89%)', $groupStats['aprobados'], round(($groupStats['aprobados']/$total)*100, 1).'%', ''], $delimiter);
        fputcsv($file, ['Reprobados (50-59%)', $groupStats['reprobados'], round(($groupStats['reprobados']/$total)*100, 1).'%', ''], $delimiter);
        fputcsv($file, ['En Riesgo (<50%)', $groupStats['en_riesgo'], round(($groupStats['en_riesgo']/$total)*100, 1).'%', ''], $delimiter);
        fputcsv($file, ['Activos (último mes)', $groupStats['activos_ultimo_mes'], round(($groupStats['activos_ultimo_mes']/$total)*100, 1).'%', ''], $delimiter);
        fputcsv($file, ['Inactivos', $groupStats['inactivos'], round(($groupStats['inactivos']/$total)*100, 1).'%', ''], $delimiter);
        fputcsv($file, ['', '', '', ''], $delimiter);
        
        // Áreas débiles
        if (!empty($groupStats['areas_debiles'])) {
            fputcsv($file, ['ÁREAS DÉBILES DEL GRUPO', '', '', ''], $delimiter);
            fputcsv($file, ['Materia', 'Promedio', 'Estudiantes Afectados', ''], $delimiter);
            
            foreach ($groupStats['areas_debiles'] as $area) {
                fputcsv($file, [
                    $area['materia'],
                    $area['promedio'] . '%',
                    $area['estudiantes_afectados'],
                    ''
                ], $delimiter);
            }
            fputcsv($file, ['', '', '', ''], $delimiter);
        }
        
        // Mejores estudiantes
        if (!empty($groupStats['mejores_estudiantes'])) {
            fputcsv($file, ['MEJORES ESTUDIANTES', '', '', ''], $delimiter);
            fputcsv($file, ['Nombre', 'Email', 'Promedio', ''], $delimiter);
            
            foreach ($groupStats['mejores_estudiantes'] as $estudiante) {
                fputcsv($file, [
                    $estudiante['nombre'],
                    $estudiante['email'],
                    round($estudiante['score'], 2) . '%',
                    ''
                ], $delimiter);
            }
            fputcsv($file, ['', '', '', ''], $delimiter);
        }
        
        // Estudiantes en riesgo
        if (!empty($groupStats['estudiantes_riesgo'])) {
            fputcsv($file, ['ESTUDIANTES EN RIESGO', '', '', ''], $delimiter);
            fputcsv($file, ['Nombre', 'Promedio', '', ''], $delimiter);
            
            foreach ($groupStats['estudiantes_riesgo'] as $estudiante) {
                fputcsv($file, [
                    $estudiante['nombre'],
                    $estudiante['score'] . '%',
                    '',
                    ''
                ], $delimiter);
            }
        }
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}
    /**
     * Métricas generales del grupo
     */
    private function getGeneralStats($career = null, $semester = null)
    {
        $query = DB::table('users')
            ->leftJoin('student_progress', 'users.id', '=', 'student_progress.user_id')
            ->leftJoin('ml_analysis', 'users.id', '=', 'ml_analysis.user_id')
            ->where('users.role_id', 3)
            ->where('users.active', 1);
        
        if ($career) {
            $query->where('users.career', $career);
        }
        
        if ($semester) {
            $query->where('users.semester', $semester);
        }
        
        $stats = $query->select(
            DB::raw('COUNT(DISTINCT users.id) as total_students'),
            DB::raw('AVG(student_progress.progress_percentage) as avg_progress'),
            DB::raw('AVG(student_progress.average_score) as avg_score'),
            DB::raw('SUM(CASE WHEN ml_analysis.nivel_riesgo IN ("medio", "alto") THEN 1 ELSE 0 END) as risk_students')
        )->first();
        
        // Tasa de completitud
        $completionRate = DB::table('users')
            ->join('learning_path_content', function($join) {
                $join->on('users.id', '=', DB::raw('(SELECT user_id FROM learning_paths WHERE learning_paths.id = learning_path_content.learning_path_id)'));
            })
            ->where('users.role_id', 3)
            ->when($career, fn($q) => $q->where('users.career', $career))
            ->when($semester, fn($q) => $q->where('users.semester', $semester))
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed')
            )
            ->first();
        
        $completionPercentage = $completionRate && $completionRate->total > 0
            ? round(($completionRate->completed / $completionRate->total) * 100, 1)
            : 0;
        
        return [
            'total_students' => $stats->total_students ?? 0,
            'avg_progress' => round($stats->avg_progress ?? 0, 1),
            'avg_score' => round($stats->avg_score ?? 0, 1),
            'risk_students' => $stats->risk_students ?? 0,
            'completion_rate' => $completionPercentage
        ];
    }
    
    /**
     * Estadísticas por carrera
     */
    private function getStatsByCareer($filterCareer = null, $semester = null)
    {
        $query = DB::table('users')
            ->leftJoin('student_progress', 'users.id', '=', 'student_progress.user_id')
            ->leftJoin('ml_analysis', 'users.id', '=', 'ml_analysis.user_id')
            ->where('users.role_id', 3)
            ->where('users.active', 1)
            ->whereNotNull('users.career');
        
        if ($filterCareer) {
            $query->where('users.career', $filterCareer);
        }
        
        if ($semester) {
            $query->where('users.semester', $semester);
        }
        
        return $query->select(
            'users.career',
            DB::raw('COUNT(DISTINCT users.id) as total_students'),
            DB::raw('AVG(student_progress.progress_percentage) as avg_progress'),
            DB::raw('AVG(student_progress.average_score) as avg_score'),
            DB::raw('SUM(CASE WHEN ml_analysis.nivel_riesgo IN ("medio", "alto") THEN 1 ELSE 0 END) as risk_students')
        )
        ->groupBy('users.career')
        ->orderBy('total_students', 'desc')
        ->get();
    }
    
    /**
     * Estadísticas por semestre
     */
    private function getStatsBySemester($career = null, $filterSemester = null)
    {
        $query = DB::table('users')
            ->leftJoin('student_progress', 'users.id', '=', 'student_progress.user_id')
            ->leftJoin('ml_analysis', 'users.id', '=', 'ml_analysis.user_id')
            ->where('users.role_id', 3)
            ->where('users.active', 1)
            ->whereNotNull('users.semester');
        
        if ($career) {
            $query->where('users.career', $career);
        }
        
        if ($filterSemester) {
            $query->where('users.semester', $filterSemester);
        }
        
        return $query->select(
            'users.semester',
            DB::raw('COUNT(DISTINCT users.id) as total_students'),
            DB::raw('AVG(student_progress.progress_percentage) as avg_progress'),
            DB::raw('AVG(student_progress.average_score) as avg_score'),
            DB::raw('SUM(CASE WHEN ml_analysis.nivel_riesgo IN ("medio", "alto") THEN 1 ELSE 0 END) as risk_students')
        )
        ->groupBy('users.semester')
        ->orderBy('users.semester')
        ->get();
    }
    
    /**
     * Estudiantes en riesgo por grupo
     */
    private function getRiskByGroup($career = null, $semester = null)
    {
        $query = DB::table('users')
            ->join('ml_analysis', 'users.id', '=', 'ml_analysis.user_id')
            ->where('users.role_id', 3)
            ->whereIn('ml_analysis.nivel_riesgo', ['medio', 'alto']);
        
        if ($career) {
            $query->where('users.career', $career);
        }
        
        if ($semester) {
            $query->where('users.semester', $semester);
        }
        
        return $query->select(
            'users.career',
            'ml_analysis.nivel_riesgo',
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('users.career', 'ml_analysis.nivel_riesgo')
        ->get();
    }
    
    /**
     * Top materias con mejor/peor rendimiento
     */
    private function getTopSubjects($career = null, $semester = null)
    {
        $query = DB::table('student_progress')
            ->join('users', 'student_progress.user_id', '=', 'users.id')
            ->where('users.role_id', 3);
        
        if ($career) {
            $query->where('users.career', $career);
        }
        
        if ($semester) {
            $query->where('users.semester', $semester);
        }
        
        return $query->select(
            'student_progress.subject_area',
            DB::raw('COUNT(DISTINCT student_progress.user_id) as students'),
            DB::raw('AVG(student_progress.average_score) as avg_score'),
            DB::raw('AVG(student_progress.progress_percentage) as avg_progress')
        )
        ->groupBy('student_progress.subject_area')
        ->orderBy('avg_score', 'desc')
        ->limit(10)
        ->get();
    }
    
    /**
     * Top estudiantes destacados
     */
    private function getTopStudents($career = null, $semester = null, $limit = 5)
    {
        $query = DB::table('users')
            ->leftJoin('student_progress', 'users.id', '=', 'student_progress.user_id')
            ->where('users.role_id', 3)
            ->where('users.active', 1);
        
        if ($career) {
            $query->where('users.career', $career);
        }
        
        if ($semester) {
            $query->where('users.semester', $semester);
        }
        
        return $query->select(
            'users.id',
            'users.name',
            'users.student_code',
            'users.career',
            'users.semester',
            DB::raw('AVG(student_progress.average_score) as avg_score'),
            DB::raw('AVG(student_progress.progress_percentage) as avg_progress')
        )
        ->groupBy('users.id', 'users.name', 'users.student_code', 'users.career', 'users.semester')
        ->orderBy('avg_score', 'desc')
        ->limit($limit)
        ->get();
    }
    
    /**
     * Estudiantes en riesgo
     */
    private function getRiskStudents($career = null, $semester = null, $limit = 10)
    {
        $query = DB::table('users')
            ->join('ml_analysis', 'users.id', '=', 'ml_analysis.user_id')
            ->leftJoin('student_progress', 'users.id', '=', 'student_progress.user_id')
            ->where('users.role_id', 3)
            ->whereIn('ml_analysis.nivel_riesgo', ['medio', 'alto']);
        
        if ($career) {
            $query->where('users.career', $career);
        }
        
        if ($semester) {
            $query->where('users.semester', $semester);
        }
        
        return $query->select(
            'users.id',
            'users.name',
            'users.student_code',
            'users.career',
            'users.semester',
            'ml_analysis.nivel_riesgo',
            DB::raw('AVG(student_progress.average_score) as avg_score'),
            DB::raw('AVG(student_progress.progress_percentage) as avg_progress')
        )
        ->groupBy('users.id', 'users.name', 'users.student_code', 'users.career', 'users.semester', 'ml_analysis.nivel_riesgo')
        ->orderBy('ml_analysis.nivel_riesgo', 'desc')
        ->orderBy('avg_score', 'asc')
        ->limit($limit)
        ->get();
    }
    /**
     * Generar reporte en PDF
     */
    private function generateGroupReportPDF($groupStats, $carrera, $semestre, $studentsData)
    {
        $pdf = Pdf::loadView('admin.reports.group-pdf', compact(
            'groupStats',
            'carrera',
            'semestre',
            'studentsData'
        ))->setPaper('a4', 'portrait');
        
        $filename = 'informe_grupo_' . ($carrera ?? 'todas') . '_' . ($semestre ?? 'todos') . '_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }
    /**
     * Exportar informe a PDF
     */
    public function exportGroupReportPDF(Request $request)
    {
        $career = $request->input('career');
        $semester = $request->input('semester');
        
        $generalStats = $this->getGeneralStats($career, $semester);
        $byCareer = $this->getStatsByCareer($career, $semester);
        $bySemester = $this->getStatsBySemester($career, $semester);
        
        $pdf = Pdf::loadView('admin.reports.group-pdf', compact(
            'generalStats',
            'byCareer',
            'bySemester',
            'career',
            'semester'
        ));
        
        $filename = 'informe_grupo_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Exportar informe a Excel/CSV
     */
    public function exportGroupReportExcel(Request $request)
    {
        $career = $request->input('career');
        $semester = $request->input('semester');
        
        $byCareer = $this->getStatsByCareer($career, $semester);
        
        $filename = 'informe_grupo_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($byCareer) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, [
                'Carrera',
                'Total Estudiantes',
                'Progreso Promedio (%)',
                'Nota Promedio',
                'Estudiantes en Riesgo'
            ]);
            
            // Datos
            foreach ($byCareer as $data) {
                fputcsv($file, [
                    $data->career,
                    $data->total_students,
                    round($data->avg_progress, 1),
                    round($data->avg_score, 1),
                    $data->risk_students
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }



    /**
 * INFORME AUTOMÁTICO DE RIESGO SEMANAL
 * Genera reportes de estudiantes en condiciones de riesgo
 */
public function weeklyRiskReport(Request $request)
{
    $format = $request->input('format', 'view');
    $date = $request->input('date', now()->format('Y-m-d'));
    
    // ✅ CRITERIOS DE RIESGO (Según historia de usuario)
    $riskCriteria = [
        'low_score_threshold' => 40,      // Puntaje menor al 40%
        'inactivity_days' => 7,           // Más de 7 días sin actividad
        'min_activities' => 2             // Menos de 2 actividades
    ];
    
    // Obtener TODOS los estudiantes
    $studentsData = $this->usuarioModel->obtenerEstudiantes();
    
    $riskStudents = collect($studentsData)->map(function($student) use ($riskCriteria) {
        $studentObj = (object) $student;
        
        // 1. OBTENER MÉTRICAS DEL ESTUDIANTE
        $performance = $this->diagnosticoModel->obtenerRendimientoEstudiante($studentObj->id);
        $avgScore = (float) ($performance['percentage'] ?? 0);
        
        $totalActivities = $this->diagnosticoModel->contarDiagnosticosCompletados($studentObj->id);
        
        $lastActivity = $studentObj->last_activity ?? $studentObj->last_login_at ?? null;
        
        // 2. EVALUAR CONDICIONES DE RIESGO
        $riskFactors = [];
        $riskLevel = 0; // 0: Sin riesgo, 1: Bajo, 2: Medio, 3: Alto, 4: Crítico
        
        // ⚠️ Factor 1: Puntaje bajo (<40%)
        if ($avgScore > 0 && $avgScore < $riskCriteria['low_score_threshold']) {
            $riskFactors[] = [
                'factor' => 'Rendimiento muy bajo',
                'detail' => "Promedio: {$avgScore}% (< {$riskCriteria['low_score_threshold']}%)",
                'severity' => 'high'
            ];
            $riskLevel += 2;
        } elseif ($avgScore >= $riskCriteria['low_score_threshold'] && $avgScore < 60) {
            $riskFactors[] = [
                'factor' => 'Rendimiento bajo',
                'detail' => "Promedio: {$avgScore}%",
                'severity' => 'medium'
            ];
            $riskLevel += 1;
        }
        
        // ⚠️ Factor 2: Inactividad prolongada (>7 días)
        $daysSinceActivity = null;
        if ($lastActivity) {
            $daysSinceActivity = now()->diffInDays($lastActivity);
            
            if ($daysSinceActivity > $riskCriteria['inactivity_days']) {
                $riskFactors[] = [
                    'factor' => 'Inactividad prolongada',
                    'detail' => "{$daysSinceActivity} días sin actividad",
                    'severity' => 'high'
                ];
                $riskLevel += 2;
            } elseif ($daysSinceActivity > 3) {
                $riskFactors[] = [
                    'factor' => 'Actividad baja',
                    'detail' => "{$daysSinceActivity} días desde último acceso",
                    'severity' => 'medium'
                ];
                $riskLevel += 1;
            }
        } else {
            $riskFactors[] = [
                'factor' => 'Sin actividad registrada',
                'detail' => 'El estudiante nunca ha ingresado',
                'severity' => 'critical'
            ];
            $riskLevel += 3;
        }
        
        // ⚠️ Factor 3: Pocas actividades completadas
        if ($totalActivities < $riskCriteria['min_activities']) {
            $riskFactors[] = [
                'factor' => 'Baja participación',
                'detail' => "Solo {$totalActivities} diagnósticos completados",
                'severity' => 'medium'
            ];
            $riskLevel += 1;
        }
        
        // 3. CLASIFICAR NIVEL DE RIESGO FINAL
        $riskCategory = 'sin_riesgo';
        if ($riskLevel >= 5) {
            $riskCategory = 'critico';
        } elseif ($riskLevel >= 3) {
            $riskCategory = 'alto';
        } elseif ($riskLevel >= 1) {
            $riskCategory = 'moderado';
        }
        
        // 4. SOLO RETORNAR SI HAY RIESGO
        if (count($riskFactors) > 0) {
            return [
                'id' => $studentObj->id,
                'nombre' => $studentObj->name,
                'email' => $studentObj->email,
                'carrera' => $studentObj->career ?? 'Sin carrera',
                'semestre' => $studentObj->semestre ?? $studentObj->semester ?? 'N/A',
                'avg_score' => round($avgScore, 2),
                'total_activities' => $totalActivities,
                'last_activity' => $lastActivity,
                'days_inactive' => $daysSinceActivity,
                'risk_level' => $riskLevel,
                'risk_category' => $riskCategory,
                'risk_factors' => $riskFactors,
                'created_at' => $studentObj->created_at
            ];
        }
        
        return null;
    })
    ->filter() // Eliminar nulls (estudiantes sin riesgo)
    ->sortByDesc('risk_level') // Ordenar por nivel de riesgo
    ->values();
    
    // ESTADÍSTICAS DEL REPORTE
    $reportStats = [
        'total_risk_students' => $riskStudents->count(),
        'total_students' => count($studentsData),
        'percentage_at_risk' => count($studentsData) > 0 
            ? round(($riskStudents->count() / count($studentsData)) * 100, 2) 
            : 0,
        'critical' => $riskStudents->where('risk_category', 'critico')->count(),
        'high' => $riskStudents->where('risk_category', 'alto')->count(),
        'moderate' => $riskStudents->where('risk_category', 'moderado')->count(),
        'generation_date' => now()->format('d/m/Y H:i:s'),
        'report_week' => now()->weekOfYear,
        'report_year' => now()->year
    ];
    
    // RECOMENDACIONES AUTOMÁTICAS
    $recommendations = $this->generateRiskRecommendations($riskStudents);
    
    // 📧 MANEJO DE FORMATOS
    if ($format === 'csv') {
        return $this->generateRiskReportCSV($riskStudents, $reportStats, $riskCriteria);
    }
    
    if ($format === 'pdf') {
        return $this->generateRiskReportPDF($riskStudents, $reportStats, $riskCriteria, $recommendations);
    }
    
    // Vista HTML
    return view('admin.reports.risk-report', compact(
        'riskStudents',
        'reportStats',
        'riskCriteria',
        'recommendations',
        'date'
    ));
}

/**
 * Generar recomendaciones automáticas basadas en patrones
 */
private function generateRiskRecommendations($riskStudents)
{
    $recommendations = [];
    
    $criticalStudents = $riskStudents->where('risk_category', 'critico');
    if ($criticalStudents->count() > 0) {
        $recommendations[] = [
            'priority' => 'urgent',
            'title' => 'Contacto Inmediato Requerido',
            'description' => "Se detectaron {$criticalStudents->count()} estudiante(s) en riesgo crítico. Se recomienda contacto telefónico o email urgente en las próximas 24-48 horas.",
            'students' => $criticalStudents->pluck('id')->toArray()
        ];
    }
    
    $inactiveStudents = $riskStudents->filter(function($s) {
        return $s['days_inactive'] > 7;
    });
    
    if ($inactiveStudents->count() > 0) {
        $recommendations[] = [
            'priority' => 'high',
            'title' => 'Campaña de Reactivación',
            'description' => "Implementar campaña de email/SMS para reactivar a {$inactiveStudents->count()} estudiante(s) con más de 7 días de inactividad.",
            'students' => $inactiveStudents->pluck('id')->toArray()
        ];
    }
    
    $lowScoreStudents = $riskStudents->filter(function($s) {
        return $s['avg_score'] < 40 && $s['avg_score'] > 0;
    });
    
    if ($lowScoreStudents->count() > 0) {
        $recommendations[] = [
            'priority' => 'medium',
            'title' => 'Soporte Académico',
            'description' => "Asignar tutor o recursos adicionales a {$lowScoreStudents->count()} estudiante(s) con rendimiento menor al 40%.",
            'students' => $lowScoreStudents->pluck('id')->toArray()
        ];
    }
    
    return $recommendations;
}

/**
 * Generar informe de riesgo en CSV
 */
private function generateRiskReportCSV($riskStudents, $reportStats, $riskCriteria)
{
    $filename = 'informe-riesgo-semana-' . $reportStats['report_week'] . '-' . date('Y-m-d_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];
    
    $callback = function() use ($riskStudents, $reportStats, $riskCriteria) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        $delimiter = ';';
        
        // ENCABEZADO DEL REPORTE
        fputcsv($file, ['INFORME SEMANAL DE ESTUDIANTES EN RIESGO', '', '', '', ''], $delimiter);
        fputcsv($file, ['Fecha de Generación', $reportStats['generation_date'], '', '', ''], $delimiter);
        fputcsv($file, ['Semana', $reportStats['report_week'] . '/' . $reportStats['report_year'], '', '', ''], $delimiter);
        fputcsv($file, ['', '', '', '', ''], $delimiter);
        
        // CRITERIOS DE RIESGO
        fputcsv($file, ['CRITERIOS DE RIESGO APLICADOS', '', '', '', ''], $delimiter);
        fputcsv($file, ['Puntaje mínimo', $riskCriteria['low_score_threshold'] . '%', '', '', ''], $delimiter);
        fputcsv($file, ['Días de inactividad máximos', $riskCriteria['inactivity_days'], '', '', ''], $delimiter);
        fputcsv($file, ['Actividades mínimas', $riskCriteria['min_activities'], '', '', ''], $delimiter);
        fputcsv($file, ['', '', '', '', ''], $delimiter);
        
        // RESUMEN EJECUTIVO
        fputcsv($file, ['RESUMEN EJECUTIVO', '', '', '', ''], $delimiter);
        fputcsv($file, ['Total de estudiantes en el sistema', $reportStats['total_students'], '', '', ''], $delimiter);
        fputcsv($file, ['Estudiantes en riesgo', $reportStats['total_risk_students'], '', '', ''], $delimiter);
        fputcsv($file, ['Porcentaje en riesgo', $reportStats['percentage_at_risk'] . '%', '', '', ''], $delimiter);
        fputcsv($file, ['Nivel Crítico', $reportStats['critical'], '', '', ''], $delimiter);
        fputcsv($file, ['Nivel Alto', $reportStats['high'], '', '', ''], $delimiter);
        fputcsv($file, ['Nivel Moderado', $reportStats['moderate'], '', '', ''], $delimiter);
        fputcsv($file, ['', '', '', '', ''], $delimiter);
        
        // DETALLE DE ESTUDIANTES EN RIESGO
        fputcsv($file, ['DETALLE DE ESTUDIANTES EN RIESGO', '', '', '', ''], $delimiter);
        fputcsv($file, [
            'ID',
            'Nombre',
            'Email',
            'Carrera',
            'Semestre',
            'Promedio (%)',
            'Actividades',
            'Días Inactivo',
            'Nivel de Riesgo',
            'Factores de Riesgo'
        ], $delimiter);
        
        foreach ($riskStudents as $student) {
            $factorsText = collect($student['risk_factors'])
                ->pluck('factor')
                ->implode(', ');
            
            $categoryLabels = [
                'critico' => 'CRÍTICO',
                'alto' => 'ALTO',
                'moderado' => 'MODERADO'
            ];
            
            fputcsv($file, [
                $student['id'],
                $student['nombre'],
                $student['email'],
                $student['carrera'],
                $student['semestre'],
                $student['avg_score'],
                $student['total_activities'],
                $student['days_inactive'] ?? 'N/A',
                $categoryLabels[$student['risk_category']],
                $factorsText
            ], $delimiter);
        }
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}

/**
 * Generar informe de riesgo en PDF
 */
private function generateRiskReportPDF($riskStudents, $reportStats, $riskCriteria, $recommendations)
{
    $pdf = PDF::loadView('admin.reports.risk-report-pdf', compact(
        'riskStudents',
        'reportStats',
        'riskCriteria',
        'recommendations'
    ))->setPaper('a4', 'portrait');
    
    $filename = 'informe-riesgo-semana-' . $reportStats['report_week'] . '-' . date('Y-m-d_His') . '.pdf';
    
    return $pdf->download($filename);
}
}