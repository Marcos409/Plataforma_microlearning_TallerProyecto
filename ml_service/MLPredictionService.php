<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio de predicción ML - Conecta con la API de Python
 * Este es el servicio que usa MLAnalysisController
 */
class MLPredictionService
{
    private string $apiUrl;
    private int $timeout;

    public function __construct()
    {
        $this->apiUrl = env('ML_API_URL', 'http://localhost:5000');
        $this->timeout = env('ML_API_TIMEOUT', 10);
    }

    /**
     * Verificar salud de la API
     */
    public function checkHealth(): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->apiUrl}/health");

            return $response->successful() && 
                   $response->json('status') === 'ok';
        } catch (Exception $e) {
            Log::error('Error al verificar salud de API ML', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Predicción de Diagnóstico
     * Espera un objeto con propiedades del estudiante
     */
    public function predictDiagnostico($student): ?array
    {
        try {
            $datos = $this->prepararDatosEstudiante($student);
            
            Log::info('Enviando datos para diagnóstico', $datos);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiUrl}/predict/diagnostico", $datos);

            if (!$response->successful()) {
                Log::error('Error en predict/diagnostico', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $resultado = $response->json();
            Log::info('Diagnóstico recibido', $resultado);

            return $resultado;

        } catch (Exception $e) {
            Log::error('Excepción en predictDiagnostico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Predicción de Ruta de Aprendizaje
     */
    public function predictRuta($student): ?array
    {
        try {
            $datos = $this->prepararDatosEstudiante($student);
            
            Log::info('Enviando datos para ruta', $datos);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiUrl}/predict/ruta", $datos);

            if (!$response->successful()) {
                Log::error('Error en predict/ruta', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $resultado = $response->json();
            Log::info('Ruta recibida', $resultado);

            return $resultado;

        } catch (Exception $e) {
            Log::error('Excepción en predictRuta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Predicción de Riesgo Académico
     */
    public function predictRiesgo($student): ?array
    {
        try {
            $datos = $this->prepararDatosEstudiante($student);
            
            Log::info('Enviando datos para riesgo', $datos);
            
            $response = Http::timeout($this->timeout)
                ->post("{$this->apiUrl}/predict/riesgo", $datos);

            if (!$response->successful()) {
                Log::error('Error en predict/riesgo', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $resultado = $response->json();
            Log::info('Riesgo recibido', $resultado);

            return $resultado;

        } catch (Exception $e) {
            Log::error('Excepción en predictRiesgo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Prepara los datos del estudiante para enviar a la API
     * Convierte objeto/array del estudiante al formato requerido
     */
    private function prepararDatosEstudiante($student): array
    {
        // Convertir a array si es objeto
        if (is_object($student)) {
            $student = (array) $student;
        }

        // Extraer datos con valores por defecto
        return [
            'ciclo' => (float) ($student['ciclo'] ?? $student['cycle'] ?? 1),
            'tiempo_estudio' => (float) ($student['tiempo_estudio'] ?? $student['study_time'] ?? 10),
            'sesiones_semana' => (float) ($student['sesiones_semana'] ?? $student['sessions_per_week'] ?? 2),
            'modulos_completados' => (float) ($student['modulos_completados'] ?? $student['modules_completed'] ?? 0),
            'evaluaciones_aprobadas' => (float) ($student['evaluaciones_aprobadas'] ?? $student['evaluations_passed'] ?? 0),
            'promedio_anterior' => (float) ($student['promedio_anterior'] ?? $student['previous_average'] ?? 10),
            'materia_num' => (float) ($student['materia_num'] ?? $student['subject_id'] ?? 1),
            'eficiencia' => (float) ($student['eficiencia'] ?? $student['efficiency'] ?? $this->calcularEficiencia($student)),
            'productividad' => (float) ($student['productividad'] ?? $student['productivity'] ?? $this->calcularProductividad($student))
        ];
    }

    /**
     * Calcular eficiencia si no está en los datos
     */
    private function calcularEficiencia($student): float
    {
        $modulos = (float) ($student['modulos_completados'] ?? $student['modules_completed'] ?? 0);
        $evaluaciones = (float) ($student['evaluaciones_aprobadas'] ?? $student['evaluations_passed'] ?? 0);
        
        if ($modulos == 0) {
            return 0.5; // Valor por defecto
        }
        
        return round($evaluaciones / $modulos, 2);
    }

    /**
     * Calcular productividad si no está en los datos
     */
    private function calcularProductividad($student): float
    {
        $sesiones = (float) ($student['sesiones_semana'] ?? $student['sessions_per_week'] ?? 1);
        $modulos = (float) ($student['modulos_completados'] ?? $student['modules_completed'] ?? 0);
        
        if ($sesiones == 0) {
            return 0.5; // Valor por defecto
        }
        
        // Productividad = módulos completados por sesión (aproximado)
        return round($modulos / ($sesiones * 4), 2); // 4 semanas
    }

    /**
     * Obtener análisis completo (los 3 modelos)
     */
    public function getAnalisisCompleto($student): array
    {
        $diagnostico = $this->predictDiagnostico($student);
        $ruta = $this->predictRuta($student);
        $riesgo = $this->predictRiesgo($student);

        return [
            'diagnostico' => $diagnostico,
            'ruta' => $ruta,
            'riesgo' => $riesgo,
            'timestamp' => now()->toIso8601String(),
            'success' => !is_null($diagnostico) && !is_null($ruta) && !is_null($riesgo)
        ];
    }
}