<?php
// app/DataAccessModels/RiskAlertModel.php

namespace App\DataAccessModels;
use App\DataAccessModels\BaseModel;
use PDO;
use PDOException;

class RiskAlertModel extends BaseModel 
{
    // ==========================================
    // DETECCIÓN Y ANÁLISIS DE RIESGO
    // ==========================================
    
    /**
     * Obtener alertas de riesgo de un estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerAlertasEstudiante($userId) 
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
            error_log("Error en obtenerAlertasEstudiante: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcular nivel de riesgo de un estudiante
     * @param int $userId
     * @return array
     */
    public function calcularNivelRiesgo($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [
                'risk_level' => 'sin_datos',
                'avg_score' => 0,
                'total_subjects' => 0,
                'total_activities' => 0,
                'last_activity_date' => null
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_calcular_nivel_riesgo(?)");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: [
                'risk_level' => 'sin_datos',
                'avg_score' => 0,
                'total_subjects' => 0,
                'total_activities' => 0,
                'last_activity_date' => null
            ];
        } catch (PDOException $e) {
            error_log("Error en calcularNivelRiesgo: " . $e->getMessage());
            return [
                'risk_level' => 'sin_datos',
                'avg_score' => 0,
                'total_subjects' => 0,
                'total_activities' => 0,
                'last_activity_date' => null
            ];
        }
    }

    /**
     * Verificar si un estudiante está en riesgo
     * @param int $userId
     * @return bool
     */
    public function estudianteEnRiesgo($userId) 
    {
        $riesgo = $this->calcularNivelRiesgo($userId);
        return in_array($riesgo['risk_level'], ['alto', 'medio']);
    }

    /**
     * Obtener estudiantes en riesgo alto
     * @return array
     */
    public function obtenerEstudiantesRiesgoAlto() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    u.id, u.name, u.email, u.student_code, u.career, u.semester,
                    ROUND(AVG(sp.average_score), 2) as avg_score,
                    COUNT(DISTINCT sp.subject_area) as total_subjects,
                    MAX(sp.last_activity) as last_activity,
                    CASE 
                        WHEN AVG(sp.average_score) < 50 THEN 'alto'
                        WHEN AVG(sp.average_score) < 70 THEN 'medio'
                        ELSE 'bajo'
                    END as risk_level
                FROM users u
                INNER JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1
                GROUP BY u.id, u.name, u.email, u.student_code, u.career, u.semester
                HAVING AVG(sp.average_score) < 50
                ORDER BY avg_score ASC
            ");
            
            $estudiantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estudiantes[] = $row;
            }
            
            $stmt->closeCursor();
            return $estudiantes;
        } catch (PDOException $e) {
            error_log("Error en obtenerEstudiantesRiesgoAlto: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estudiantes en riesgo medio
     * @return array
     */
    public function obtenerEstudiantesRiesgoMedio() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    u.id, u.name, u.email, u.student_code, u.career,
                    ROUND(AVG(sp.average_score), 2) as avg_score,
                    COUNT(DISTINCT sp.subject_area) as total_subjects,
                    MAX(sp.last_activity) as last_activity
                FROM users u
                INNER JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1
                GROUP BY u.id, u.name, u.email, u.student_code, u.career
                HAVING AVG(sp.average_score) >= 50 AND AVG(sp.average_score) < 70
                ORDER BY avg_score ASC
            ");
            
            $estudiantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estudiantes[] = $row;
            }
            
            $stmt->closeCursor();
            return $estudiantes;
        } catch (PDOException $e) {
            error_log("Error en obtenerEstudiantesRiesgoMedio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los estudiantes en riesgo (alto + medio)
     * @return array
     */
    public function obtenerTodosEstudiantesEnRiesgo() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    u.id, u.name, u.email, u.student_code, u.career, u.semester,
                    ROUND(AVG(sp.average_score), 2) as avg_score,
                    COUNT(DISTINCT sp.subject_area) as total_subjects,
                    MAX(sp.last_activity) as last_activity,
                    CASE 
                        WHEN AVG(sp.average_score) < 50 THEN 'alto'
                        WHEN AVG(sp.average_score) < 70 THEN 'medio'
                        ELSE 'bajo'
                    END as risk_level
                FROM users u
                INNER JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1
                GROUP BY u.id, u.name, u.email, u.student_code, u.career, u.semester
                HAVING AVG(sp.average_score) < 70
                ORDER BY risk_level DESC, avg_score ASC
            ");
            
            $estudiantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estudiantes[] = $row;
            }
            
            $stmt->closeCursor();
            return $estudiantes;
        } catch (PDOException $e) {
            error_log("Error en obtenerTodosEstudiantesEnRiesgo: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // ANÁLISIS DE INACTIVIDAD
    // ==========================================

    /**
     * Obtener estudiantes inactivos (sin actividad reciente)
     * @param int $diasInactividad Días sin actividad
     * @return array
     */
    public function obtenerEstudiantesInactivos($diasInactividad = 7) 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.id, u.name, u.email, u.student_code, u.career,
                    MAX(sp.last_activity) as last_activity,
                    DATEDIFF(NOW(), MAX(sp.last_activity)) as dias_inactivo
                FROM users u
                LEFT JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1
                GROUP BY u.id, u.name, u.email, u.student_code, u.career
                HAVING MAX(sp.last_activity) IS NULL 
                    OR DATEDIFF(NOW(), MAX(sp.last_activity)) >= ?
                ORDER BY dias_inactivo DESC
            ");
            $stmt->execute([$diasInactividad]);
            
            $estudiantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estudiantes[] = $row;
            }
            
            $stmt->closeCursor();
            return $estudiantes;
        } catch (PDOException $e) {
            error_log("Error en obtenerEstudiantesInactivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un estudiante está inactivo
     * @param int $userId
     * @param int $diasInactividad
     * @return bool
     */
    public function estudianteInactivo($userId, $diasInactividad = 7) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    MAX(last_activity) as last_activity,
                    DATEDIFF(NOW(), MAX(last_activity)) as dias_inactivo
                FROM student_progress
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if (!$result || !$result['last_activity']) {
                return true; // Sin actividad registrada
            }
            
            return $result['dias_inactivo'] >= $diasInactividad;
        } catch (PDOException $e) {
            error_log("Error en estudianteInactivo: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // ESTADÍSTICAS DE RIESGO
    // ==========================================

    /**
     * Contar estudiantes por nivel de riesgo
     * @return array
     */
    public function contarEstudiantesPorRiesgo() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    CASE 
                        WHEN AVG(sp.average_score) < 50 THEN 'alto'
                        WHEN AVG(sp.average_score) < 70 THEN 'medio'
                        ELSE 'bajo'
                    END as risk_level,
                    COUNT(DISTINCT u.id) as total
                FROM users u
                INNER JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1
                GROUP BY risk_level
            ");
            
            $resultados = [
                'alto' => 0,
                'medio' => 0,
                'bajo' => 0
            ];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $resultados[$row['risk_level']] = (int)$row['total'];
            }
            
            $stmt->closeCursor();
            return $resultados;
        } catch (PDOException $e) {
            error_log("Error en contarEstudiantesPorRiesgo: " . $e->getMessage());
            return ['alto' => 0, 'medio' => 0, 'bajo' => 0];
        }
    }

    /**
     * Obtener porcentaje de estudiantes en riesgo
     * @return array
     */
    public function obtenerPorcentajeRiesgo() 
    {
        $conteo = $this->contarEstudiantesPorRiesgo();
        $total = array_sum($conteo);
        
        if ($total === 0) {
            return [
                'alto' => 0,
                'medio' => 0,
                'bajo' => 0,
                'total' => 0
            ];
        }
        
        return [
            'alto' => round(($conteo['alto'] / $total) * 100, 2),
            'medio' => round(($conteo['medio'] / $total) * 100, 2),
            'bajo' => round(($conteo['bajo'] / $total) * 100, 2),
            'total' => $total
        ];
    }

    // ==========================================
    // ANÁLISIS POR CARRERA
    // ==========================================

    /**
     * Obtener estudiantes en riesgo por carrera
     * @param string $career
     * @return array
     */
    public function obtenerEstudiantesRiesgoPorCarrera($career) 
    {
        if (empty($career)) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.id, u.name, u.email, u.student_code, u.semester,
                    ROUND(AVG(sp.average_score), 2) as avg_score,
                    COUNT(DISTINCT sp.subject_area) as total_subjects,
                    MAX(sp.last_activity) as last_activity,
                    CASE 
                        WHEN AVG(sp.average_score) < 50 THEN 'alto'
                        WHEN AVG(sp.average_score) < 70 THEN 'medio'
                        ELSE 'bajo'
                    END as risk_level
                FROM users u
                INNER JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1 AND u.career = ?
                GROUP BY u.id, u.name, u.email, u.student_code, u.semester
                HAVING AVG(sp.average_score) < 70
                ORDER BY risk_level DESC, avg_score ASC
            ");
            $stmt->execute([$career]);
            
            $estudiantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estudiantes[] = $row;
            }
            
            $stmt->closeCursor();
            return $estudiantes;
        } catch (PDOException $e) {
            error_log("Error en obtenerEstudiantesRiesgoPorCarrera: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de riesgo por carrera
     * @return array
     */
    public function obtenerEstadisticasRiesgoPorCarrera() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    u.career,
                    COUNT(DISTINCT u.id) as total_estudiantes,
                    SUM(CASE WHEN AVG(sp.average_score) < 50 THEN 1 ELSE 0 END) as riesgo_alto,
                    SUM(CASE WHEN AVG(sp.average_score) >= 50 AND AVG(sp.average_score) < 70 THEN 1 ELSE 0 END) as riesgo_medio,
                    ROUND(AVG(sp.average_score), 2) as promedio_carrera
                FROM users u
                INNER JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1 AND u.career IS NOT NULL
                GROUP BY u.career
                ORDER BY riesgo_alto DESC, riesgo_medio DESC
            ");
            
            $estadisticas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estadisticas[] = $row;
            }
            
            $stmt->closeCursor();
            return $estadisticas;
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasRiesgoPorCarrera: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // DASHBOARD Y REPORTES
    // ==========================================

    /**
     * Obtener dashboard completo de riesgo
     * @return array
     */
    public function obtenerDashboardRiesgo() 
    {
        return [
            'resumen' => [
                'conteo' => $this->contarEstudiantesPorRiesgo(),
                'porcentajes' => $this->obtenerPorcentajeRiesgo()
            ],
            'estudiantes' => [
                'riesgo_alto' => $this->obtenerEstudiantesRiesgoAlto(),
                'riesgo_medio' => $this->obtenerEstudiantesRiesgoMedio(),
                'inactivos' => $this->obtenerEstudiantesInactivos(7)
            ],
            'por_carrera' => $this->obtenerEstadisticasRiesgoPorCarrera()
        ];
    }

    /**
     * Obtener perfil de riesgo completo de un estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerPerfilRiesgo($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return null;
        }
        
        return [
            'nivel_riesgo' => $this->calcularNivelRiesgo($userId),
            'alertas' => $this->obtenerAlertasEstudiante($userId),
            'en_riesgo' => $this->estudianteEnRiesgo($userId),
            'inactivo' => $this->estudianteInactivo($userId, 7),
            'dias_inactividad' => $this->obtenerDiasInactividad($userId)
        ];
    }

    // ==========================================
    // MÉTODOS DE UTILIDAD
    // ==========================================

    /**
     * Obtener días de inactividad de un estudiante
     * @param int $userId
     * @return int|null
     */
    private function obtenerDiasInactividad($userId) 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT DATEDIFF(NOW(), MAX(last_activity)) as dias_inactivo
                FROM student_progress
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result['dias_inactivo'] ?? null;
        } catch (PDOException $e) {
            error_log("Error en obtenerDiasInactividad: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si un estudiante necesita intervención inmediata
     * @param int $userId
     * @return bool
     */
    public function necesitaIntervencionInmediata($userId) 
    {
        $perfil = $this->obtenerPerfilRiesgo($userId);
        
        if (!$perfil) {
            return false;
        }
        
        // Intervención inmediata si:
        // - Riesgo alto
        // - Más de 14 días inactivo
        // - Tiene alertas críticas
        $riesgoAlto = $perfil['nivel_riesgo']['risk_level'] === 'alto';
        $muyInactivo = ($perfil['dias_inactividad'] ?? 0) > 14;
        
        $alertasCriticas = false;
        foreach ($perfil['alertas'] as $alerta) {
            if (isset($alerta['severity']) && $alerta['severity'] === 'danger') {
                $alertasCriticas = true;
                break;
            }
        }
        
        return $riesgoAlto || $muyInactivo || $alertasCriticas;
    }

    /**
     * Obtener estudiantes que necesitan intervención inmediata
     * @return array
     */
    public function obtenerEstudiantesIntervencionInmediata() 
    {
        $enRiesgo = $this->obtenerTodosEstudiantesEnRiesgo();
        $inactivos = $this->obtenerEstudiantesInactivos(14);
        
        // Combinar y eliminar duplicados
        $estudiantes = [];
        $ids = [];
        
        foreach ($enRiesgo as $estudiante) {
            if ($estudiante['risk_level'] === 'alto') {
                $ids[] = $estudiante['id'];
                $estudiantes[] = array_merge($estudiante, ['razon' => 'Riesgo alto']);
            }
        }
        
        foreach ($inactivos as $estudiante) {
            if (!in_array($estudiante['id'], $ids)) {
                $ids[] = $estudiante['id'];
                $estudiantes[] = array_merge($estudiante, ['razon' => 'Inactividad prolongada']);
            }
        }
        
        return $estudiantes;
    }

    // ==========================================
    /**
 * Generar alerta de riesgo automática
 */
public function generarAlertaAutomatica($userId)
{
    try {
        // Calcular nivel de riesgo
        $stmt = $this->pdo->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                AVG(dr.score) as avg_score,
                COUNT(dr.id) as total_diagnostics,
                DATEDIFF(NOW(), MAX(u.last_login_at)) as dias_inactivo
            FROM users u
            LEFT JOIN diagnostic_responses dr ON u.id = dr.user_id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        
        $stmt->execute([$userId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$data) return null;
        
        $riskLevel = 0;
        $reasons = [];
        
        // Evaluar factores de riesgo
        if ($data['avg_score'] < 50 && $data['avg_score'] > 0) {
            $riskLevel += 3;
            $reasons[] = 'Promedio bajo (' . round($data['avg_score']) . '%)';
        }
        
        if ($data['dias_inactivo'] > 14) {
            $riskLevel += 2;
            $reasons[] = 'Inactividad de ' . $data['dias_inactivo'] . ' días';
        }
        
        if ($data['total_diagnostics'] < 3) {
            $riskLevel += 1;
            $reasons[] = 'Baja participación';
        }
        
        // Si hay riesgo, crear alerta
        if ($riskLevel >= 2) {
            $stmt = $this->pdo->prepare("
                INSERT INTO risk_alerts 
                (user_id, risk_level, reasons, status, created_at)
                VALUES (?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $userId,
                $riskLevel >= 4 ? 'critico' : ($riskLevel >= 3 ? 'alto' : 'medio'),
                json_encode($reasons)
            ]);
            
            return [
                'user_id' => $userId,
                'risk_level' => $riskLevel,
                'reasons' => $reasons
            ];
        }
        
        return null;
        
    } catch (\PDOException $e) {
        error_log("Error generando alerta: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtener alertas pendientes
 */
public function obtenerAlertasPendientes()
{
    try {
        $stmt = $this->pdo->query("
            SELECT 
                ra.*,
                u.name,
                u.email
            FROM risk_alerts ra
            JOIN users u ON ra.user_id = u.id
            WHERE ra.status = 'pending'
            ORDER BY ra.risk_level DESC, ra.created_at DESC
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Error obteniendo alertas: " . $e->getMessage());
        return [];
    }
}
}