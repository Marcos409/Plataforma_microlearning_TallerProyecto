<?php
// app/DataAccessModels/RutaAprendizajeModel.php

namespace App\DataAccessModels;

use PDO;
use PDOException;

class RutaAprendizajeModel extends BaseModel 
{

    // ==========================================
    // GESTIÓN DE RUTAS DE APRENDIZAJE
    // ==========================================
    
    /**
     * Obtener rutas de aprendizaje del usuario
     * @param int $userId
     * @return array
     */
    public function obtenerRutasUsuario($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_rutas_usuario(?)");
            $stmt->execute([$userId]);
            
            $rutas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rutas[] = $row;
            }
            
            $stmt->closeCursor();
            return $rutas;
        } catch (PDOException $e) {
            error_log("Error en obtenerRutasUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener contenidos de una ruta específica
     * @param int $learningPathId
     * @return array
     */
    public function obtenerContenidosRuta($learningPathId) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_contenidos_ruta(?)");
            $stmt->execute([$learningPathId]);
            
            $contenidos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $contenidos[] = $row;
            }
            
            $stmt->closeCursor();
            return $contenidos;
        } catch (PDOException $e) {
            error_log("Error en obtenerContenidosRuta: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Completar contenido de ruta
     * @param int $learningPathContentId
     * @param int $timeSpent Tiempo en minutos
     * @return bool
     */
    public function completarContenidoRuta($learningPathContentId, $timeSpent = 0) 
    {
        if (!is_numeric($learningPathContentId) || $learningPathContentId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_completar_contenido_ruta(?, ?)");
            $stmt->execute([$learningPathContentId, $timeSpent]);
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en completarContenidoRuta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener una ruta específica con todos sus detalles
     * @param int $learningPathId
     * @return array|null
     */
    public function obtenerRutaCompleta($learningPathId) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0) {
            return null;
        }
        
        try {
            // Obtener datos de la ruta
            $stmt = $this->pdo->prepare("
                SELECT id, user_id, subject_area, name, description,
                       difficulty_level, estimated_duration, progress_percentage,
                       is_completed, completed_at, created_at, updated_at
                FROM learning_paths
                WHERE id = ?
            ");
            $stmt->execute([$learningPathId]);
            
            $ruta = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if (!$ruta) {
                return null;
            }
            
            // Agregar contenidos
            $ruta['contents'] = $this->obtenerContenidosRuta($learningPathId);
            $ruta['total_contents'] = count($ruta['contents']);
            $ruta['completed_contents'] = count(array_filter($ruta['contents'], function($c) {
                return isset($c['is_completed']) && $c['is_completed'] == 1;
            }));
            
            return $ruta;
        } catch (PDOException $e) {
            error_log("Error en obtenerRutaCompleta: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener rutas con contenidos (todas del usuario)
     * @param int $userId
     * @return array
     */
    public function obtenerRutasConContenidos($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $rutas = $this->obtenerRutasUsuario($userId);
            
            foreach ($rutas as &$ruta) {
                $ruta['contents'] = $this->obtenerContenidosRuta($ruta['id']);
                $ruta['total_contents'] = count($ruta['contents']);
                $ruta['completed_contents'] = count(array_filter($ruta['contents'], function($c) {
                    return isset($c['is_completed']) && $c['is_completed'] == 1;
                }));
            }
            
            return $rutas;
        } catch (PDOException $e) {
            error_log("Error en obtenerRutasConContenidos: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // CREAR Y MODIFICAR RUTAS
    // ==========================================

    /**
     * Crear nueva ruta de aprendizaje
     * @param int $userId
     * @param string $subjectArea
     * @param string $name
     * @param string $description
     * @param string $difficultyLevel
     * @param int $estimatedDuration
     * @return int|false ID de la ruta creada o false
     */
    public function crearRuta($userId, $subjectArea, $name, $description, $difficultyLevel, $estimatedDuration) 
    {
        if (!is_numeric($userId) || $userId <= 0 || empty($name)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO learning_paths (
                    user_id, subject_area, name, description,
                    difficulty_level, estimated_duration, progress_percentage,
                    is_completed, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, 0, 0, NOW(), NOW())
            ");
            
            $stmt->execute([
                $userId,
                $subjectArea,
                $name,
                $description,
                $difficultyLevel,
                $estimatedDuration
            ]);
            
            $rutaId = $this->pdo->lastInsertId();
            $stmt->closeCursor();
            
            return $rutaId;
        } catch (PDOException $e) {
            error_log("Error en crearRuta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar ruta de aprendizaje
     * @param int $learningPathId
     * @param array $datos
     * @return bool
     */
    public function actualizarRuta($learningPathId, $datos) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0 || empty($datos)) {
            return false;
        }
        
        try {
            $campos = [];
            $valores = [];
            
            $camposPermitidos = ['name', 'description', 'subject_area', 'difficulty_level', 'estimated_duration'];
            
            foreach ($camposPermitidos as $campo) {
                if (isset($datos[$campo])) {
                    $campos[] = "$campo = ?";
                    $valores[] = $datos[$campo];
                }
            }
            
            if (empty($campos)) {
                return false;
            }
            
            $campos[] = "updated_at = NOW()";
            $valores[] = $learningPathId;
            
            $sql = "UPDATE learning_paths SET " . implode(', ', $campos) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($valores);
            
            $affected = $stmt->rowCount();
            $stmt->closeCursor();
            
            return $affected > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizarRuta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar ruta de aprendizaje
     * @param int $learningPathId
     * @return bool
     */
    public function eliminarRuta($learningPathId) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0) {
            return false;
        }
        
        try {
            // Primero eliminar contenidos asociados
            $stmt = $this->pdo->prepare("DELETE FROM learning_path_content WHERE learning_path_id = ?");
            $stmt->execute([$learningPathId]);
            $stmt->closeCursor();
            
            // Luego eliminar la ruta
            $stmt = $this->pdo->prepare("DELETE FROM learning_paths WHERE id = ?");
            $stmt->execute([$learningPathId]);
            
            $affected = $stmt->rowCount();
            $stmt->closeCursor();
            
            return $affected > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminarRuta: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // GESTIÓN DE CONTENIDOS DE RUTA
    // ==========================================

    /**
     * Agregar contenido a una ruta
     * @param int $learningPathId
     * @param int $contentId
     * @param int $orderIndex
     * @param bool $isRequired
     * @return bool
     */
    public function agregarContenido($learningPathId, $contentId, $orderIndex, $isRequired = true) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0 ||
            !is_numeric($contentId) || $contentId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO learning_path_content (
                    learning_path_id, content_id, order_index, is_required,
                    is_completed, created_at, updated_at
                ) VALUES (?, ?, ?, ?, 0, NOW(), NOW())
            ");
            
            $stmt->execute([
                $learningPathId,
                $contentId,
                $orderIndex,
                $isRequired ? 1 : 0
            ]);
            
            $stmt->closeCursor();
            return true;
        } catch (PDOException $e) {
            error_log("Error en agregarContenido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar contenido de una ruta
     * @param int $learningPathContentId
     * @return bool
     */
    public function eliminarContenido($learningPathContentId) 
    {
        if (!is_numeric($learningPathContentId) || $learningPathContentId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("DELETE FROM learning_path_content WHERE id = ?");
            $stmt->execute([$learningPathContentId]);
            
            $affected = $stmt->rowCount();
            $stmt->closeCursor();
            
            return $affected > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminarContenido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reordenar contenidos de una ruta
     * @param array $orden Array con [id => order_index]
     * @return bool
     */
    public function reordenarContenidos($orden) 
    {
        if (empty($orden) || !is_array($orden)) {
            return false;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("
                UPDATE learning_path_content 
                SET order_index = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            foreach ($orden as $id => $index) {
                $stmt->execute([$index, $id]);
            }
            
            $this->pdo->commit();
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en reordenarContenidos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar contenido como no completado
     * @param int $learningPathContentId
     * @return bool
     */
    public function desmarcarContenido($learningPathContentId) 
    {
        if (!is_numeric($learningPathContentId) || $learningPathContentId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE learning_path_content 
                SET is_completed = 0, 
                    completed_at = NULL,
                    time_spent = 0,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$learningPathContentId]);
            
            $affected = $stmt->rowCount();
            $stmt->closeCursor();
            
            // Recalcular progreso de la ruta
            if ($affected > 0) {
                $this->recalcularProgreso($learningPathContentId);
            }
            
            return $affected > 0;
        } catch (PDOException $e) {
            error_log("Error en desmarcarContenido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Recalcular progreso de una ruta
     * @param int $learningPathContentId
     * @return bool
     */
    private function recalcularProgreso($learningPathContentId) 
    {
        try {
            // Obtener ID de la ruta
            $stmt = $this->pdo->prepare("
                SELECT learning_path_id 
                FROM learning_path_content 
                WHERE id = ?
            ");
            $stmt->execute([$learningPathContentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if (!$result) {
                return false;
            }
            
            $learningPathId = $result['learning_path_id'];
            
            // Calcular progreso
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completados
                FROM learning_path_content
                WHERE learning_path_id = ?
            ");
            $stmt->execute([$learningPathId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if ($stats['total'] > 0) {
                $progreso = ($stats['completados'] / $stats['total']) * 100;
                $completado = $progreso >= 100 ? 1 : 0;
                
                // Actualizar ruta
                $stmt = $this->pdo->prepare("
                    UPDATE learning_paths 
                    SET progress_percentage = ?,
                        is_completed = ?,
                        completed_at = IF(? = 1, NOW(), NULL),
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$progreso, $completado, $completado, $learningPathId]);
                $stmt->closeCursor();
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en recalcularProgreso: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // ESTADÍSTICAS Y PROGRESO
    // ==========================================

    /**
     * Obtener estadísticas de rutas del usuario
     * @param int $userId
     * @return array
     */
    public function obtenerEstadisticasRutas($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [
                'total_paths' => 0,
                'completed_paths' => 0,
                'avg_progress' => 0.00,
                'total_estimated_duration' => 0
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_estadisticas_rutas_estudiante(?)");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: [
                'total_paths' => 0,
                'completed_paths' => 0,
                'avg_progress' => 0.00,
                'total_estimated_duration' => 0
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasRutas: " . $e->getMessage());
            return [
                'total_paths' => 0,
                'completed_paths' => 0,
                'avg_progress' => 0.00,
                'total_estimated_duration' => 0
            ];
        }
    }

    /**
     * Obtener progreso detallado de una ruta
     * @param int $learningPathId
     * @return array
     */
    public function obtenerProgresoDetallado($learningPathId) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0) {
            return [
                'total_contents' => 0,
                'completed_contents' => 0,
                'progress_percentage' => 0.00,
                'total_time_spent' => 0,
                'estimated_duration' => 0,
                'time_remaining' => 0
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    lp.estimated_duration,
                    COUNT(lpc.id) as total_contents,
                    SUM(CASE WHEN lpc.is_completed = 1 THEN 1 ELSE 0 END) as completed_contents,
                    ROUND((SUM(CASE WHEN lpc.is_completed = 1 THEN 1 ELSE 0 END) / COUNT(lpc.id)) * 100, 2) as progress_percentage,
                    SUM(COALESCE(lpc.time_spent, 0)) as total_time_spent
                FROM learning_paths lp
                LEFT JOIN learning_path_content lpc ON lp.id = lpc.learning_path_id
                WHERE lp.id = ?
                GROUP BY lp.id, lp.estimated_duration
            ");
            $stmt->execute([$learningPathId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if ($result) {
                $result['time_remaining'] = max(0, $result['estimated_duration'] - $result['total_time_spent']);
                return $result;
            }
            
            return [
                'total_contents' => 0,
                'completed_contents' => 0,
                'progress_percentage' => 0.00,
                'total_time_spent' => 0,
                'estimated_duration' => 0,
                'time_remaining' => 0
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerProgresoDetallado: " . $e->getMessage());
            return [
                'total_contents' => 0,
                'completed_contents' => 0,
                'progress_percentage' => 0.00,
                'total_time_spent' => 0,
                'estimated_duration' => 0,
                'time_remaining' => 0
            ];
        }
    }

    /**
     * Obtener siguiente contenido no completado
     * @param int $learningPathId
     * @return array|null
     */
    public function obtenerSiguienteContenido($learningPathId) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    lpc.id, lpc.order_index, lpc.is_required,
                    c.id as content_id, c.title, c.description,
                    c.type, c.difficulty_level, c.duration_minutes,
                    c.content_url
                FROM learning_path_content lpc
                INNER JOIN content_library c ON lpc.content_id = c.id
                WHERE lpc.learning_path_id = ? 
                AND lpc.is_completed = 0
                ORDER BY lpc.order_index ASC
                LIMIT 1
            ");
            $stmt->execute([$learningPathId]);
            
            $contenido = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $contenido ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerSiguienteContenido: " . $e->getMessage());
            return null;
        }
    }

    // ==========================================
    // FILTROS Y BÚSQUEDAS
    // ==========================================

    /**
     * Obtener rutas filtradas
     * @param int $userId
     * @param array $filtros
     * @return array
     */
    public function obtenerRutasFiltradas($userId, $filtros = []) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $sql = "
                SELECT id, subject_area, name, description,
                       difficulty_level, estimated_duration, progress_percentage,
                       is_completed, completed_at, created_at
                FROM learning_paths
                WHERE user_id = ?
            ";
            
            $params = [$userId];
            
            if (!empty($filtros['subject_area'])) {
                $sql .= " AND subject_area = ?";
                $params[] = $filtros['subject_area'];
            }
            
            if (!empty($filtros['difficulty_level'])) {
                $sql .= " AND difficulty_level = ?";
                $params[] = $filtros['difficulty_level'];
            }
            
            if (isset($filtros['is_completed'])) {
                $sql .= " AND is_completed = ?";
                $params[] = $filtros['is_completed'];
            }
            
            $sql .= " ORDER BY is_completed ASC, created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $rutas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rutas[] = $row;
            }
            
            $stmt->closeCursor();
            return $rutas;
        } catch (PDOException $e) {
            error_log("Error en obtenerRutasFiltradas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar rutas por término
     * @param int $userId
     * @param string $termino
     * @return array
     */
    public function buscarRutas($userId, $termino) 
    {
        if (!is_numeric($userId) || $userId <= 0 || empty($termino)) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, subject_area, name, description,
                       difficulty_level, progress_percentage, is_completed
                FROM learning_paths
                WHERE user_id = ?
                AND (name LIKE ? OR description LIKE ? OR subject_area LIKE ?)
                ORDER BY created_at DESC
            ");
            
            $busqueda = "%{$termino}%";
            $stmt->execute([$userId, $busqueda, $busqueda, $busqueda]);
            
            $rutas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rutas[] = $row;
            }
            
            $stmt->closeCursor();
            return $rutas;
        } catch (PDOException $e) {
            error_log("Error en buscarRutas: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // UTILIDADES
    // ==========================================

    /**
     * Verificar si una ruta pertenece al usuario
     * @param int $learningPathId
     * @param int $userId
     * @return bool
     */
    public function verificarPropietario($learningPathId, $userId) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0 ||
            !is_numeric($userId) || $userId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count
                FROM learning_paths
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$learningPathId, $userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['count'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en verificarPropietario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clonar una ruta para otro usuario
     * @param int $learningPathId
     * @param int $nuevoUserId
     * @return int|false
     */
    public function clonarRuta($learningPathId, $nuevoUserId) 
    {
        if (!is_numeric($learningPathId) || $learningPathId <= 0 ||
            !is_numeric($nuevoUserId) || $nuevoUserId <= 0) {
            return false;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Obtener ruta original
            $stmt = $this->pdo->prepare("
                SELECT subject_area, name, description, difficulty_level, estimated_duration
                FROM learning_paths
                WHERE id = ?
            ");
            $stmt->execute([$learningPathId]);
            $rutaOriginal = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if (!$rutaOriginal) {
                $this->pdo->rollBack();
                return false;
            }
            
            // Crear nueva ruta
            $nuevaRutaId = $this->crearRuta(
                $nuevoUserId,
                $rutaOriginal['subject_area'],
                $rutaOriginal['name'] . ' (Copia)',
                $rutaOriginal['description'],
                $rutaOriginal['difficulty_level'],
                $rutaOriginal['estimated_duration']
            );
            
            if (!$nuevaRutaId) {
                $this->pdo->rollBack();
                return false;
            }
            
            // Copiar contenidos
            $contenidos = $this->obtenerContenidosRuta($learningPathId);
            foreach ($contenidos as $contenido) {
                $this->agregarContenido(
                    $nuevaRutaId,
                    $contenido['content_id'],
                    $contenido['order_index'],
                    $contenido['is_required']
                );
            }
            
            $this->pdo->commit();
            return $nuevaRutaId;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en clonarRuta: " . $e->getMessage());
            return false;
        }
    }

}