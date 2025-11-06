<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecommendationController extends Controller
{
    protected $mlApiUrl;

    public function __construct()
    {
        $this->mlApiUrl = env('ML_API_URL', 'http://localhost:5000');
    }

    /**
     * Dashboard principal con predicciones ML
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Obtener o crear perfil del estudiante
        $profile = $this->getOrCreateStudentProfile($user->id);
        
        // Obtener predicciones
        $diagnostico = $this->predictDiagnostico($user->id);
        $ruta = $this->predictRuta($user->id);
        $riesgo = $this->predictRiesgo($user->id);
        
        // Obtener contenido recomendado
        $recomendaciones = $this->getRecommendedContent($diagnostico, $ruta);
        
        // Obtener progreso actual
        $progreso = $this->getUserProgress($user->id);
        
        return view('student.ml-dashboard', compact(
            'profile',
            'diagnostico',
            'ruta',
            'riesgo',
            'recomendaciones',
            'progreso'
        ));
    }

    /**
     * Obtener o crear perfil del estudiante
     */
    private function getOrCreateStudentProfile($userId)
    {
        $profile = DB::table('student_profiles')->where('user_id', $userId)->first();
        
        if (!$profile) {
            // Calcular valores iniciales basados en actividad
            $stats = $this->calculateInitialStats($userId);
            
            DB::table('student_profiles')->insert([
                'user_id' => $userId,
                'ciclo' => $stats['ciclo'],
                'tiempo_estudio' => $stats['tiempo_estudio'],
                'sesiones_semana' => $stats['sesiones_semana'],
                'modulos_completados' => $stats['modulos_completados'],
                'evaluaciones_aprobadas' => $stats['evaluaciones_aprobadas'],
                'promedio_anterior' => $stats['promedio_anterior'],
                'materia_num' => $stats['materia_num'],
                'eficiencia' => $stats['eficiencia'],
                'productividad' => $stats['productividad'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $profile = DB::table('student_profiles')->where('user_id', $userId)->first();
        }
        
        return $profile;
    }

    /**
     * Calcular estadísticas iniciales del estudiante
     */
    private function calculateInitialStats($userId)
    {
        // Contar módulos completados
        $modulosCompletados = DB::table('user_progress')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        // Calcular promedio de calificaciones
        $promedio = DB::table('user_progress')
            ->where('user_id', $userId)
            ->whereNotNull('score')
            ->avg('score') ?? 0;

        // Calcular tiempo promedio de estudio
        $tiempoPromedio = DB::table('user_progress')
            ->where('user_id', $userId)
            ->avg('time_spent') ?? 0;
        $tiempoPromedio = round($tiempoPromedio / 60); // Convertir a minutos

        // Calcular sesiones por semana (últimas 4 semanas)
        $sesiones = DB::table('user_progress')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subWeeks(4))
            ->distinct('content_id')
            ->count();
        $sesionesSemana = round($sesiones / 4);

        // Calcular evaluaciones aprobadas
        $evaluacionesAprobadas = DB::table('user_progress')
            ->where('user_id', $userId)
            ->where('score', '>=', 60)
            ->count();

        // Calcular eficiencia (completados / intentados)
        $intentados = DB::table('user_progress')
            ->where('user_id', $userId)
            ->count();
        $eficiencia = $intentados > 0 ? $modulosCompletados / $intentados : 0;

        // Calcular productividad (score promedio / tiempo promedio)
        $productividad = $tiempoPromedio > 0 ? ($promedio / 100) * (60 / $tiempoPromedio) : 0;

        return [
            'ciclo' => 1,
            'tiempo_estudio' => max(30, $tiempoPromedio),
            'sesiones_semana' => max(1, $sesionesSemana),
            'modulos_completados' => $modulosCompletados,
            'evaluaciones_aprobadas' => $evaluacionesAprobadas,
            'promedio_anterior' => round($promedio, 2),
            'materia_num' => 1,
            'eficiencia' => round($eficiencia, 2),
            'productividad' => round($productividad, 2)
        ];
    }

    /**
     * Predecir diagnóstico del estudiante
     */
    public function predictDiagnostico($userId)
    {
        $profile = DB::table('student_profiles')->where('user_id', $userId)->first();
        
        if (!$profile) {
            $profile = (object) $this->calculateInitialStats($userId);
        }

        try {
            $response = Http::timeout(10)->post("{$this->mlApiUrl}/predict/diagnostico", [
                'ciclo' => $profile->ciclo,
                'tiempo_estudio' => $profile->tiempo_estudio,
                'sesiones_semana' => $profile->sesiones_semana,
                'modulos_completados' => $profile->modulos_completados,
                'evaluaciones_aprobadas' => $profile->evaluaciones_aprobadas,
                'promedio_anterior' => $profile->promedio_anterior,
                'materia_num' => $profile->materia_num,
                'eficiencia' => $profile->eficiencia,
                'productividad' => $profile->productividad
            ]);

            if ($response->successful()) {
                $prediction = $response->json();
                
                // Guardar predicción
                DB::table('diagnostic_predictions')->insert([
                    'user_id' => $userId,
                    'nivel' => $prediction['nivel'],
                    'tiene_baja_retencion' => $prediction['tiene_baja_retencion'],
                    'probabilidad' => $prediction['probabilidad'],
                    'temas_problematicos' => json_encode($prediction['temas_problematicos']),
                    'contenido_recomendado' => json_encode($prediction['contenido_recomendado']),
                    'predicted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return $prediction;
            }

        } catch (\Exception $e) {
            Log::error('Error en predicción de diagnóstico', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }

        return $this->getDefaultDiagnostico();
    }

    /**
     * Predecir ruta de aprendizaje
     */
    public function predictRuta($userId)
    {
        $profile = DB::table('student_profiles')->where('user_id', $userId)->first();
        
        if (!$profile) {
            $profile = (object) $this->calculateInitialStats($userId);
        }

        try {
            $response = Http::timeout(10)->post("{$this->mlApiUrl}/predict/ruta", [
                'ciclo' => $profile->ciclo,
                'tiempo_estudio' => $profile->tiempo_estudio,
                'sesiones_semana' => $profile->sesiones_semana,
                'modulos_completados' => $profile->modulos_completados,
                'evaluaciones_aprobadas' => $profile->evaluaciones_aprobadas,
                'promedio_anterior' => $profile->promedio_anterior,
                'materia_num' => $profile->materia_num,
                'eficiencia' => $profile->eficiencia,
                'productividad' => $profile->productividad
            ]);

            if ($response->successful()) {
                $prediction = $response->json();
                
                // Guardar predicción
                DB::table('learning_path_predictions')->insert([
                    'user_id' => $userId,
                    'tipo_ruta' => $prediction['tipo_ruta'],
                    'progreso_esperado' => $prediction['progreso_esperado'],
                    'dificultad_recomendada' => $prediction['dificultad_recomendada'],
                    'ruta_aprendizaje' => json_encode($prediction['ruta_aprendizaje']),
                    'predicted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return $prediction;
            }

        } catch (\Exception $e) {
            Log::error('Error en predicción de ruta', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }

        return $this->getDefaultRuta();
    }

    /**
     * Predecir riesgo académico
     */
    public function predictRiesgo($userId)
    {
        $profile = DB::table('student_profiles')->where('user_id', $userId)->first();
        
        if (!$profile) {
            $profile = (object) $this->calculateInitialStats($userId);
        }

        try {
            $response = Http::timeout(10)->post("{$this->mlApiUrl}/predict/riesgo", [
                'ciclo' => (float) $profile->ciclo,
                'tiempo_estudio' => (float) $profile->tiempo_estudio,
                'sesiones_semana' => (float) $profile->sesiones_semana,
                'modulos_completados' => (float) $profile->modulos_completados,
                'evaluaciones_aprobadas' => (float) $profile->evaluaciones_aprobadas,
                'promedio_anterior' => (float) $profile->promedio_anterior,
                'materia_num' => (float) $profile->materia_num,
                'eficiencia' => (float) $profile->eficiencia,
                'productividad' => (float) $profile->productividad
            ]);

            if ($response->successful()) {
                $prediction = $response->json();
                
                // Guardar predicción
                DB::table('risk_predictions')->insert([
                    'user_id' => $userId,
                    'nivel_riesgo' => $prediction['nivel_riesgo'],
                    'tiene_riesgo' => $prediction['tiene_riesgo'],
                    'probabilidad_riesgo' => $prediction['probabilidad_riesgo'],
                    'severidad' => $prediction['severidad'],
                    'actividades_refuerzo' => json_encode($prediction['actividades_refuerzo']),
                    'predicted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return $prediction;
            }

        } catch (\Exception $e) {
            Log::error('Error en predicción de riesgo', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }

        return $this->getDefaultRiesgo();
    }

    /**
     * Obtener contenido recomendado basado en predicciones
     */
    private function getRecommendedContent($diagnostico, $ruta)
    {
        $dificultad = $ruta['dificultad_recomendada'] ?? 'basico';
        
        // Obtener contenidos según dificultad y temas problemáticos
        $query = DB::table('learning_contents')
            ->where('active', 1)
            ->where('difficulty_level', $dificultad);

        // Si hay temas problemáticos, filtrar por ellos
        if (!empty($diagnostico['temas_problematicos'])) {
            $query->whereIn('topic', $diagnostico['temas_problematicos']);
        }

        $contents = $query->limit(6)->get();

        // Enriquecer con información de progreso del usuario
        return $contents->map(function($content) {
            $progress = DB::table('user_progress')
                ->where('user_id', Auth::id())
                ->where('content_id', $content->id)
                ->first();

            return [
                'id' => $content->id,
                'title' => $content->title,
                'description' => $content->description,
                'content_type' => $content->content_type,
                'difficulty_level' => $content->difficulty_level,
                'estimated_duration' => $content->estimated_duration,
                'thumbnail' => $content->thumbnail ?? '/images/default-content.jpg',
                'progress' => $progress ? $progress->progress_percentage : 0,
                'status' => $progress ? $progress->status : 'not_started'
            ];
        })->toArray();
    }

    /**
     * Obtener progreso del usuario
     */
    private function getUserProgress($userId)
    {
        $total = DB::table('learning_contents')->where('active', 1)->count();
        $completed = DB::table('user_progress')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->count();
        $inProgress = DB::table('user_progress')
            ->where('user_id', $userId)
            ->where('status', 'in_progress')
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'not_started' => $total - $completed - $inProgress,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0
        ];
    }

    /**
     * Actualizar perfil del estudiante
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'ciclo' => 'nullable|integer|min:1',
            'tiempo_estudio' => 'nullable|integer|min:0',
            'sesiones_semana' => 'nullable|integer|min:0'
        ]);

        $userId = Auth::id();
        
        // Recalcular estadísticas
        $stats = $this->calculateInitialStats($userId);
        
        // Merge con datos validados
        $data = array_merge($stats, $validated);
        $data['updated_at'] = now();

        DB::table('student_profiles')
            ->updateOrInsert(
                ['user_id' => $userId],
                $data
            );

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado'
        ]);
    }

    /**
     * Valores por defecto
     */
    private function getDefaultDiagnostico()
    {
        return [
            'nivel' => 'basico',
            'tiene_baja_retencion' => 0,
            'probabilidad' => 0.5,
            'temas_problematicos' => [],
            'contenido_recomendado' => ['Videos de repaso', 'Ejercicios básicos']
        ];
    }

    private function getDefaultRuta()
    {
        return [
            'tipo_ruta' => 'refuerzo_basico',
            'progreso_esperado' => 'bajo',
            'dificultad_recomendada' => 'basico',
            'ruta_aprendizaje' => [
                ['paso' => 1, 'contenido' => 'Fundamentos', 'tipo' => 'video'],
                ['paso' => 2, 'contenido' => 'Ejercicios básicos', 'tipo' => 'quiz']
            ]
        ];
    }

    private function getDefaultRiesgo()
    {
        return [
            'nivel_riesgo' => 'medio',
            'tiene_riesgo' => 0,
            'probabilidad_riesgo' => 0.3,
            'severidad' => 'medio',
            'actividades_refuerzo' => []
        ];
    }
}