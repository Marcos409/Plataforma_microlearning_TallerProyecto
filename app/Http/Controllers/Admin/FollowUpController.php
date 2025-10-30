<?php
// app/DataAccessModels/SeguimientoModel.php

namespace App\DataAccessModels;

class SeguimientoModel extends BaseModel 
{
    /**
     * Crear un nuevo seguimiento
     * @param array $data
     * @return int|bool ID del seguimiento creado o false
     */
    public function crear($data)
    {
        $result = $this->callProcedureSingle('sp_crear_seguimiento', [
            $data['user_id'],
            $data['admin_id'],
            $data['type'],
            $data['scheduled_at'],
            $data['notes'] ?? null
        ]);
        
        return $result ? $result['id'] : false;
    }

    /**
     * Obtener seguimiento por ID
     * @param int $id
     * @return array|null
     */
    public function buscarPorId($id)
    {
        return $this->callProcedureSingle('sp_obtener_seguimiento', [$id]);
    }

    /**
     * Alias de buscarPorId para compatibilidad
     * @param int $id
     * @return array|null
     */
    public function obtenerSeguimiento($id)
    {
        return $this->buscarPorId($id);
    }

    /**
     * Listar seguimientos de un usuario
     * @param int $userId
     * @return array
     */
    public function listarPorUsuario($userId)
    {
        return $this->callProcedureMultiple('sp_listar_seguimientos_usuario', [$userId]);
    }

    /**
     * Listar seguimientos pendientes
     * @return array
     */
    public function listarPendientes()
    {
        return $this->callProcedureMultiple('sp_listar_seguimientos_pendientes', []);
    }

    /**
     * Listar todos los seguimientos (si existe el SP)
     * @return array
     */
    public function listarTodos()
    {
        // Si tienes un SP para listar todos, úsalo
        // Si no, puedes usar listarPendientes como fallback
        return $this->listarPendientes();
    }

    /**
     * Actualizar un seguimiento
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizar($id, $data)
    {
        return $this->callProcedureNoReturn('sp_actualizar_seguimiento', [
            $id,
            $data['scheduled_at'],
            $data['type'],
            $data['notes'] ?? null
        ]);
    }

    /**
     * Marcar seguimiento como completado
     * @param int $id
     * @param string|null $notes
     * @return bool
     */
    public function completar($id, $notes = null)
    {
        return $this->callProcedureNoReturn('sp_completar_seguimiento', [$id, $notes]);
    }

    /**
     * Cancelar seguimiento
     * @param int $id
     * @return bool
     */
    public function cancelar($id)
    {
        return $this->callProcedureNoReturn('sp_cancelar_seguimiento', [$id]);
    }

    /**
     * Eliminar seguimiento (hard delete)
     * @param int $id
     * @return bool
     */
    public function eliminar($id)
    {
        return $this->callProcedureNoReturn('sp_eliminar_seguimiento', [$id]);
    }

    /**
     * Obtener seguimientos próximos (próximos 7 días)
     * @return array
     */
    public function obtenerProximos()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    f.id, f.user_id, f.admin_id, f.type, f.scheduled_at,
                    f.notes, f.status,
                    u.name as student_name, u.email as student_email,
                    a.name as admin_name
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                INNER JOIN users a ON f.admin_id = a.id
                WHERE f.status = 'pending'
                AND f.scheduled_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                ORDER BY f.scheduled_at ASC
            ");
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en obtenerProximos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar seguimientos pendientes de un usuario
     * @param int $userId
     * @return int
     */
    public function contarPendientesPorUsuario($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM follow_ups
                WHERE user_id = ? AND status = 'pending'
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result ? (int) $result['total'] : 0;
        } catch (\PDOException $e) {
            error_log("Error en contarPendientesPorUsuario: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener seguimientos vencidos (scheduled_at ya pasó y aún pending)
     * @return array
     */
    public function obtenerVencidos()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    f.id, f.user_id, f.admin_id, f.type, f.scheduled_at,
                    f.notes,
                    u.name as student_name, u.email as student_email,
                    a.name as admin_name
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                INNER JOIN users a ON f.admin_id = a.id
                WHERE f.status = 'pending'
                AND f.scheduled_at < NOW()
                ORDER BY f.scheduled_at DESC
            ");
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en obtenerVencidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de seguimientos
     * @return array
     */
    public function obtenerEstadisticas()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completados,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelados,
                    COUNT(CASE WHEN status = 'pending' AND scheduled_at < NOW() THEN 1 END) as vencidos
                FROM follow_ups
            ");
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'pendientes' => 0,
                'completados' => 0,
                'cancelados' => 0,
                'vencidos' => 0
            ];
        }
    }
}