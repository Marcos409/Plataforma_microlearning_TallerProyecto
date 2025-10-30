<?php
// app/DataAccessModels/LearningPathModel.php

namespace App\DataAccessModels;

use \PDO;

class LearningPathModel extends BaseModel 
{
    /**
     * Obtener rutas de aprendizaje del usuario
     */
    public function obtenerRutasUsuario($userId) 
    {
        return $this->callProcedureMultiple('sp_obtener_rutas_usuario', [$userId]);
    }

    /**
     * Obtener contenidos de una ruta
     */
    public function obtenerContenidosRuta($learningPathId) 
    {
        return $this->callProcedureMultiple('sp_obtener_contenidos_ruta', [$learningPathId]);
    }

    /**
     * Obtener rutas de aprendizaje con contenidos
     */
    public function obtenerRutasConContenidos($userId) 
    {
        try {
            // Obtener rutas usando SP
            $rutas = $this->obtenerRutasUsuario($userId);
            
            // Para cada ruta, obtener sus contenidos usando SP
            foreach ($rutas as &$ruta) {
                $contenidos = $this->obtenerContenidosRuta($ruta['id']);
                $ruta['contents'] = $contenidos;
            }
            
            return $rutas;
        } catch (\Exception $e) {
            error_log("Error en obtenerRutasConContenidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de rutas del estudiante
     */
    public function obtenerEstadisticasRutas($userId) 
    {
        // Necesitas crear este SP: sp_estadisticas_rutas_estudiante
        $result = $this->callProcedureSingle('sp_estadisticas_rutas_estudiante', [$userId]);
        
        return $result ?? [
            'total_paths' => 0,
            'completed_paths' => 0,
            'avg_progress' => 0
        ];
    }

    /**
     * Completar contenido de ruta
     */
    public function completarContenidoRuta($learningPathContentId, $timeSpent) 
    {
        return $this->callProcedureNoReturn('sp_completar_contenido_ruta', 
            [$learningPathContentId, $timeSpent]
        );
    }
}