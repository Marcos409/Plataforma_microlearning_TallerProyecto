<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class MLPredictionService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.ml.url', 'http://localhost:5000');
    }

    public function predictDiagnostico(User $student)
    {
        $data = $this->prepararDatosComunes($student);
        return $this->makeRequest('/predict/diagnostico', $data);
    }

    public function predictRuta(User $student)
    {
        $data = $this->prepararDatosComunes($student);
        return $this->makeRequest('/predict/ruta', $data);
    }

    public function predictRiesgo(User $student)
    {
        $data = $this->prepararDatosComunes($student);
        return $this->makeRequest('/predict/riesgo', $data);
    }

    /**
     * Prepara los 9 campos comunes que necesitan todos los endpoints
     */
    private function prepararDatosComunes(User $student)
    {
        $modulosCompletados = $student->studentProgress()->sum('completed_activities') ?? 0;
        $evaluacionesAprobadas = $student->diagnosticResponses()->where('is_correct', true)->count();
        $sesiones = $student->studentProgress()->count() ?? 1;
        
        return [
            'ciclo' => $student->semester ?? 1,
            'tiempo_estudio' => 15, // Valor por defecto o de tu BD
            'sesiones_semana' => min($sesiones, 10),
            'modulos_completados' => $modulosCompletados,
            'evaluaciones_aprobadas' => $evaluacionesAprobadas,
            'promedio_anterior' => 14.5, // Obtener de BD si existe
            'materia_num' => 0,
            'eficiencia' => $modulosCompletados / max($sesiones, 1),
            'productividad' => $evaluacionesAprobadas / max($sesiones, 1),
        ];
    }

    private function makeRequest($endpoint, $data)
    {
        try {
            $response = Http::timeout(30)->post($this->apiUrl . $endpoint, $data);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('ML API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('ML Service Error: ' . $e->getMessage());
            return null;
        }
    }
}