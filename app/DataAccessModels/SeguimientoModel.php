<?php
// app/DataAccessModels/SeguimientoModel.php

namespace App\DataAccessModels;

use PDO;
use PDOException;

class SeguimientoModel extends BaseModel 
{
    // ==========================================
    // CRUD BÁSICO DE SEGUIMIENTOS
    // ==========================================
    
    /**
     * Crear un nuevo seguimiento
     * @param array $data
     * @return int|false ID del seguimiento creado o false
     */
    public function crear($data) 
    {
        // Validaciones
        if (empty($data['user_id']) || empty($data['admin_id']) || 
            empty($data['type']) || empty($data['scheduled_at'])) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_crear_seguimiento(?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['user_id'],
                $data['admin_id'],
                $data['type'],
                $data['scheduled_at'],
                $data['notes'] ?? null
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result['id'] ?? false;
        } catch (PDOException $e) {
            error_log("Error en crear seguimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener seguimiento por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id) 
    {
        if (!is_numeric($id) || $id <= 0) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_seguimiento(?)");
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar seguimientos de un usuario
     * @param int $userId
     * @return array
     */
    public function listarPorUsuario($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_listar_seguimientos_usuario(?)");
            $stmt->execute([$userId]);
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarPorUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar todos los seguimientos pendientes
     * @return array
     */
    public function listarPendientes() 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_listar_seguimientos_pendientes()");
            $stmt->execute();
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarPendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar un seguimiento
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizar($id, $data)
    {
        if (!is_numeric($id) || $id <= 0 || empty($data)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_actualizar_seguimiento(?, ?, ?, ?)");
            $stmt->execute([
                $id,
                $data['scheduled_at'] ?? null,
                $data['type'] ?? null,
                $data['notes'] ?? null
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Completar un seguimiento
     * @param int $id
     * @param string|null $notes Notas adicionales
     * @return bool
     */
    public function completar($id, $notes = null) 
    {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_completar_seguimiento(?, ?)");
            $stmt->execute([$id, $notes]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en completar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancelar un seguimiento
     * @param int $id
     * @return bool
     */
    public function cancelar($id) 
    {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_cancelar_seguimiento(?)");
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en cancelar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un seguimiento (hard delete)
     * @param int $id
     * @return bool
     */
    public function eliminar($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_eliminar_seguimiento(?)");
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // LISTADOS Y FILTROS AVANZADOS
    // ==========================================

    /**
     * Listar seguimientos por estado
     * @param string $status pending, completed, cancelled
     * @return array
     */
    public function listarPorEstado($status) 
    {
        $statusPermitidos = ['pending', 'completed', 'cancelled'];
        if (!in_array($status, $statusPermitidos)) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    f.id, f.user_id, f.admin_id, f.type, 
                    f.scheduled_at, f.completed_at, f.notes, 
                    f.status, f.created_at,
                    u.name as student_name, u.email as student_email,
                    a.name as admin_name
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                INNER JOIN users a ON f.admin_id = a.id
                WHERE f.status = ?
                ORDER BY f.scheduled_at ASC
            ");
            $stmt->execute([$status]);
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarPorEstado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar seguimientos por tipo
     * @param string $type meeting, call, video_call, email
     * @return array
     */
    public function listarPorTipo($type) 
    {
        $tiposPermitidos = ['meeting', 'call', 'video_call', 'email'];
        if (!in_array($type, $tiposPermitidos)) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    f.id, f.user_id, f.type, f.scheduled_at, 
                    f.status, f.notes,
                    u.name as student_name
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                WHERE f.type = ?
                ORDER BY f.scheduled_at DESC
            ");
            $stmt->execute([$type]);
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarPorTipo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Listar seguimientos por administrador/docente
     * @param int $adminId
     * @return array
     */
    public function listarPorAdmin($adminId) 
    {
        if (!is_numeric($adminId) || $adminId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    f.id, f.user_id, f.type, f.scheduled_at, 
                    f.completed_at, f.status, f.notes,
                    u.name as student_name, u.email as student_email
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                WHERE f.admin_id = ?
                ORDER BY f.scheduled_at DESC
            ");
            $stmt->execute([$adminId]);
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarPorAdmin: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener seguimientos por rango de fechas
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function listarPorRangoFechas($fechaInicio, $fechaFin) 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    f.id, f.user_id, f.admin_id, f.type,
                    f.scheduled_at, f.status, f.notes,
                    u.name as student_name,
                    a.name as admin_name
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                INNER JOIN users a ON f.admin_id = a.id
                WHERE DATE(f.scheduled_at) BETWEEN ? AND ?
                ORDER BY f.scheduled_at ASC
            ");
            $stmt->execute([$fechaInicio, $fechaFin]);
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarPorRangoFechas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener seguimientos de hoy
     * @return array
     */
    public function listarHoy() 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    f.id, f.user_id, f.admin_id, f.type,
                    f.scheduled_at, f.status, f.notes,
                    u.name as student_name, u.email as student_email,
                    a.name as admin_name
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                INNER JOIN users a ON f.admin_id = a.id
                WHERE DATE(f.scheduled_at) = CURDATE()
                AND f.status = 'pending'
                ORDER BY f.scheduled_at ASC
            ");
            $stmt->execute();
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarHoy: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener seguimientos próximos (próximos 7 días)
     * @param int $dias Días hacia adelante
     * @return array
     */
    public function listarProximos($dias = 7) 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    f.id, f.user_id, f.admin_id, f.type,
                    f.scheduled_at, f.status, f.notes,
                    u.name as student_name,
                    a.name as admin_name,
                    DATEDIFF(f.scheduled_at, CURDATE()) as dias_restantes
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                INNER JOIN users a ON f.admin_id = a.id
                WHERE f.scheduled_at >= CURDATE()
                AND f.scheduled_at <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND f.status = 'pending'
                ORDER BY f.scheduled_at ASC
            ");
            $stmt->execute([$dias]);
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarProximos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener seguimientos vencidos (pasada la fecha sin completar)
     * @return array
     */
    public function listarVencidos() 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    f.id, f.user_id, f.admin_id, f.type,
                    f.scheduled_at, f.notes,
                    u.name as student_name, u.email as student_email,
                    a.name as admin_name,
                    DATEDIFF(CURDATE(), f.scheduled_at) as dias_vencido
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                INNER JOIN users a ON f.admin_id = a.id
                WHERE f.scheduled_at < CURDATE()
                AND f.status = 'pending'
                ORDER BY f.scheduled_at ASC
            ");
            $stmt->execute();
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en listarVencidos: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // ESTADÍSTICAS DE SEGUIMIENTOS
    // ==========================================

    /**
     * Contar seguimientos por estado
     * @return array
     */
    public function contarPorEstado() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    status,
                    COUNT(*) as total
                FROM follow_ups
                GROUP BY status
            ");
            
            $resultado = [
                'pending' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $resultado[$row['status']] = (int)$row['total'];
            }
            
            $stmt->closeCursor();
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en contarPorEstado: " . $e->getMessage());
            return ['pending' => 0, 'completed' => 0, 'cancelled' => 0];
        }
    }

    /**
     * Contar seguimientos por tipo
     * @return array
     */
    public function contarPorTipo() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    type,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completados
                FROM follow_ups
                GROUP BY type
            ");
            
            $resultado = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $resultado[$row['type']] = [
                    'total' => (int)$row['total'],
                    'completados' => (int)$row['completados']
                ];
            }
            
            $stmt->closeCursor();
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en contarPorTipo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener resumen de seguimientos
     * @return array
     */
    public function obtenerResumen() 
    {
        return [
            'por_estado' => $this->contarPorEstado(),
            'por_tipo' => $this->contarPorTipo(),
            'hoy' => count($this->listarHoy()),
            'proximos_7_dias' => count($this->listarProximos(7)),
            'vencidos' => count($this->listarVencidos())
        ];
    }

    /**
     * Obtener estadísticas por administrador
     * @param int $adminId
     * @return array
     */
    public function obtenerEstadisticasAdmin($adminId) 
    {
        if (!is_numeric($adminId) || $adminId <= 0) {
            return [
                'total' => 0,
                'pendientes' => 0,
                'completados' => 0,
                'cancelados' => 0
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completados,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelados
                FROM follow_ups
                WHERE admin_id = ?
            ");
            $stmt->execute([$adminId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return [
                'total' => (int)($result['total'] ?? 0),
                'pendientes' => (int)($result['pendientes'] ?? 0),
                'completados' => (int)($result['completados'] ?? 0),
                'cancelados' => (int)($result['cancelados'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasAdmin: " . $e->getMessage());
            return [
                'total' => 0,
                'pendientes' => 0,
                'completados' => 0,
                'cancelados' => 0
            ];
        }
    }

    // ==========================================
    // UTILIDADES
    // ==========================================

    /**
     * Verificar si existe un seguimiento pendiente para un usuario
     * @param int $userId
     * @return bool
     */
    public function tieneSeguimientoPendiente($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM follow_ups
                WHERE user_id = ? AND status = 'pending'
            ");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['total'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en tieneSeguimientoPendiente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar seguimientos
     * @param string $termino
     * @return array
     */
    public function buscar($termino) 
    {
        if (empty($termino)) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    f.id, f.user_id, f.type, f.scheduled_at,
                    f.status, f.notes,
                    u.name as student_name, u.email as student_email
                FROM follow_ups f
                INNER JOIN users u ON f.user_id = u.id
                WHERE u.name LIKE ? 
                OR u.email LIKE ?
                OR f.notes LIKE ?
                ORDER BY f.scheduled_at DESC
            ");
            
            $busqueda = "%{$termino}%";
            $stmt->execute([$busqueda, $busqueda, $busqueda]);
            
            $seguimientos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $seguimientos[] = $row;
            }
            
            $stmt->closeCursor();
            return $seguimientos;
        } catch (PDOException $e) {
            error_log("Error en buscar: " . $e->getMessage());
            return [];
        }
    }
}