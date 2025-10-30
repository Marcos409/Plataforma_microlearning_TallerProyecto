<?php
// app/DataAccessModels/ProgresoModel.php

namespace App\DataAccessModels;

class ProgresoModel extends BaseModel 
{
    // De StudentProgress.php
    public function obtenerProgresoEstudiante($userId) 
    {
        return $this->callProcedureMultiple('sp_obtener_progreso_estudiante', [$userId]);
    }

    public function actualizarProgreso($userId, $subjectArea, $completedActivities, $score, $timeSpent) 
    {
        return $this->callProcedureNoReturn('sp_actualizar_progreso', 
            [$userId, $subjectArea, $completedActivities, $score, $timeSpent]
        );
    }

    // De StudentProgress.php + Report.php
    public function dashboardEstudiante($userId) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_dashboard_estudiante(?)");
            $stmt->execute([$userId]);
            
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $progreso = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $rutasActivas = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $recomendaciones = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return [
                'usuario' => $usuario,
                'progreso' => $progreso,
                'rutas_activas' => $rutasActivas,
                'recomendaciones' => $recomendaciones
            ];
        } catch (\PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }

    // De Report.php
    public function dashboardDocente() 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_dashboard_docente()");
            $stmt->execute();
            
            $totalStudents = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $goodProgress = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $atRisk = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $diagnosticsToday = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return [
                'total_students' => $totalStudents,
                'good_progress' => $goodProgress,
                'at_risk' => $atRisk,
                'diagnostics_today' => $diagnosticsToday
            ];
        } catch (\PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }

    public function estudiantesPorCarrera() 
    {
        return $this->callProcedureMultiple('sp_estudiantes_por_carrera', []);
    }

    // Agregar al final de ProgresoModel.php

/**
 * Obtener total de actividades completadas (todos los usuarios)
 */
public function obtenerTotalActividadesCompletadas() 
{
    try {
        $stmt = $this->pdo->query("
            SELECT COALESCE(SUM(completed_activities), 0) as total
            FROM student_progress
        ");
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        return $result['total'] ?? 0;
    } catch (\PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return 0;
    }
}
}