<?php
// app/DataAccessModels/RecomendacionModel.php

namespace App\DataAccessModels;

use PDO;
use PDOException;

class RecomendacionModel extends BaseModel 
{
    // ==========================================
    // GESTIÓN DE RECOMENDACIONES
    // ==========================================
    
    /**
     * Obtener recomendaciones del usuario con detalles del contenido
     * @param int $userId
     * @return array
     */
    public function obtenerRecomendaciones($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_recomendaciones(?)");
            $stmt->execute([$userId]);
            
            $recomendaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $recomendaciones[] = $row;
            }
            
            $stmt->closeCursor();
            return $recomendaciones;
        } catch (PDOException $e) {
            error_log("Error en obtenerRecomendaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Marcar una recomendación como vista
     * @param int $recommendationId
     * @return bool
     */
    public function marcarRecomendacionVista($recommendationId) 
    {
        if (!is_numeric($recommendationId) || $recommendationId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_marcar_recomendacion_vista(?)");
            $stmt->execute([$recommendationId]);
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en marcarRecomendacionVista: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear una nueva recomendación
     * @param int $userId
     * @param int $contentId
     * @param string $reason
     * @param int $priority (1-5, siendo 5 más alta)
     * @param string $generatedBy (system, teacher, admin)
     * @return bool
     */
    public function crearRecomendacion($userId, $contentId, $reason, $priority, $generatedBy) 
    {
        if (!is_numeric($userId) || $userId <= 0 || 
            !is_numeric($contentId) || $contentId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_crear_recomendacion(?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $contentId, $reason, $priority, $generatedBy]);
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en crearRecomendacion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar una recomendación como completada
     * @param int $recommendationId
     * @return bool
     */
    public function marcarRecomendacionCompletada($recommendationId) 
    {
        if (!is_numeric($recommendationId) || $recommendationId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE recommendations 
                SET is_completed = 1, 
                    completed_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$recommendationId]);
            
            $affected = $stmt->rowCount();
            $stmt->closeCursor();
            
            return $affected > 0;
        } catch (PDOException $e) {
            error_log("Error en marcarRecomendacionCompletada: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener recomendaciones pendientes del usuario
     * @param int $userId
     * @return array
     */
    public function obtenerRecomendacionesPendientes($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    r.id, r.reason, r.priority, r.is_viewed, r.created_at,
                    r.generated_by,
                    c.id as content_id, c.title, c.description, 
                    c.subject_area, c.type, c.difficulty_level,
                    c.duration_minutes, c.content_url
                FROM recommendations r
                INNER JOIN content_library c ON r.content_id = c.id
                WHERE r.user_id = ? 
                AND r.is_completed = 0
                ORDER BY r.priority DESC, r.created_at DESC
            ");
            $stmt->execute([$userId]);
            
            $recomendaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $recomendaciones[] = $row;
            }
            
            $stmt->closeCursor();
            return $recomendaciones;
        } catch (PDOException $e) {
            error_log("Error en obtenerRecomendacionesPendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener recomendaciones por prioridad
     * @param int $userId
     * @param int $priority
     * @return array
     */
    public function obtenerRecomendacionesPorPrioridad($userId, $priority) 
    {
        if (!is_numeric($userId) || $userId <= 0 || 
            !is_numeric($priority) || $priority < 1 || $priority > 5) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    r.id, r.reason, r.priority, r.is_viewed, 
                    r.is_completed, r.created_at,
                    c.title, c.subject_area, c.type
                FROM recommendations r
                INNER JOIN content_library c ON r.content_id = c.id
                WHERE r.user_id = ? AND r.priority = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$userId, $priority]);
            
            $recomendaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $recomendaciones[] = $row;
            }
            
            $stmt->closeCursor();
            return $recomendaciones;
        } catch (PDOException $e) {
            error_log("Error en obtenerRecomendacionesPorPrioridad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Eliminar una recomendación
     * @param int $recommendationId
     * @return bool
     */
    public function eliminarRecomendacion($recommendationId) 
    {
        if (!is_numeric($recommendationId) || $recommendationId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("DELETE FROM recommendations WHERE id = ?");
            $stmt->execute([$recommendationId]);
            
            $affected = $stmt->rowCount();
            $stmt->closeCursor();
            
            return $affected > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminarRecomendacion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Contar recomendaciones pendientes
     * @param int $userId
     * @return int
     */
    public function contarRecomendacionesPendientes($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return 0;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM recommendations
                WHERE user_id = ? AND is_completed = 0
            ");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en contarRecomendacionesPendientes: " . $e->getMessage());
            return 0;
        }
    }

    // ==========================================
    // ALERTAS DE RIESGO
    // ==========================================

    /**
     * Obtener alertas de riesgo del estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerAlertasRiesgo($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_alertas_riesgo(?)");
            $stmt->execute([$userId]);
            
            $alertas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alertas[] = $row;
            }
            
            $stmt->closeCursor();
            return $alertas;
        } catch (PDOException $e) {
            error_log("Error en obtenerAlertasRiesgo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si el usuario tiene alertas críticas
     * @param int $userId
     * @return bool
     */
    public function tieneAlertasCriticas($userId) 
    {
        $alertas = $this->obtenerAlertasRiesgo($userId);
        
        foreach ($alertas as $alerta) {
            if (isset($alerta['severity']) && $alerta['severity'] === 'danger') {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Contar alertas por nivel de severidad
     * @param int $userId
     * @return array
     */
    public function contarAlertasPorSeveridad($userId) 
    {
        $alertas = $this->obtenerAlertasRiesgo($userId);
        
        $conteo = [
            'danger' => 0,
            'warning' => 0,
            'info' => 0
        ];
        
        foreach ($alertas as $alerta) {
            $severidad = $alerta['severity'] ?? 'info';
            if (isset($conteo[$severidad])) {
                $conteo[$severidad]++;
            }
        }
        
        return $conteo;
    }

    // ==========================================
    // SEGUIMIENTOS
    // ==========================================

    /**
     * Crear un nuevo seguimiento
     * @param int $userId ID del estudiante
     * @param int $adminId ID del docente/admin
     * @param string $type Tipo: meeting, call, video_call, email
     * @param string $scheduledAt Fecha programada
     * @param string $notes Notas del seguimiento
     * @return int|false ID del seguimiento o false
     */
    public function crearSeguimiento($userId, $adminId, $type, $scheduledAt, $notes = '') 
    {
        if (!is_numeric($userId) || $userId <= 0 || 
            !is_numeric($adminId) || $adminId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_crear_seguimiento(?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $adminId, $type, $scheduledAt, $notes]);
            
            // Obtener el ID insertado
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result['id'] ?? false;
        } catch (PDOException $e) {
            error_log("Error en crearSeguimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar seguimiento (método simplificado - alias)
     * @param int $userId
     * @param int $docenteId
     * @param string $observaciones
     * @return int|false
     */
    public function registrarSeguimiento($userId, $docenteId, $observaciones) 
    {
        // Por defecto: tipo 'meeting', programado para ahora
        return $this->crearSeguimiento(
            $userId, 
            $docenteId, 
            'meeting', 
            date('Y-m-d H:i:s'), 
            $observaciones
        );
    }

    /**
     * Obtener seguimientos de un estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerSeguimientosUsuario($userId) 
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
            error_log("Error en obtenerSeguimientosUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un seguimiento por ID
     * @param int $followUpId
     * @return array|null
     */
    public function obtenerSeguimiento($followUpId) 
    {
        if (!is_numeric($followUpId) || $followUpId <= 0) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_seguimiento(?)");
            $stmt->execute([$followUpId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerSeguimiento: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener seguimientos pendientes (todos)
     * @return array
     */
    public function obtenerSeguimientosPendientes() 
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
            error_log("Error en obtenerSeguimientosPendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Completar un seguimiento
     * @param int $followUpId
     * @param string $notes Notas adicionales
     * @return bool
     */
    public function completarSeguimiento($followUpId, $notes = '') 
    {
        if (!is_numeric($followUpId) || $followUpId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_completar_seguimiento(?, ?)");
            $stmt->execute([$followUpId, $notes]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en completarSeguimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancelar un seguimiento
     * @param int $followUpId
     * @return bool
     */
    public function cancelarSeguimiento($followUpId) 
    {
        if (!is_numeric($followUpId) || $followUpId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_cancelar_seguimiento(?)");
            $stmt->execute([$followUpId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en cancelarSeguimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un seguimiento
     * @param int $followUpId
     * @param string $scheduledAt
     * @param string $type
     * @param string $notes
     * @return bool
     */
    public function actualizarSeguimiento($followUpId, $scheduledAt, $type, $notes) 
    {
        if (!is_numeric($followUpId) || $followUpId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_actualizar_seguimiento(?, ?, ?, ?)");
            $stmt->execute([$followUpId, $scheduledAt, $type, $notes]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizarSeguimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un seguimiento
     * @param int $followUpId
     * @return bool
     */
    public function eliminarSeguimiento($followUpId) 
    {
        if (!is_numeric($followUpId) || $followUpId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_eliminar_seguimiento(?)");
            $stmt->execute([$followUpId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminarSeguimiento: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // GENERACIÓN AUTOMÁTICA DE RECOMENDACIONES
    // ==========================================

    /**
     * Generar recomendaciones automáticas basadas en el rendimiento
     * @param int $userId
     * @return int Número de recomendaciones creadas
     */
    public function generarRecomendacionesAutomaticas($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return 0;
        }
        
        $recomendacionesCreadas = 0;
        
        try {
            // Obtener áreas con bajo rendimiento
            $stmt = $this->pdo->prepare("
                SELECT subject_area, average_score
                FROM student_progress
                WHERE user_id = ? AND average_score < 70
                ORDER BY average_score ASC
                LIMIT 3
            ");
            $stmt->execute([$userId]);
            
            $areasDebiles = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $areasDebiles[] = $row;
            }
            $stmt->closeCursor();
            
            // Para cada área débil, recomendar contenido
            foreach ($areasDebiles as $area) {
                // Buscar contenido recomendado para esa área
                $stmt = $this->pdo->prepare("
                    SELECT id
                    FROM content_library
                    WHERE subject_area = ? 
                    AND active = 1
                    AND difficulty_level = 'Básico'
                    ORDER BY views DESC
                    LIMIT 1
                ");
                $stmt->execute([$area['subject_area']]);
                
                $contenido = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                
                if ($contenido) {
                    $razon = "Bajo rendimiento en " . $area['subject_area'] . 
                             " (promedio: " . $area['average_score'] . "%). " .
                             "Este contenido te ayudará a mejorar.";
                    
                    $this->crearRecomendacion(
                        $userId,
                        $contenido['id'],
                        $razon,
                        5, // Alta prioridad
                        'system'
                    );
                    
                    $recomendacionesCreadas++;
                }
            }
            
            return $recomendacionesCreadas;
        } catch (PDOException $e) {
            error_log("Error en generarRecomendacionesAutomaticas: " . $e->getMessage());
            return $recomendacionesCreadas;
        }
    }

    // ==========================================
    // ESTADÍSTICAS Y RESÚMENES
    // ==========================================

    /**
     * Obtener resumen de recomendaciones del usuario
     * @param int $userId
     * @return array
     */
    public function obtenerResumenRecomendaciones($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [
                'total' => 0,
                'pendientes' => 0,
                'vistas' => 0,
                'completadas' => 0
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_completed = 0 THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN is_viewed = 1 THEN 1 ELSE 0 END) as vistas,
                    SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completadas
                FROM recommendations
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: [
                'total' => 0,
                'pendientes' => 0,
                'vistas' => 0,
                'completadas' => 0
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerResumenRecomendaciones: " . $e->getMessage());
            return [
                'total' => 0,
                'pendientes' => 0,
                'vistas' => 0,
                'completadas' => 0
            ];
        }
    }

    /**
     * Obtener dashboard completo de intervención (recomendaciones + alertas + seguimientos)
     * @param int $userId
     * @return array
     */
    public function obtenerDashboardIntervencion($userId) 
    {
        return [
            'recomendaciones' => [
                'resumen' => $this->obtenerResumenRecomendaciones($userId),
                'pendientes' => $this->obtenerRecomendacionesPendientes($userId),
                'alta_prioridad' => $this->obtenerRecomendacionesPorPrioridad($userId, 5)
            ],
            'alertas' => [
                'lista' => $this->obtenerAlertasRiesgo($userId),
                'tiene_criticas' => $this->tieneAlertasCriticas($userId),
                'conteo_severidad' => $this->contarAlertasPorSeveridad($userId)
            ],
            'seguimientos' => [
                'historial' => $this->obtenerSeguimientosUsuario($userId)
            ]
        ];
    }
    // ==========================================
    /**
 * Generar recomendaciones personalizadas
 */
public function generarRecomendaciones($userId, $limit = 5)
{
    $recomendaciones = [];
    
    try {
        // 1. Obtener áreas débiles del usuario
        $stmt = $this->pdo->prepare("
            SELECT 
                dq.subject_area,
                AVG(CASE WHEN dr.is_correct = 1 THEN 100 ELSE 0 END) as avg_score
            FROM diagnostic_responses dr
            JOIN diagnostic_questions dq ON dr.question_id = dq.id
            WHERE dr.user_id = ?
            GROUP BY dq.subject_area
            HAVING avg_score < 60
            ORDER BY avg_score ASC
        ");
        $stmt->execute([$userId]);
        $areasDebiles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // 2. Recomendar contenidos para áreas débiles
        if (!empty($areasDebiles)) {
            foreach ($areasDebiles as $area) {
                $stmt = $this->pdo->prepare("
                    SELECT 
                        c.*,
                        'area_debil' as razon,
                        ? as area_objetivo
                    FROM contents c
                    WHERE c.subject_area = ?
                    AND c.active = 1
                    AND c.difficulty_level IN ('basico', 'intermedio')
                    AND c.id NOT IN (
                        SELECT content_id FROM content_views WHERE user_id = ?
                    )
                    ORDER BY c.views DESC
                    LIMIT 2
                ");
                
                $stmt->execute([$area['subject_area'], $area['subject_area'], $userId]);
                $contenidos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $recomendaciones = array_merge($recomendaciones, $contenidos);
            }
        }
        
        // 3. Contenidos populares no vistos
        $stmt = $this->pdo->prepare("
            SELECT 
                c.*,
                'popular' as razon,
                NULL as area_objetivo
            FROM contents c
            WHERE c.active = 1
            AND c.id NOT IN (
                SELECT content_id FROM content_views WHERE user_id = ?
            )
            ORDER BY c.views DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit - count($recomendaciones)]);
        $populares = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $recomendaciones = array_merge($recomendaciones, $populares);
        
        // Limitar y retornar
        return array_slice($recomendaciones, 0, $limit);
        
    } catch (\PDOException $e) {
        error_log("Error generando recomendaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Guardar recomendación
 */
public function guardarRecomendacion($userId, $contentId, $razon, $score = 1.0)
{
    try {
        $stmt = $this->pdo->prepare("
            INSERT INTO content_recommendations 
            (user_id, content_id, reason, score, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$userId, $contentId, $razon, $score]);
    } catch (\PDOException $e) {
        error_log("Error guardando recomendación: " . $e->getMessage());
        return false;
    }
}


}