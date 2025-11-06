<?php
// app/DataAccessModels/ProgresoModel.php

namespace App\DataAccessModels;

use PDO;
use PDOException;

class ProgresoModel extends BaseModel 
{
    // ==========================================
    // PROGRESO DEL ESTUDIANTE
    // ==========================================
    
    /**
     * Obtener progreso general del estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerProgresoEstudiante($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_progreso_estudiante(?)");
            $stmt->execute([$userId]);
            
            $progreso = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $progreso[] = $row;
            }
            
            $stmt->closeCursor();
            return $progreso;
        } catch (PDOException $e) {
            error_log("Error en obtenerProgresoEstudiante: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar progreso del estudiante
     * @param int $userId
     * @param string $subjectArea
     * @param int $completedActivities
     * @param float $score
     * @param int $timeSpent (en minutos)
     * @return bool
     */
    public function actualizarProgreso($userId, $subjectArea, $completedActivities, $score, $timeSpent) 
    {
        if (!is_numeric($userId) || $userId <= 0 || empty($subjectArea)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_actualizar_progreso(?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $subjectArea,
                $completedActivities,
                $score,
                $timeSpent
            ]);
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en actualizarProgreso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener progreso por área de materia
     * @param int $userId
     * @param string $subjectArea
     * @return array|null
     */
    public function obtenerProgresoPorArea($userId, $subjectArea) 
    {
        if (!is_numeric($userId) || $userId <= 0 || empty($subjectArea)) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, subject_area, topic, total_activities, 
                       completed_activities, progress_percentage, 
                       average_score, total_time_spent, last_activity
                FROM student_progress
                WHERE user_id = ? AND subject_area = ?
            ");
            $stmt->execute([$userId, $subjectArea]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerProgresoPorArea: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener resumen de progreso general
     * @param int $userId
     * @return array
     */
    public function obtenerResumenProgreso($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [
                'total_areas' => 0,
                'promedio_progreso' => 0.00,
                'total_actividades' => 0,
                'tiempo_total' => 0,
                'ultima_actividad' => null
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(DISTINCT subject_area) as total_areas,
                    ROUND(AVG(progress_percentage), 2) as promedio_progreso,
                    SUM(completed_activities) as total_actividades,
                    SUM(total_time_spent) as tiempo_total,
                    MAX(last_activity) as ultima_actividad
                FROM student_progress
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: [
                'total_areas' => 0,
                'promedio_progreso' => 0.00,
                'total_actividades' => 0,
                'tiempo_total' => 0,
                'ultima_actividad' => null
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerResumenProgreso: " . $e->getMessage());
            return [
                'total_areas' => 0,
                'promedio_progreso' => 0.00,
                'total_actividades' => 0,
                'tiempo_total' => 0,
                'ultima_actividad' => null
            ];
        }
    }

    /**
     * Obtener áreas con mejor rendimiento
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function obtenerMejoresAreas($userId, $limit = 5) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT subject_area, topic, progress_percentage, 
                       average_score, completed_activities
                FROM student_progress
                WHERE user_id = ?
                ORDER BY average_score DESC, progress_percentage DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            
            $areas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $areas[] = $row;
            }
            
            $stmt->closeCursor();
            return $areas;
        } catch (PDOException $e) {
            error_log("Error en obtenerMejoresAreas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener áreas que necesitan atención
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function obtenerAreasAtencion($userId, $limit = 5) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT subject_area, topic, progress_percentage, 
                       average_score, completed_activities
                FROM student_progress
                WHERE user_id = ? AND (average_score < 70 OR progress_percentage < 50)
                ORDER BY average_score ASC, progress_percentage ASC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            
            $areas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $areas[] = $row;
            }
            
            $stmt->closeCursor();
            return $areas;
        } catch (PDOException $e) {
            error_log("Error en obtenerAreasAtencion: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // DASHBOARDS
    // ==========================================

    /**
     * Dashboard del estudiante
     * @param int $userId
     * @return array|null
     */
    public function dashboardEstudiante($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_dashboard_estudiante(?)");
            $stmt->execute([$userId]);
            
            // Primer resultado: Datos del usuario
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return null;
            }
            
            // Segundo resultado: Progreso general
            $progreso = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return null;
            }
            
            // Tercer resultado: Rutas activas
            $rutasActivas = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return null;
            }
            
            // Cuarto resultado: Recomendaciones pendientes
            $recomendaciones = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt->closeCursor();
            
            return [
                'usuario' => $usuario ?: [],
                'progreso' => $progreso ?: [
                    'total_subjects' => 0,
                    'avg_progress' => 0,
                    'total_completed_activities' => 0,
                    'total_study_time' => 0
                ],
                'rutas_activas' => $rutasActivas ?: ['active_paths' => 0],
                'recomendaciones' => $recomendaciones ?: ['pending_recommendations' => 0]
            ];
        } catch (PDOException $e) {
            error_log("Error en dashboardEstudiante: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Dashboard del docente
     * @return array|null
     */
    public function dashboardDocente() 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_dashboard_docente()");
            $stmt->execute();
            
            // Primer resultado: Total estudiantes
            $totalStudents = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return null;
            }
            
            // Segundo resultado: Estudiantes con buen progreso
            $goodProgress = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return null;
            }
            
            // Tercer resultado: Estudiantes en riesgo
            $atRisk = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return null;
            }
            
            // Cuarto resultado: Diagnósticos completados hoy
            $diagnosticsToday = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt->closeCursor();
            
            return [
                'total_students' => $totalStudents['total_students'] ?? 0,
                'good_progress' => $goodProgress['good_progress_students'] ?? 0,
                'at_risk' => $atRisk['at_risk_students'] ?? 0,
                'diagnostics_today' => $diagnosticsToday['diagnostics_completed_today'] ?? 0
            ];
        } catch (PDOException $e) {
            error_log("Error en dashboardDocente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Dashboard del administrador
     * @return array|null
     */
    public function dashboardAdmin() 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_dashboard_admin()");
            $stmt->execute();
            
            // Primer resultado: Total estudiantes
            $students = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            
            // Segundo resultado: Total docentes
            $teachers = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            
            // Tercer resultado: Total contenidos
            $contents = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            
            // Cuarto resultado: Diagnósticos recientes
            $diagnostics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt->closeCursor();
            
            return [
                'total_students' => $students['total_students'] ?? 0,
                'total_teachers' => $teachers['total_teachers'] ?? 0,
                'total_contents' => $contents['total_contents'] ?? 0,
                'recent_diagnostics' => $diagnostics['recent_diagnostics'] ?? 0
            ];
        } catch (PDOException $e) {
            error_log("Error en dashboardAdmin: " . $e->getMessage());
            return null;
        }
    }

    // ==========================================
    // ESTADÍSTICAS GENERALES
    // ==========================================

    /**
     * Obtener estudiantes por carrera
     * @return array
     */
    public function estudiantesPorCarrera() 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_estudiantes_por_carrera()");
            $stmt->execute();
            
            $estudiantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estudiantes[] = $row;
            }
            
            $stmt->closeCursor();
            return $estudiantes;
        } catch (PDOException $e) {
            error_log("Error en estudiantesPorCarrera: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener total de actividades completadas (todos los usuarios)
     * @return int
     */
    public function obtenerTotalActividadesCompletadas() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT COALESCE(SUM(completed_activities), 0) as total
                FROM student_progress
            ");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en obtenerTotalActividadesCompletadas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener total de actividades completadas por usuario
     * @param int $userId
     * @return int
     */
    public function obtenerActividadesUsuario($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return 0;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_contar_actividades_usuario(?)");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en obtenerActividadesUsuario: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener tiempo total de estudio (todos los usuarios)
     * @return int Tiempo en minutos
     */
    public function obtenerTiempoEstudioTotal() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT COALESCE(SUM(total_time_spent), 0) as total
                FROM student_progress
            ");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en obtenerTiempoEstudioTotal: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener tiempo de estudio por usuario
     * @param int $userId
     * @return int Tiempo en minutos
     */
    public function obtenerTiempoEstudioUsuario($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return 0;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(SUM(total_time_spent), 0) as total
                FROM student_progress
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en obtenerTiempoEstudioUsuario: " . $e->getMessage());
            return 0;
        }
    }

    // ==========================================
    // COMPARATIVAS Y RANKINGS
    // ==========================================

    /**
     * Obtener ranking de estudiantes por progreso
     * @param int $limit
     * @return array
     */
    public function obtenerRankingProgreso($limit = 10) 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.id, u.name, u.email, u.career,
                    ROUND(AVG(sp.progress_percentage), 2) as promedio_progreso,
                    SUM(sp.completed_activities) as total_actividades
                FROM users u
                INNER JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1
                GROUP BY u.id, u.name, u.email, u.career
                ORDER BY promedio_progreso DESC, total_actividades DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            $ranking = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ranking[] = $row;
            }
            
            $stmt->closeCursor();
            return $ranking;
        } catch (PDOException $e) {
            error_log("Error en obtenerRankingProgreso: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener ranking por tiempo de estudio
     * @param int $limit
     * @return array
     */
    public function obtenerRankingTiempoEstudio($limit = 10) 
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.id, u.name, u.email, u.career,
                    SUM(sp.total_time_spent) as tiempo_total,
                    COUNT(DISTINCT sp.subject_area) as areas_estudiadas
                FROM users u
                INNER JOIN student_progress sp ON u.id = sp.user_id
                WHERE u.role_id = 3 AND u.active = 1
                GROUP BY u.id, u.name, u.email, u.career
                ORDER BY tiempo_total DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            $ranking = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ranking[] = $row;
            }
            
            $stmt->closeCursor();
            return $ranking;
        } catch (PDOException $e) {
            error_log("Error en obtenerRankingTiempoEstudio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Comparar progreso del usuario con promedio de su carrera
     * @param int $userId
     * @return array|null
     */
    public function compararConCarrera($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    u.career,
                    (SELECT AVG(sp.progress_percentage) 
                     FROM student_progress sp 
                     WHERE sp.user_id = ?) as mi_progreso,
                    AVG(sp_all.progress_percentage) as promedio_carrera,
                    (SELECT AVG(sp.average_score) 
                     FROM student_progress sp 
                     WHERE sp.user_id = ?) as mi_puntaje,
                    AVG(sp_all.average_score) as puntaje_carrera
                FROM users u
                INNER JOIN student_progress sp_all ON sp_all.user_id = u.id
                WHERE u.career = (SELECT career FROM users WHERE id = ?)
                AND u.role_id = 3
                GROUP BY u.career
            ");
            $stmt->execute([$userId, $userId, $userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en compararConCarrera: " . $e->getMessage());
            return null;
        }
    }

    // ==========================================
    // UTILIDADES
    // ==========================================

    /**
     * Obtener última actividad del estudiante
     * @param int $userId
     * @return string|null Fecha de última actividad
     */
    public function obtenerUltimaActividad($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT MAX(last_activity) as ultima_actividad
                FROM student_progress
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result['ultima_actividad'] ?? null;
        } catch (PDOException $e) {
            error_log("Error en obtenerUltimaActividad: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si el estudiante está activo (actividad reciente)
     * @param int $userId
     * @param int $diasInactividad Días de inactividad permitidos
     * @return bool
     */
    public function estudianteActivo($userId, $diasInactividad = 7) 
    {
        $ultimaActividad = $this->obtenerUltimaActividad($userId);
        
        if (!$ultimaActividad) {
            return false;
        }
        
        $fecha = new \DateTime($ultimaActividad);
        $ahora = new \DateTime();
        $diferencia = $ahora->diff($fecha);
        
        return $diferencia->days <= $diasInactividad;
    }

    /**
     * Obtener estadísticas consolidadas del estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerEstadisticasConsolidadas($userId) 
    {
        return [
            'resumen' => $this->obtenerResumenProgreso($userId),
            'mejores_areas' => $this->obtenerMejoresAreas($userId, 3),
            'areas_atencion' => $this->obtenerAreasAtencion($userId, 3),
            'comparativa_carrera' => $this->compararConCarrera($userId),
            'ultima_actividad' => $this->obtenerUltimaActividad($userId),
            'estudiante_activo' => $this->estudianteActivo($userId)
        ];
    }
}