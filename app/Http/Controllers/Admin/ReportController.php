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
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->usuarioModel = new UsuarioModel();
        $this->diagnosticoModel = new DiagnosticoModel($pdo);
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
        $performanceByMonth = $this->getPerformanceByMonth($this->pdo, 6); // ← Cambia esta línea
        
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
        $students = collect($studentsData)->map(fn($s) => (object) $s);
        
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
     */
    private function getPerformanceByMonth(\PDO $pdo)
    {
        try {
            
            $stmt = $pdo->query("
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
}