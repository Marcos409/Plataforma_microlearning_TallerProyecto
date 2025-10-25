<?php

namespace App\DataAccessObjects;

use App\Contracts\StudentProgressDAOInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentStudentProgressDAO implements StudentProgressDAOInterface
{
    /**
     * Obtener todo el progreso
     */
    public function getAll(): Collection
    {
        try {
            $results = DB::select('SELECT * FROM student_progress ORDER BY created_at DESC');
            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener student_progress: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener progreso por usuario
     */
    public function getByUser(int $userId): Collection
    {
        try {
            $results = DB::select('
                SELECT * FROM student_progress
                WHERE user_id = ?
                ORDER BY created_at DESC
            ', [$userId]);

            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener progreso por usuario: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Crear registro de progreso
     */
    public function create(array $data): ?int
    {
        try {
            DB::insert('
                INSERT INTO student_progress 
                (user_id, subject_area, topic, total_activities, completed_activities, progress_percentage, average_score, total_time_spent, last_activity, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [
                $data['user_id'],
                $data['subject_area'] ?? null,
                $data['topic'] ?? null,
                $data['total_activities'] ?? 0,
                $data['completed_activities'] ?? 0,
                $data['progress_percentage'] ?? 0,
                $data['average_score'] ?? 0,
                $data['total_time_spent'] ?? 0,
                $data['last_activity'] ?? null,
            ]);

            return DB::getPdo()->lastInsertId();
        } catch (\Exception $e) {
            Log::error("Error al crear student_progress: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar progreso
     */
    public function update(int $progressId, array $data): bool
    {
        try {
            $fields = [];
            $params = [];

            if (isset($data['total_activities'])) {
                $fields[] = 'total_activities = ?';
                $params[] = $data['total_activities'];
            }
            if (isset($data['completed_activities'])) {
                $fields[] = 'completed_activities = ?';
                $params[] = $data['completed_activities'];
            }
            if (isset($data['progress_percentage'])) {
                $fields[] = 'progress_percentage = ?';
                $params[] = $data['progress_percentage'];
            }
            if (isset($data['average_score'])) {
                $fields[] = 'average_score = ?';
                $params[] = $data['average_score'];
            }
            if (isset($data['total_time_spent'])) {
                $fields[] = 'total_time_spent = ?';
                $params[] = $data['total_time_spent'];
            }
            if (isset($data['last_activity'])) {
                $fields[] = 'last_activity = ?';
                $params[] = $data['last_activity'];
            }

            $fields[] = 'updated_at = NOW()';
            $params[] = $progressId;

            $sql = 'UPDATE student_progress SET ' . implode(', ', $fields) . ' WHERE id = ?';
            DB::statement($sql, $params);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al actualizar student_progress: " . $e->getMessage());
            return false;
        }
    }
}