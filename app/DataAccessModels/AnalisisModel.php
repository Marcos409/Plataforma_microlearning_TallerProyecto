<?php
// app/DataAccessModels/AnalisisModel.php

namespace App\DataAccessModels;

class AnalisisModel extends BaseModel 
{
    public function analizarRendimiento($userId) 
    {
        return $this->callProcedureSingle('sp_analizar_rendimiento', [$userId]);
    }

    public function predecirRiesgo($userId) 
    {
        return $this->callProcedureSingle('sp_predecir_riesgo', [$userId]);
    }

    // ==========================================
    // MÉTODOS NUEVOS PARA ML ANALYSIS
    // ==========================================
    
    /**
     * Crear un nuevo análisis ML
     * @param array $data
     * @return int|bool ID del análisis creado o false
     */
    public function crearAnalisis($data)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ml_analysis (
                    user_id, diagnostico, ruta_aprendizaje, nivel_riesgo,
                    metricas, recomendaciones, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['diagnostico'] ?? null,
                $data['ruta_aprendizaje'] ?? null,
                $data['nivel_riesgo'] ?? null,
                json_encode($data['metricas'] ?? []),
                json_encode($data['recomendaciones'] ?? [])
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error en crearAnalisis: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener análisis por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerAnalisis($id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    ma.id, ma.user_id, ma.diagnostico, ma.ruta_aprendizaje,
                    ma.nivel_riesgo, ma.metricas, ma.recomendaciones,
                    ma.created_at, ma.updated_at,
                    u.name as student_name, u.email as student_email,
                    u.career, u.semester
                FROM ml_analysis ma
                INNER JOIN users u ON ma.user_id = u.id
                WHERE ma.id = ?
            ");
            
            $stmt->execute([$id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result) {
                // Decodificar JSON
                $result['metricas'] = json_decode($result['metricas'], true);
                $result['recomendaciones'] = json_decode($result['recomendaciones'], true);
            }
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Error en obtenerAnalisis: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar análisis con paginación
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function listarAnalisis($page = 1, $perPage = 20)
    {
        try {
            $offset = ($page - 1) * $perPage;
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    ma.id, ma.user_id, ma.diagnostico, ma.ruta_aprendizaje,
                    ma.nivel_riesgo, ma.created_at,
                    u.name as student_name, u.email as student_email,
                    u.career
                FROM ml_analysis ma
                INNER JOIN users u ON ma.user_id = u.id
                ORDER BY ma.created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $stmt->bindValue(1, $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en listarAnalisis: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar total de análisis
     * @return int
     */
    public function contarAnalisis()
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM ml_analysis");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? (int) $result['total'] : 0;
        } catch (\PDOException $e) {
            error_log("Error en contarAnalisis: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener análisis más reciente de un usuario
     * @param int $userId
     * @return array|null
     */
    public function obtenerAnalisisReciente($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    id, user_id, diagnostico, ruta_aprendizaje, nivel_riesgo,
                    metricas, recomendaciones, created_at
                FROM ml_analysis
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result) {
                $result['metricas'] = json_decode($result['metricas'], true);
                $result['recomendaciones'] = json_decode($result['recomendaciones'], true);
            }
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Error en obtenerAnalisisReciente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener historial de análisis de un usuario
     * @param int $userId
     * @return array
     */
    public function obtenerHistorialUsuario($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    id, diagnostico, ruta_aprendizaje, nivel_riesgo,
                    created_at
                FROM ml_analysis
                WHERE user_id = ?
                ORDER BY created_at DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en obtenerHistorialUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de análisis
     * @return array
     */
    public function obtenerEstadisticas()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN diagnostico = 'basico' THEN 1 ELSE 0 END) as diagnostico_basico,
                    SUM(CASE WHEN diagnostico = 'intermedio' THEN 1 ELSE 0 END) as diagnostico_intermedio,
                    SUM(CASE WHEN diagnostico = 'avanzado' THEN 1 ELSE 0 END) as diagnostico_avanzado,
                    SUM(CASE WHEN nivel_riesgo = 'alto' THEN 1 ELSE 0 END) as riesgo_alto,
                    SUM(CASE WHEN nivel_riesgo = 'medio' THEN 1 ELSE 0 END) as riesgo_medio,
                    SUM(CASE WHEN nivel_riesgo = 'bajo' THEN 1 ELSE 0 END) as riesgo_bajo
                FROM ml_analysis
            ");
            
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [
                'total' => 0,
                'diagnostico_basico' => 0,
                'diagnostico_intermedio' => 0,
                'diagnostico_avanzado' => 0,
                'riesgo_alto' => 0,
                'riesgo_medio' => 0,
                'riesgo_bajo' => 0
            ];
        }
    }

    /**
     * Contar análisis por nivel de diagnóstico
     * @param string $nivel
     * @return int
     */
    public function contarPorDiagnostico($nivel)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM ml_analysis
                WHERE diagnostico = ?
            ");
            
            $stmt->execute([$nivel]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? (int) $result['total'] : 0;
        } catch (\PDOException $e) {
            error_log("Error en contarPorDiagnostico: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar análisis por nivel de riesgo
     * @param string $nivel
     * @return int
     */
    public function contarPorRiesgo($nivel)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM ml_analysis
                WHERE nivel_riesgo = ?
            ");
            
            $stmt->execute([$nivel]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? (int) $result['total'] : 0;
        } catch (\PDOException $e) {
            error_log("Error en contarPorRiesgo: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Actualizar un análisis existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizarAnalisis($id, $data)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE ml_analysis
                SET diagnostico = ?,
                    ruta_aprendizaje = ?,
                    nivel_riesgo = ?,
                    metricas = ?,
                    recomendaciones = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $data['diagnostico'] ?? null,
                $data['ruta_aprendizaje'] ?? null,
                $data['nivel_riesgo'] ?? null,
                json_encode($data['metricas'] ?? []),
                json_encode($data['recomendaciones'] ?? []),
                $id
            ]);
        } catch (\PDOException $e) {
            error_log("Error en actualizarAnalisis: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar análisis antiguos (más de X días)
     * @param int $days
     * @return int Número de registros eliminados
     */
    public function eliminarAntiguos($days = 90)
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM ml_analysis
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            
            $stmt->execute([$days]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Error en eliminarAntiguos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener estudiantes sin análisis reciente (últimos 30 días)
     * @return array
     */
    public function obtenerEstudiantesSinAnalisis()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT u.id, u.name, u.email, u.career
                FROM users u
                WHERE u.role_id = 3 
                AND u.active = 1
                AND u.id NOT IN (
                    SELECT user_id 
                    FROM ml_analysis 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                )
                ORDER BY u.name
            ");
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en obtenerEstudiantesSinAnalisis: " . $e->getMessage());
            return [];
        }
    }
}