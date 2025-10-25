<?php

namespace App\DataAccessObjects;

use App\Contracts\FollowUpDAOInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentFollowUpDAO implements FollowUpDAOInterface
{
    /**
     * Crear nuevo seguimiento
     * Procedure: sp_create_follow_up
     */
    public function create(array $data): ?int
    {
        try {
            $result = DB::select('CALL sp_create_follow_up(?, ?, ?, ?, ?)', [
                $data['user_id'],
                $data['admin_id'],
                $data['type'],
                $data['scheduled_at'],
                $data['notes'] ?? null
            ]);

            return $result[0]->follow_up_id ?? null;
        } catch (\Exception $e) {
            Log::error("Error en sp_create_follow_up: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener seguimiento por ID
     */
    public function findById(int $id): ?object
    {
        try {
            $result = DB::select('SELECT * FROM follow_ups WHERE id = ?', [$id]);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            Log::error("Error al buscar follow_up: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener todos los seguimientos
     */
    public function getAll(): Collection
    {
        try {
            $results = DB::select('SELECT * FROM follow_ups ORDER BY scheduled_at DESC');
            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener follow_ups: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener seguimientos pendientes
     * Procedure: sp_get_pending_follow_ups
     */
    public function getPending(): Collection
    {
        try {
            $results = DB::select('CALL sp_get_pending_follow_ups()');
            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error en sp_get_pending_follow_ups: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener seguimientos vencidos
     */
    public function getOverdue(): Collection
    {
        try {
            $results = DB::select('
                SELECT * FROM follow_ups 
                WHERE completed = 0 
                AND scheduled_at < NOW()
                ORDER BY scheduled_at
            ');
            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener follow_ups vencidos: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener seguimientos por usuario
     */
    public function getByUser(int $userId): Collection
    {
        try {
            $results = DB::select('
                SELECT * FROM follow_ups 
                WHERE user_id = ?
                ORDER BY scheduled_at DESC
            ', [$userId]);
            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener follow_ups por usuario: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener seguimientos por admin
     */
    public function getByAdmin(int $adminId): Collection
    {
        try {
            $results = DB::select('
                SELECT * FROM follow_ups 
                WHERE admin_id = ?
                ORDER BY scheduled_at DESC
            ', [$adminId]);
            return collect($results);
        } catch (\Exception $e) {
            Log::error("Error al obtener follow_ups por admin: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Marcar como completado
     */
    public function markAsCompleted(int $followUpId): bool
    {
        try {
            DB::statement('
                UPDATE follow_ups 
                SET completed = 1, updated_at = NOW()
                WHERE id = ?
            ', [$followUpId]);
            return true;
        } catch (\Exception $e) {
            Log::error("Error al marcar follow_up completado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar seguimiento
     */
    public function update(int $followUpId, array $data): bool
    {
        try {
            $fields = [];
            $params = [];

            if (isset($data['type'])) {
                $fields[] = 'type = ?';
                $params[] = $data['type'];
            }
            if (isset($data['scheduled_at'])) {
                $fields[] = 'scheduled_at = ?';
                $params[] = $data['scheduled_at'];
            }
            if (isset($data['notes'])) {
                $fields[] = 'notes = ?';
                $params[] = $data['notes'];
            }
            if (isset($data['completed'])) {
                $fields[] = 'completed = ?';
                $params[] = $data['completed'];
            }

            $fields[] = 'updated_at = NOW()';
            $params[] = $followUpId;

            $sql = 'UPDATE follow_ups SET ' . implode(', ', $fields) . ' WHERE id = ?';
            DB::statement($sql, $params);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al actualizar follow_up: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar seguimiento
     */
    public function delete(int $followUpId): bool
    {
        try {
            DB::statement('DELETE FROM follow_ups WHERE id = ?', [$followUpId]);
            return true;
        } catch (\Exception $e) {
            Log::error("Error al eliminar follow_up: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas
     */
    public function getStats(): array
    {
        try {
            $total = DB::select('SELECT COUNT(*) as count FROM follow_ups')[0]->count;
            $pending = DB::select('SELECT COUNT(*) as count FROM follow_ups WHERE completed = 0')[0]->count;
            $completed = DB::select('SELECT COUNT(*) as count FROM follow_ups WHERE completed = 1')[0]->count;
            $overdue = DB::select('SELECT COUNT(*) as count FROM follow_ups WHERE completed = 0 AND scheduled_at < NOW()')[0]->count;

            return [
                'total' => $total,
                'pending' => $pending,
                'completed' => $completed,
                'overdue' => $overdue,
            ];
        } catch (\Exception $e) {
            Log::error("Error al obtener stats de follow_ups: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
                'overdue' => 0,
            ];
        }
    }
}