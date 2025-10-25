<?php

namespace App\DataAccessObjects;

use App\Contracts\DiagnosticResponseDAOInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentDiagnosticResponseDAO implements DiagnosticResponseDAOInterface
{
    /**
     * Obtener rendimiento por materia
     * Procedure: sp_get_performance_by_subject
     */
    public function getPerformanceBySubject(): Collection
    {
        try {
            $results = DB::select('CALL sp_get_performance_by_subject()');
            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error en sp_get_performance_by_subject: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener rendimiento de un estudiante
     */
    public function getStudentPerformance(int $userId): array
    {
        try {
            $result = DB::select('
                SELECT 
                    COUNT(*) as total_responses,
                    SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_responses,
                    ROUND((SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as percentage
                FROM diagnostic_responses
                WHERE user_id = ?
            ', [$userId]);

            if (empty($result)) {
                return [
                    'total_responses' => 0,
                    'correct_responses' => 0,
                    'percentage' => 0,
                ];
            }

            return [
                'total_responses' => $result[0]->total_responses ?? 0,
                'correct_responses' => $result[0]->correct_responses ?? 0,
                'percentage' => $result[0]->percentage ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error("Error al obtener rendimiento del estudiante: " . $e->getMessage());
            return [
                'total_responses' => 0,
                'correct_responses' => 0,
                'percentage' => 0,
            ];
        }
    }

    /**
     * Contar diagnósticos completados por usuario
     */
    public function countCompletedDiagnostics(int $userId): int
    {
        try {
            $result = DB::select('
                SELECT COUNT(DISTINCT diagnostic_id) as count
                FROM diagnostic_responses
                WHERE user_id = ?
            ', [$userId]);

            return $result[0]->count ?? 0;
        } catch (\Exception $e) {
            Log::error("Error al contar diagnósticos completados: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Crear nueva respuesta
     */
    public function create(array $data): ?int
    {
        try {
            DB::insert('
                INSERT INTO diagnostic_responses 
                (user_id, diagnostic_id, question_id, user_answer, is_correct, points_earned, time_spent_seconds, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [
                $data['user_id'],
                $data['diagnostic_id'],
                $data['question_id'],
                $data['user_answer'],
                $data['is_correct'],
                $data['points_earned'] ?? 0,
                $data['time_spent_seconds'] ?? 0,
            ]);

            return DB::getPdo()->lastInsertId();
        } catch (\Exception $e) {
            Log::error("Error al crear diagnostic_response: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener respuestas por usuario
     */
    public function getByUser(int $userId): Collection
    {
        try {
            $results = DB::select('
                SELECT * FROM diagnostic_responses
                WHERE user_id = ?
                ORDER BY created_at DESC
            ', [$userId]);

            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener respuestas por usuario: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener respuestas por diagnóstico
     */
    public function getByDiagnostic(int $diagnosticId): Collection
    {
        try {
            $results = DB::select('
                SELECT * FROM diagnostic_responses
                WHERE diagnostic_id = ?
                ORDER BY created_at DESC
            ', [$diagnosticId]);

            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener respuestas por diagnóstico: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener respuestas incorrectas
     */
    public function getIncorrectResponses(int $userId): Collection
    {
        try {
            $results = DB::select('
                SELECT * FROM diagnostic_responses
                WHERE user_id = ?
                AND is_correct = 0
                ORDER BY created_at DESC
            ', [$userId]);

            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener respuestas incorrectas: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener todas las respuestas
     */
    public function getAll(): Collection
    {
        try {
            $results = DB::select('SELECT * FROM diagnostic_responses ORDER BY created_at DESC');
            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener todas las respuestas: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener respuesta por ID
     */
    public function findById(int $id): ?object
    {
        try {
            $result = DB::select('SELECT * FROM diagnostic_responses WHERE id = ?', [$id]);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            Log::error("Error al buscar respuesta: " . $e->getMessage());
            return null;
        }
    }
}