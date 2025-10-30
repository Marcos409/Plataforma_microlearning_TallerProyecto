<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataAccessModels\AnalisisModel;
use App\DataAccessModels\UsuarioModel;
use App\Services\MLPredictionService;
use Illuminate\Support\Facades\Log;

class MLAnalysisController extends Controller
{
    protected $mlService;
    protected $analisisModel;
    protected $usuarioModel;

    /**
     * Constructor - Inyección de dependencias
     */
    public function __construct(MLPredictionService $mlService)
    {
        $this->mlService = $mlService;
        $this->analisisModel = new AnalisisModel();
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Analizar un estudiante individual
     * @param int $studentId
     * @return bool
     */
    public function analyzeStudent($studentId)
    {
        // ✅ Obtener estudiante usando PDO
        $studentData = $this->usuarioModel->obtenerUsuario($studentId);
        
        if (!$studentData) {
            Log::error("Estudiante no encontrado: ID {$studentId}");
            return false;
        }
        
        $student = (object) $studentData;
        
        Log::info("=== Analizando estudiante: {$student->name} (ID: {$student->id}) ===");
        
        try {
            // PREDICCIÓN 1: Diagnóstico
            $diagnostico = $this->mlService->predictDiagnostico($student);
            Log::info("Diagnóstico:", $diagnostico ?? ['null']);
            
            // PREDICCIÓN 2: Rutas
            $ruta = $this->mlService->predictRuta($student);
            Log::info("Ruta:", $ruta ?? ['null']);
            
            // PREDICCIÓN 3: Riesgo
            $riesgo = $this->mlService->predictRiesgo($student);
            Log::info("Riesgo:", $riesgo ?? ['null']);
            
            // ✅ Guardar análisis usando PDO
            $analisisId = $this->analisisModel->crearAnalisis([
                'user_id' => $student->id,
                'diagnostico' => $diagnostico['nivel'] ?? null,
                'ruta_aprendizaje' => $ruta['tipo_ruta'] ?? null,
                'nivel_riesgo' => $riesgo['nivel_riesgo'] ?? null,
                'metricas' => [
                    'probabilidad_diagnostico' => $diagnostico['probabilidad'] ?? 0,
                    'progreso_esperado' => $ruta['progreso_esperado'] ?? 'desconocido',
                    'probabilidad_riesgo' => $riesgo['probabilidad_riesgo'] ?? 0,
                    'dificultad_recomendada' => $ruta['dificultad_recomendada'] ?? 'intermedio',
                ],
                'recomendaciones' => [
                    'contenido' => $diagnostico['contenido_recomendado'] ?? [],
                    'temas_problematicos' => $diagnostico['temas_problematicos'] ?? [],
                    'actividades_refuerzo' => $riesgo['actividades_refuerzo'] ?? [],
                    'ruta_pasos' => $ruta['ruta_aprendizaje'] ?? [],
                ],
            ]);
            
            if ($analisisId) {
                Log::info("✓ Estudiante {$student->name} analizado exitosamente (Análisis ID: {$analisisId})");
                return true;
            } else {
                Log::error("✗ Error al guardar análisis de {$student->name}");
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error("✗ Error analizando {$student->name}: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }

    /**
     * Analizar todos los estudiantes
     */
    public function analyzeAll()
    {
        Log::info("=== INICIO ANÁLISIS MASIVO ===");
        
        // ✅ Obtener estudiantes usando PDO
        $students = $this->usuarioModel->buscarUsuarios(null, 3, 1); // role_id=3, active=1
        
        Log::info("Estudiantes encontrados: " . count($students));
        
        if (count($students) == 0) {
            return redirect()->back()->with('error', 'No hay estudiantes registrados');
        }
        
        $analizados = 0;
        $errores = 0;

        foreach ($students as $studentData) {
            $resultado = $this->analyzeStudent($studentData['id']);
            if ($resultado) {
                $analizados++;
            } else {
                $errores++;
            }
        }

        Log::info("=== FIN: {$analizados} exitosos, {$errores} errores ===");

        return redirect()->route('admin.ml.results')->with('success', 
            "Análisis completado: {$analizados} estudiantes analizados" . 
            ($errores > 0 ? ", {$errores} con errores" : "")
        );
    }

    /**
     * Mostrar resultados de análisis ML
     */
    public function showResults()
    {
        $page = request()->get('page', 1);
        $perPage = 20;
        
        // ✅ Obtener análisis usando PDO
        $analysesData = $this->analisisModel->listarAnalisis($page, $perPage);
        $totalAnalyses = $this->analisisModel->contarAnalisis();
        
        // Convertir a collection para paginación
        $analyses = new \Illuminate\Pagination\LengthAwarePaginator(
            $analysesData,
            $totalAnalyses,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        // ✅ Obtener estadísticas usando PDO
        $statistics = $this->analisisModel->obtenerEstadisticas();
        
        return view('admin.ml.results', compact('analyses', 'statistics'));
    }

    /**
     * Mostrar detalles de un análisis específico
     * @param int $id
     */
    public function show($id)
    {
        // ✅ Obtener análisis usando PDO
        $analysisData = $this->analisisModel->obtenerAnalisis($id);
        
        if (!$analysisData) {
            abort(404, 'Análisis no encontrado.');
        }
        
        $analysis = (object) $analysisData;
        
        return view('admin.ml.show', compact('analysis'));
    }

    /**
     * Ver historial de análisis de un estudiante
     * @param int $studentId
     */
    public function studentHistory($studentId)
    {
        // ✅ Obtener estudiante usando PDO
        $studentData = $this->usuarioModel->obtenerUsuario($studentId);
        
        if (!$studentData) {
            abort(404, 'Estudiante no encontrado.');
        }
        
        $student = (object) $studentData;
        
        // ✅ Obtener historial usando PDO
        $history = $this->analisisModel->obtenerHistorialUsuario($studentId);
        
        return view('admin.ml.student-history', compact('student', 'history'));
    }

    /**
     * Re-analizar un estudiante
     * @param int $studentId
     */
    public function reanalyze($studentId)
    {
        $resultado = $this->analyzeStudent($studentId);
        
        if ($resultado) {
            return redirect()->back()
                ->with('success', '✓ Estudiante re-analizado exitosamente.');
        } else {
            return redirect()->back()
                ->with('error', 'Error al re-analizar el estudiante.');
        }
    }

    /**
     * Dashboard de ML con métricas
     */
    public function dashboard()
    {
        // ✅ Obtener estadísticas usando PDO
        $statistics = $this->analisisModel->obtenerEstadisticas();
        
        // ✅ Obtener estudiantes sin análisis reciente
        $studentsSinAnalisis = $this->analisisModel->obtenerEstudiantesSinAnalisis();
        
        // Análisis recientes (últimos 10)
        $recentAnalyses = $this->analisisModel->listarAnalisis(1, 10);
        
        return view('admin.ml.dashboard', compact(
            'statistics',
            'studentsSinAnalisis',
            'recentAnalyses'
        ));
    }

    /**
     * Exportar resultados (CSV o Excel)
     */
    public function export($format = 'csv')
    {
        // ✅ Obtener todos los análisis
        $analyses = $this->analisisModel->listarAnalisis(1, 10000); // Límite alto para exportación
        
        if ($format === 'csv') {
            return $this->exportCsv($analyses);
        } else {
            return redirect()->back()
                ->with('error', 'Formato de exportación no soportado.');
        }
    }

    /**
     * Exportar a CSV
     * @param array $analyses
     */
    private function exportCsv($analyses)
    {
        $filename = 'ml_analysis_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($analyses) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, [
                'ID', 'Estudiante', 'Email', 'Carrera',
                'Diagnóstico', 'Ruta', 'Nivel Riesgo', 'Fecha Análisis'
            ]);
            
            // Datos
            foreach ($analyses as $analysis) {
                fputcsv($file, [
                    $analysis['id'],
                    $analysis['student_name'],
                    $analysis['student_email'],
                    $analysis['career'] ?? 'N/A',
                    $analysis['diagnostico'] ?? 'N/A',
                    $analysis['ruta_aprendizaje'] ?? 'N/A',
                    $analysis['nivel_riesgo'] ?? 'N/A',
                    $analysis['created_at']
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Limpiar análisis antiguos (mantenimiento)
     */
    public function cleanup()
    {
        try {
            // Eliminar análisis de más de 90 días
            $deleted = $this->analisisModel->eliminarAntiguos(90);
            
            Log::info("Limpieza de análisis antiguos: {$deleted} registros eliminados");
            
            return redirect()->back()
                ->with('success', "✓ Limpieza completada: {$deleted} análisis antiguos eliminados.");
                
        } catch (\Exception $e) {
            Log::error("Error en limpieza: " . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al realizar la limpieza.');
        }
    }

    /**
     * API: Obtener análisis más reciente de un estudiante
     * @param int $studentId
     */
    public function apiGetRecent($studentId)
    {
        $analysis = $this->analisisModel->obtenerAnalisisReciente($studentId);
        
        if ($analysis) {
            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró análisis para este estudiante.'
            ], 404);
        }
    }
}