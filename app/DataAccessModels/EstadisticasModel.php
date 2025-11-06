<?php
// app/DataAccessModels/EstadisticasModel.php

namespace App\DataAccessModels;

use PDO;
use PDOException;

class EstadisticasModel extends BaseModel 
{
    // ==========================================
    // ESTADÍSTICAS DE ESTUDIANTES
    // ==========================================
    
    /**
     * Obtener estadísticas completas del estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerEstadisticasEstudiante($userId) 
    {
        // Validación de entrada
        if (!is_numeric($userId) || $userId <= 0) {
            return $this->getEstadisticasVacias();
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_estadisticas_estudiante(?)");
            $stmt->execute([$userId]);
            
            // Primer resultado: Progreso general
            $progresoGeneral = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return $this->getEstadisticasVacias();
            }
            
            // Segundo resultado: Diagnósticos
            $diagnosticos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return $this->getEstadisticasVacias();
            }
            
            // Tercer resultado: Rutas de aprendizaje
            $rutas = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stmt->nextRowset()) {
                $stmt->closeCursor();
                return $this->getEstadisticasVacias();
            }
            
            // Cuarto resultado: Recomendaciones
            $recomendaciones = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt->closeCursor();
            
            return [
                'progreso_general' => $progresoGeneral ?: $this->getDefaultProgresoGeneral(),
                'diagnosticos' => $diagnosticos ?: $this->getDefaultDiagnosticos(),
                'rutas' => $rutas ?: $this->getDefaultRutas(),
                'recomendaciones' => $recomendaciones ?: $this->getDefaultRecomendaciones()
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasEstudiante: " . $e->getMessage());
            return $this->getEstadisticasVacias();
        }
    }
    
    /**
     * Obtener estadísticas de rutas del estudiante
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
     * Calcular nivel de riesgo del estudiante
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
     * Verificar si hay alertas críticas
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
    
    // ==========================================
    // ESTADÍSTICAS DE SISTEMA/ADMINISTRACIÓN
    // ==========================================
    
    /**
     * Obtener estadísticas generales del sistema
     * @return array
     */
    public function obtenerEstadisticasSistema()
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_estadisticas_sistema()");
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: [
                'total_users' => 0,
                'total_students' => 0,
                'total_teachers' => 0,
                'total_contents' => 0,
                'total_diagnostics' => 0,
                'total_learning_paths' => 0
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasSistema: " . $e->getMessage());
            return [
                'total_users' => 0,
                'total_students' => 0,
                'total_teachers' => 0,
                'total_contents' => 0,
                'total_diagnostics' => 0,
                'total_learning_paths' => 0
            ];
        }
    }
    
    /**
     * Obtener estudiantes por carrera
     * @return array
     */
    public function obtenerEstudiantesPorCarrera()
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
            error_log("Error en obtenerEstudiantesPorCarrera: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // ESTADÍSTICAS DE CONTENIDO
    // ==========================================
    
    /**
     * Obtener estadísticas de contenidos
     * @return array
     */
    public function obtenerEstadisticasContenidos()
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_estadisticas_contenidos()");
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: [
                'total_contenidos' => 0,
                'contenidos_activos' => 0,
                'total_vistas' => 0,
                'duracion_promedio' => 0,
                'total_areas' => 0,
                'total_tipos' => 0
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasContenidos: " . $e->getMessage());
            return [
                'total_contenidos' => 0,
                'contenidos_activos' => 0,
                'total_vistas' => 0,
                'duracion_promedio' => 0,
                'total_areas' => 0,
                'total_tipos' => 0
            ];
        }
    }
    
    /**
     * Contar contenidos por tipo
     * @return array
     */
    public function contarContenidosPorTipo()
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_contar_contenidos_por_tipo()");
            $stmt->execute();
            
            $contenidos = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $contenidos[] = $row;
            }
            
            $stmt->closeCursor();
            return $contenidos;
        } catch (PDOException $e) {
            error_log("Error en contarContenidosPorTipo: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // ESTADÍSTICAS DE DIAGNÓSTICOS
    // ==========================================
    
    /**
     * Obtener rendimiento por materia
     * @return array
     */
    public function obtenerRendimientoPorMateria()
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_rendimiento_por_materia()");
            $stmt->execute();
            
            $rendimiento = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rendimiento[] = $row;
            }
            
            $stmt->closeCursor();
            return $rendimiento;
        } catch (PDOException $e) {
            error_log("Error en obtenerRendimientoPorMateria: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener rendimiento por mes
     * @return array
     */
    public function obtenerRendimientoPorMes()
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_rendimiento_por_mes()");
            $stmt->execute();
            
            $rendimiento = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rendimiento[] = $row;
            }
            
            $stmt->closeCursor();
            return $rendimiento;
        } catch (PDOException $e) {
            error_log("Error en obtenerRendimientoPorMes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar diagnósticos completados
     * @param int $userId
     * @return int
     */
    public function contarDiagnosticosCompletados($userId)
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return 0;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_contar_diagnosticos_completados(?)");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error en contarDiagnosticosCompletados: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener estadísticas de un diagnóstico específico
     * @param int $diagnosticId
     * @return array
     */
    public function obtenerEstadisticasDiagnostico($diagnosticId)
    {
        if (!is_numeric($diagnosticId) || $diagnosticId <= 0) {
            return [
                'total_intentos' => 0,
                'total_aprobados' => 0,
                'promedio_puntaje' => 0,
                'puntaje_minimo' => 0,
                'puntaje_maximo' => 0,
                'tiempo_promedio' => 0,
                'porcentaje_aprobacion' => 0
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_estadisticas_diagnostico(?)");
            $stmt->execute([$diagnosticId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: [
                'total_intentos' => 0,
                'total_aprobados' => 0,
                'promedio_puntaje' => 0,
                'puntaje_minimo' => 0,
                'puntaje_maximo' => 0,
                'tiempo_promedio' => 0,
                'porcentaje_aprobacion' => 0
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasDiagnostico: " . $e->getMessage());
            return [
                'total_intentos' => 0,
                'total_aprobados' => 0,
                'promedio_puntaje' => 0,
                'puntaje_minimo' => 0,
                'puntaje_maximo' => 0,
                'tiempo_promedio' => 0,
                'porcentaje_aprobacion' => 0
            ];
        }
    }
    
    /**
     * Obtener top estudiantes en un diagnóstico
     * @param int $diagnosticId
     * @param int $limit
     * @return array
     */
    public function obtenerTopEstudiantes($diagnosticId, $limit = 10)
    {
        if (!is_numeric($diagnosticId) || $diagnosticId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_top_estudiantes_diagnostico(?, ?)");
            $stmt->execute([$diagnosticId, $limit]);
            
            $estudiantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estudiantes[] = $row;
            }
            
            $stmt->closeCursor();
            return $estudiantes;
        } catch (PDOException $e) {
            error_log("Error en obtenerTopEstudiantes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener preguntas más difíciles de un diagnóstico
     * @param int $diagnosticId
     * @param int $limit
     * @return array
     */
    public function obtenerPreguntasMasDificiles($diagnosticId, $limit = 5)
    {
        if (!is_numeric($diagnosticId) || $diagnosticId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_preguntas_mas_dificiles(?, ?)");
            $stmt->execute([$diagnosticId, $limit]);
            
            $preguntas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $preguntas[] = $row;
            }
            
            $stmt->closeCursor();
            return $preguntas;
        } catch (PDOException $e) {
            error_log("Error en obtenerPreguntasMasDificiles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener historial de diagnósticos de un usuario
     * @param int $userId
     * @return array
     */
    public function obtenerHistorialDiagnosticos($userId)
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_historial_diagnosticos_usuario(?)");
            $stmt->execute([$userId]);
            
            $historial = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $historial[] = $row;
            }
            
            $stmt->closeCursor();
            return $historial;
        } catch (PDOException $e) {
            error_log("Error en obtenerHistorialDiagnosticos: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // COMPARATIVAS Y ANÁLISIS
    // ==========================================
    
    /**
     * Comparar rendimiento de un usuario con el promedio
     * @param int $userId
     * @param int $diagnosticId
     * @return array
     */
    public function compararRendimientoUsuario($userId, $diagnosticId)
    {
        if (!is_numeric($userId) || $userId <= 0 || !is_numeric($diagnosticId) || $diagnosticId <= 0) {
            return [
                'puntaje_usuario' => 0,
                'puntaje_promedio' => 0,
                'tiempo_usuario' => 0,
                'tiempo_promedio' => 0,
                'comparacion_rendimiento' => 'sin_datos'
            ];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_comparar_rendimiento_usuario(?, ?)");
            $stmt->execute([$userId, $diagnosticId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: [
                'puntaje_usuario' => 0,
                'puntaje_promedio' => 0,
                'tiempo_usuario' => 0,
                'tiempo_promedio' => 0,
                'comparacion_rendimiento' => 'sin_datos'
            ];
        } catch (PDOException $e) {
            error_log("Error en compararRendimientoUsuario: " . $e->getMessage());
            return [
                'puntaje_usuario' => 0,
                'puntaje_promedio' => 0,
                'tiempo_usuario' => 0,
                'tiempo_promedio' => 0,
                'comparacion_rendimiento' => 'sin_datos'
            ];
        }
    }
    
    /**
     * Obtener áreas con bajo rendimiento de un usuario
     * @param int $userId
     * @return array
     */
    public function obtenerAreasBajoRendimiento($userId)
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_areas_bajo_rendimiento_usuario(?)");
            $stmt->execute([$userId]);
            
            $areas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $areas[] = $row;
            }
            
            $stmt->closeCursor();
            return $areas;
        } catch (PDOException $e) {
            error_log("Error en obtenerAreasBajoRendimiento: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // RESÚMENES Y REPORTES
    // ==========================================
    
    /**
     * Obtener resumen completo del sistema
     * @return array
     */
    public function obtenerResumenSistema()
    {
        return [
            'sistema' => $this->obtenerEstadisticasSistema(),
            'contenidos' => $this->obtenerEstadisticasContenidos(),
            'contenidos_por_tipo' => $this->contarContenidosPorTipo(),
            'estudiantes_por_carrera' => $this->obtenerEstudiantesPorCarrera(),
            'rendimiento_por_materia' => $this->obtenerRendimientoPorMateria()
        ];
    }
    
    /**
     * Obtener perfil completo del estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerPerfilEstudiante($userId)
    {
        return [
            'estadisticas' => $this->obtenerEstadisticasEstudiante($userId),
            'riesgo' => $this->calcularNivelRiesgo($userId),
            'alertas' => $this->obtenerAlertasRiesgo($userId),
            'areas_bajo_rendimiento' => $this->obtenerAreasBajoRendimiento($userId),
            'rutas' => $this->obtenerEstadisticasRutas($userId),
            'historial_diagnosticos' => $this->obtenerHistorialDiagnosticos($userId)
        ];
    }
    
    /**
     * Obtener reporte de diagnóstico completo
     * @param int $diagnosticId
     * @return array
     */
    public function obtenerReporteDiagnostico($diagnosticId)
    {
        return [
            'estadisticas' => $this->obtenerEstadisticasDiagnostico($diagnosticId),
            'top_estudiantes' => $this->obtenerTopEstudiantes($diagnosticId, 10),
            'preguntas_dificiles' => $this->obtenerPreguntasMasDificiles($diagnosticId, 5)
        ];
    }
    
    /**
     * Exportar estadísticas completas para reporte
     * @param int $userId
     * @return array
     */
    public function exportarEstadisticasCompletas($userId)
    {
        return [
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'usuario_id' => $userId,
            'perfil' => $this->obtenerPerfilEstudiante($userId),
            'tendencia' => $this->obtenerRendimientoPorMes(),
            'alertas_criticas' => $this->tieneAlertasCriticas($userId),
            'diagnosticos_completados' => $this->contarDiagnosticosCompletados($userId)
        ];
    }
    
    // ==========================================
    // MÉTODOS DE UTILIDAD Y PROCESAMIENTO
    // ==========================================
    
    /**
     * Calcular porcentaje de mejora entre dos períodos
     * @param array $estadisticasAntes
     * @param array $estadisticasDespues
     * @param string $metrica
     * @return float
     */
    public function calcularPorcentajeMejora($estadisticasAntes, $estadisticasDespues, $metrica)
    {
        $valorAntes = $estadisticasAntes[$metrica] ?? 0;
        $valorDespues = $estadisticasDespues[$metrica] ?? 0;
        
        if ($valorAntes == 0) {
            return $valorDespues > 0 ? 100 : 0;
        }
        
        return (($valorDespues - $valorAntes) / $valorAntes) * 100;
    }
    
    /**
     * Determinar tendencia (mejorando, estable, declinando)
     * @param array $datos Array con valores históricos
     * @return string
     */
    public function determinarTendencia($datos)
    {
        if (count($datos) < 2) {
            return 'insuficientes_datos';
        }
        
        $suma = 0;
        $count = count($datos) - 1;
        
        for ($i = 1; $i < count($datos); $i++) {
            $diff = $datos[$i] - $datos[$i - 1];
            $suma += $diff;
        }
        
        $promedioCambio = $suma / $count;
        
        if ($promedioCambio > 5) {
            return 'mejorando';
        } elseif ($promedioCambio < -5) {
            return 'declinando';
        } else {
            return 'estable';
        }
    }
    
    /**
     * Generar recomendaciones basadas en estadísticas
     * @param array $estadisticas
     * @return array
     */
    public function generarRecomendaciones($estadisticas)
    {
        $recomendaciones = [];
        
        $progreso = $estadisticas['progreso_general'] ?? [];
        
        // Verificar progreso bajo
        if (isset($progreso['avg_progress']) && $progreso['avg_progress'] < 30) {
            $recomendaciones[] = [
                'tipo' => 'progreso_bajo',
                'mensaje' => 'El progreso general es bajo. Considera dedicar más tiempo al estudio.',
                'prioridad' => 'alta'
            ];
        }
        
        // Verificar tiempo de estudio
        if (isset($progreso['total_study_time']) && $progreso['total_study_time'] < 180) {
            $recomendaciones[] = [
                'tipo' => 'tiempo_estudio',
                'mensaje' => 'El tiempo de estudio es reducido. Intenta estudiar al menos 3 horas por semana.',
                'prioridad' => 'media'
            ];
        }
        
        // Verificar diagnósticos
        $diagnosticos = $estadisticas['diagnosticos'] ?? [];
        if (isset($diagnosticos['avg_diagnostic_score']) && $diagnosticos['avg_diagnostic_score'] < 60) {
            $recomendaciones[] = [
                'tipo' => 'diagnosticos_bajos',
                'mensaje' => 'Tu rendimiento en diagnósticos es bajo. Revisa los temas con mayor dificultad.',
                'prioridad' => 'alta'
            ];
        }
        
        // Verificar rutas incompletas
        $rutas = $estadisticas['rutas'] ?? [];
        if (isset($rutas['active_paths']) && $rutas['active_paths'] > 3) {
            $recomendaciones[] = [
                'tipo' => 'rutas_multiples',
                'mensaje' => 'Tienes varias rutas activas. Enfócate en completar una a la vez.',
                'prioridad' => 'media'
            ];
        }
        
        return $recomendaciones;
    }
    
    /**
     * Verificar si el estudiante está en riesgo
     * @param int $userId
     * @return bool
     */
    public function estudianteEnRiesgo($userId)
    {
        $riesgo = $this->calcularNivelRiesgo($userId);
        return in_array($riesgo['risk_level'], ['alto', 'medio']);
    }
    
    // ==========================================
    // MÉTODOS PRIVADOS - VALORES POR DEFECTO
    // ==========================================
    
    /**
     * Obtener estadísticas vacías por defecto
     * @return array
     */
    private function getEstadisticasVacias()
    {
        return [
            'progreso_general' => $this->getDefaultProgresoGeneral(),
            'diagnosticos' => $this->getDefaultDiagnosticos(),
            'rutas' => $this->getDefaultRutas(),
            'recomendaciones' => $this->getDefaultRecomendaciones()
        ];
    }
    
    /**
     * Obtener valores por defecto para progreso general
     * @return array
     */
    private function getDefaultProgresoGeneral()
    {
        return [
            'total_subjects' => 0,
            'avg_progress' => 0.00,
            'total_completed_activities' => 0,
            'total_study_time' => 0,
            'last_activity' => null
        ];
    }
    
    /**
     * Obtener valores por defecto para diagnósticos
     * @return array
     */
    private function getDefaultDiagnosticos()
    {
        return [
            'total_diagnostics' => 0,
            'avg_diagnostic_score' => 0.00,
            'passed_diagnostics' => 0
        ];
    }
    
    /**
     * Obtener valores por defecto para rutas
     * @return array
     */
    private function getDefaultRutas()
    {
        return [
            'total_paths' => 0,
            'completed_paths' => 0,
            'active_paths' => 0
        ];
    }
    
    /**
     * Obtener valores por defecto para recomendaciones
     * @return array
     */
    private function getDefaultRecomendaciones()
    {
        return [
            'total_recommendations' => 0,
            'viewed_recommendations' => 0,
            'completed_recommendations' => 0
        ];
    }
}