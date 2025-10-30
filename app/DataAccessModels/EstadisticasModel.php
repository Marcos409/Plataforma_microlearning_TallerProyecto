<?php
// app/DataAccessModels/EstadisticasModel.php

namespace App\DataAccessModels;

use \PDO;

class EstadisticasModel extends BaseModel 
{
    /**
     * Obtener estadísticas completas del estudiante
     */
    public function obtenerEstadisticasEstudiante($userId) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_estadisticas_estudiante(?)");
            $stmt->execute([$userId]);
            
            // Primer resultado: Progreso general
            $progresoGeneral = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $stmt->nextRowset();
            
            // Segundo resultado: Diagnósticos
            $diagnosticos = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $stmt->nextRowset();
            
            // Tercer resultado: Rutas de aprendizaje
            $rutas = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $stmt->nextRowset();
            
            // Cuarto resultado: Recomendaciones
            $recomendaciones = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return [
                'progreso_general' => $progresoGeneral ?? [],
                'diagnosticos' => $diagnosticos ?? [],
                'rutas' => $rutas ?? [],
                'recomendaciones' => $recomendaciones ?? []
            ];
        } catch (\PDOException $e) {
            error_log("Error en obtenerEstadisticasEstudiante: " . $e->getMessage());
            return [
                'progreso_general' => [],
                'diagnosticos' => [],
                'rutas' => [],
                'recomendaciones' => []
            ];
        }
    }
}