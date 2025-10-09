<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MLAnalysis;
use App\Services\MLPredictionService;
use Illuminate\Support\Facades\Log;

class MLAnalysisController extends Controller
{
    protected $mlService;

    public function __construct(MLPredictionService $mlService)
    {
        $this->mlService = $mlService;
    }

    public function analyzeStudent(User $student)
    {
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
            
            // Guardar en ml_analysis
            MLAnalysis::create([
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
            
            Log::info("✓ Estudiante {$student->name} analizado exitosamente");
            return true;
            
        } catch (\Exception $e) {
            Log::error("✗ Error analizando {$student->name}: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }

    public function analyzeAll()
    {
        Log::info("=== INICIO ANÁLISIS MASIVO ===");
        
        $students = User::students()->get();
        Log::info("Estudiantes encontrados: " . $students->count());
        
        if ($students->count() == 0) {
            return redirect()->back()->with('error', 'No hay estudiantes registrados');
        }
        
        $analizados = 0;
        $errores = 0;

        foreach ($students as $student) {
            $resultado = $this->analyzeStudent($student);
            if ($resultado) {
                $analizados++;
            } else {
                $errores++;
            }
        }

        Log::info("=== FIN: {$analizados} exitosos, {$errores} errores ===");

        return redirect()->route('admin.ml.results')->with('success', 
    "Análisis completado: {$analizados} estudiantes analizados" . 
    ($errores > 0 ? ", {$errores} con errores" : ""));

    }


    public function showResults()
{
    $analyses = MLAnalysis::with('user')
        ->latest()
        ->paginate(20);
    
    $statistics = [
        'total' => MLAnalysis::count(),
        'diagnostico_basico' => MLAnalysis::where('diagnostico', 'basico')->count(),
        'diagnostico_intermedio' => MLAnalysis::where('diagnostico', 'intermedio')->count(),
        'diagnostico_avanzado' => MLAnalysis::where('diagnostico', 'avanzado')->count(),
        'riesgo_alto' => MLAnalysis::where('nivel_riesgo', 'alto')->count(),
        'riesgo_medio' => MLAnalysis::where('nivel_riesgo', 'medio')->count(),
        'riesgo_bajo' => MLAnalysis::where('nivel_riesgo', 'bajo')->count(),
    ];
    
    return view('admin.ml.results', compact('analyses', 'statistics'));
}
}