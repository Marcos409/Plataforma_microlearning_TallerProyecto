<?php
// app/DataAccessModels/AnalisisModel.php

namespace App\DataAccessModels;

class AnalisisModel extends BaseModel 
{

    
    // ==========================================
    // MÉTODOS NUEVOS PARA ML ANALYSIS
    // ==========================================
    
    /**
     * Analizar rendimiento del estudiante
     * @param int $userId
     * @return array|null
     */
    public function analizarRendimiento($userId) 
    {
        return $this->callProcedureSingle('sp_analizar_rendimiento', [$userId]);
    }
    
    /**
     * Predecir riesgo académico
     * @param int $userId
     * @return array|null
     */
    public function predecirRiesgo($userId) 
    {
        return $this->callProcedureSingle('sp_predecir_riesgo', [$userId]);
    }
    
    // ==========================================
    // CRUD PARA ML_ANALYSIS - VÍA STORED PROCEDURES
    // ==========================================
    
    /**
     * Crear un nuevo análisis ML
     * @param array $data
     * @return int|bool ID del análisis creado o false
     */
    public function crearAnalisis($data)
    {
        $result = $this->callProcedureSingle('sp_crear_analisis', [
            $data['user_id'],
            $data['diagnostico'] ?? null,
            $data['ruta_aprendizaje'] ?? null,
            $data['nivel_riesgo'] ?? null,
            json_encode($data['metricas'] ?? []),
            json_encode($data['recomendaciones'] ?? [])
        ]);
        
        return $result ? (int) $result['id'] : false;
    }
    
    /**
     * Obtener análisis por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerAnalisis($id)
    {
        $result = $this->callProcedureSingle('sp_obtener_analisis_ml', [$id]);
        
        if ($result) {
            // Decodificar JSON
            $result['metricas'] = json_decode($result['metricas'], true) ?? [];
            $result['recomendaciones'] = json_decode($result['recomendaciones'], true) ?? [];
        }
        
        return $result;
    }
    
    /**
     * Listar análisis con paginación
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function listarAnalisis($page = 1, $perPage = 20)
    {
        return $this->callProcedureMultiple('sp_listar_analisis_ml', [$page, $perPage]);
    }
    
    /**
     * Contar total de análisis
     * @return int
     */
    public function contarAnalisis()
    {
        $result = $this->callProcedureSingle('sp_contar_analisis_ml');
        return $result ? (int) $result['total'] : 0;
    }
    
    /**
     * Obtener análisis más reciente de un usuario
     * @param int $userId
     * @return array|null
     */
    public function obtenerAnalisisReciente($userId)
    {
        $result = $this->callProcedureSingle('sp_obtener_analisis_reciente_ml', [$userId]);
        
        if ($result) {
            $result['metricas'] = json_decode($result['metricas'], true) ?? [];
            $result['recomendaciones'] = json_decode($result['recomendaciones'], true) ?? [];
        }
        
        return $result;
    }
    
    /**
     * Obtener historial de análisis de un usuario
     * @param int $userId
     * @return array
     */
    public function obtenerHistorialUsuario($userId)
    {
        return $this->callProcedureMultiple('sp_obtener_historial_usuario_ml', [$userId]);
    }
    
    /**
     * Obtener estadísticas de análisis
     * @return array
     */
    public function obtenerEstadisticas()
    {
        $result = $this->callProcedureSingle('sp_obtener_estadisticas_ml');
        
        return $result ?: [
            'total' => 0,
            'diagnostico_basico' => 0,
            'diagnostico_intermedio' => 0,
            'diagnostico_avanzado' => 0,
            'riesgo_alto' => 0,
            'riesgo_medio' => 0,
            'riesgo_bajo' => 0
        ];
    }
    
    /**
     * Contar análisis por nivel de diagnóstico
     * @param string $nivel
     * @return int
     */
    public function contarPorDiagnostico($nivel)
    {
        $result = $this->callProcedureSingle('sp_contar_por_diagnostico_ml', [$nivel]);
        return $result ? (int) $result['total'] : 0;
    }
    
    /**
     * Contar análisis por nivel de riesgo
     * @param string $nivel
     * @return int
     */
    public function contarPorRiesgo($nivel)
    {
        $result = $this->callProcedureSingle('sp_contar_por_riesgo_ml', [$nivel]);
        return $result ? (int) $result['total'] : 0;
    }
    
    /**
     * Actualizar un análisis existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizarAnalisis($id, $data)
    {
        $result = $this->callProcedureSingle('sp_actualizar_analisis_ml', [
            $id,
            $data['diagnostico'] ?? null,
            $data['ruta_aprendizaje'] ?? null,
            $data['nivel_riesgo'] ?? null,
            json_encode($data['metricas'] ?? []),
            json_encode($data['recomendaciones'] ?? [])
        ]);
        
        return $result && (int) $result['affected_rows'] > 0;
    }
    
    /**
     * Eliminar análisis antiguos (más de X días)
     * @param int $days
     * @return int Número de registros eliminados
     */
    public function eliminarAntiguos($days = 90)
    {
        $result = $this->callProcedureSingle('sp_eliminar_antiguos_ml', [$days]);
        return $result ? (int) $result['deleted_rows'] : 0;
    }
    
    /**
     * Obtener estudiantes sin análisis reciente (últimos 30 días)
     * @return array
     */
    public function obtenerEstudiantesSinAnalisis()
    {
        return $this->callProcedureMultiple('sp_obtener_estudiantes_sin_analisis_ml');
    }
    
    /**
     * Buscar análisis por criterios múltiples
     * @param array $filters
     * @return array
     */
    public function buscarAnalisis($filters = [])
    {
        return $this->callProcedureMultiple('sp_buscar_analisis_ml', [
            $filters['user_id'] ?? null,
            $filters['diagnostico'] ?? null,
            $filters['nivel_riesgo'] ?? null,
            $filters['fecha_desde'] ?? null,
            $filters['fecha_hasta'] ?? null
        ]);
    }
    
    /**
     * Eliminar un análisis específico
     * @param int $id
     * @return bool
     */
    public function eliminarAnalisis($id)
    {
        $result = $this->callProcedureSingle('sp_eliminar_analisis_ml', [$id]);
        return $result && (int) $result['deleted_rows'] > 0;
    }
    
    /**
     * Obtener análisis por rango de fechas
     * @param string $fechaInicio (formato: Y-m-d)
     * @param string $fechaFin (formato: Y-m-d)
     * @return array
     */
    public function obtenerAnalisisPorFechas($fechaInicio, $fechaFin)
    {
        return $this->callProcedureMultiple('sp_obtener_analisis_por_fechas_ml', [
            $fechaInicio,
            $fechaFin
        ]);
    }
    
    /**
     * Obtener todos los análisis con alto riesgo
     * @return array
     */
    public function obtenerAnalisisAltoRiesgo()
    {
        return $this->callProcedureMultiple('sp_obtener_analisis_alto_riesgo_ml');
    }


    // ==========================================
    // MÉTODO PARA REPORTES DE GRUPO
    // ==========================================
    
    /**
     * Obtiene el conteo total y el promedio de score para un grupo.
     * @param string $career La carrera seleccionada.
     * @param int $semester El semestre seleccionado.
     * @return array
     */
    public function getGroupStatsForReport($career, $semester)
    {
        // 1. CONSULTA SQL ÚNICA para obtener el CONTEO y el PROMEDIO
        $statsQuery = "
            SELECT
                COUNT(u.id) AS total_students_count, 
                IFNULL(AVG(sp.average_score), 0) AS avg_score_value, // Calcula el promedio
                IFNULL(AVG(sp.progress_percentage), 0) AS avg_progress_value,
                COUNT(CASE WHEN sp.average_score < 60 THEN 1 END) AS risk_students_count
            FROM users u
            LEFT JOIN student_progress sp ON u.id = sp.user_id
            WHERE u.career = ? AND u.semester = ? AND u.role_id = 3
        ";
        
        $stats = DB::selectOne($statsQuery, [$career, $semester]); 
        
        // Si no se encuentra nada, asegura que las variables sean cero
        if (!$stats) {
            $stats = (object)['total_students_count' => 0, 'avg_score_value' => 0, 'avg_progress_value' => 0, 'risk_students_count' => 0];
        }
        
        // 2. ESTRUCTURA FINAL: Mapeo de resultados a las claves de tu vista
        return [
            'group_name' => "{$career} - Semestre {$semester}",
            
            // ✅ Usa la clave 'total_estudiantes' para el conteo:
            'total_estudiantes' => (int) $stats->total_students_count, 
            
            // ✅ Usa la clave 'promedio_grupo' para el porcentaje:
            'promedio_grupo' => (float) round($stats->avg_score_value, 1), 
            
            // Añade el resto de claves necesarias para completar el PDF
            'avg_progress' => (float) round($stats->avg_progress_value, 1),
            'risk_students' => (int) $stats->risk_students_count,
            'mejores_estudiantes' => [], // Aquí iría la consulta para top 3
            'estudiantes_riesgo' => [], // Aquí iría la consulta para estudiantes en riesgo
        ];
    }
}