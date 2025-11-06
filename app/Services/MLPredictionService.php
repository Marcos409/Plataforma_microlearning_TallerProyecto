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

    /**
     * ✅ Acepta User, stdClass o array
     */
    public function predictDiagnostico($student)
    {
        $data = $this->prepararDatosComunes($student);
        return $this->makeRequest('/predict/diagnostico', $data);
    }

    /**
     * ✅ Acepta User, stdClass o array
     */
    public function predictRuta($student)
    {
        $data = $this->prepararDatosComunes($student);
        return $this->makeRequest('/predict/ruta', $data);
    }

    /**
     * ✅ Acepta User, stdClass o array
     */
    public function predictRiesgo($student)
    {
        $data = $this->prepararDatosComunes($student);
        return $this->makeRequest('/predict/riesgo', $data);
    }

    /**
     * ✅ Prepara los 9 campos comunes - ahora acepta User, stdClass o array
     */
    private function prepararDatosComunes($student)
    {
        // ✅ Normalizar estudiante
        $studentData = $this->normalizeStudent($student);
        $studentId = $studentData['id'];
        
        // ✅ Si es User de Eloquent, usar relaciones
        if ($student instanceof User) {
            $modulosCompletados = $student->studentProgress()->sum('completed_activities') ?? 0;
            $evaluacionesAprobadas = $student->diagnosticResponses()->where('is_correct', true)->count();
            $sesiones = $student->studentProgress()->count() ?? 1;
        } else {
            // ✅ Si es stdClass/array, obtener datos desde BD con queries directas o SP
            $modulosCompletados = $this->getModulosCompletados($studentId);
            $evaluacionesAprobadas = $this->getEvaluacionesAprobadas($studentId);
            $sesiones = $this->getSesiones($studentId);
        }
        
        return [
            'ciclo' => $studentData['semester'] ?? 1,
            'tiempo_estudio' => 15, // Valor por defecto
            'sesiones_semana' => min($sesiones, 10),
            'modulos_completados' => $modulosCompletados,
            'evaluaciones_aprobadas' => $evaluacionesAprobadas,
            'promedio_anterior' => $studentData['promedio_anterior'] ?? 14.5,
            'materia_num' => 0,
            'eficiencia' => $modulosCompletados / max($sesiones, 1),
            'productividad' => $evaluacionesAprobadas / max($sesiones, 1),
        ];
    }

    /**
     * ✅ Normalizar estudiante a array
     */
    private function normalizeStudent($student): array
    {
        if ($student instanceof User) {
            return $student->toArray();
        } elseif ($student instanceof \stdClass) {
            return (array) $student;
        } elseif (is_array($student)) {
            return $student;
        }
        
        throw new \InvalidArgumentException('El estudiante debe ser User, stdClass o array');
    }

    /**
     * ✅ Obtener módulos completados usando PDO
     */
    private function getModulosCompletados($studentId): int
    {
        try {
            $pdo = app('pdo'); // O como accedas a tu PDO
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(completed_activities), 0) as total
                FROM student_progress 
                WHERE user_id = ?
            ");
            $stmt->execute([$studentId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) ($result['total'] ?? 0);
        } catch (\Exception $e) {
            Log::error("Error obteniendo módulos completados: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ Obtener evaluaciones aprobadas usando PDO
     */
    private function getEvaluacionesAprobadas($studentId): int
    {
        try {
            $pdo = app('pdo');
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total
                FROM diagnostic_responses 
                WHERE user_id = ? AND is_correct = 1
            ");
            $stmt->execute([$studentId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int) ($result['total'] ?? 0);
        } catch (\Exception $e) {
            Log::error("Error obteniendo evaluaciones aprobadas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * ✅ Obtener número de sesiones usando PDO
     */
    private function getSesiones($studentId): int
    {
        try {
            $pdo = app('pdo');
            $stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT DATE(created_at)) as total
                FROM student_progress 
                WHERE user_id = ?
            ");
            $stmt->execute([$studentId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return max((int) ($result['total'] ?? 1), 1); // Mínimo 1
        } catch (\Exception $e) {
            Log::error("Error obteniendo sesiones: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Hacer request a la API de ML
     */
    private function makeRequest($endpoint, $data)
    {
        try {
            Log::info("Enviando request a ML API: {$endpoint}", $data);
            
            $response = Http::timeout(30)->post($this->apiUrl . $endpoint, $data);
            
            if ($response->successful()) {
                Log::info("Respuesta exitosa de ML API", $response->json());
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