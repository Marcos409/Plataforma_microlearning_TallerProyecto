<?php
// app/DataAccessModels/DiagnosticoModel.php
namespace App\DataAccessModels;

class DiagnosticoModel extends BaseModel 
{
    // ==========================================
    // GESTIÓN DE DIAGNÓSTICOS
    // ==========================================
    
    /**
     * Listar diagnósticos activos
     * @return array
     */
    public function listarDiagnosticos() 
    {
        return $this->callProcedureMultiple('sp_listar_diagnosticos', []);
    }
    
    /**
     * Obtener diagnóstico por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerDiagnostico($id) 
    {
        return $this->callProcedureSingle('sp_obtener_diagnostico', [$id]);
    }
    
        /**
     * Obtener diagnóstico completo con preguntas
     * @param int $diagnosticId
     * @return array|null ['diagnostico' => array, 'preguntas' => array]
     */
    public function obtenerDiagnosticoCompleto($diagnosticId) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_diagnostico_completo(?)");
            $stmt->execute([$diagnosticId]);
            
            // Primer resultado: datos del diagnóstico
            $diagnostico = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Segundo resultado: preguntas
            $stmt->nextRowset();
            $preguntas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $stmt->closeCursor();
            
            if (!$diagnostico) {
                return null;
            }
            
            // Parsear fechas
            if (isset($diagnostico['created_at'])) {
                $diagnostico['created_at'] = \Carbon\Carbon::parse($diagnostico['created_at']);
            }
            if (isset($diagnostico['updated_at'])) {
                $diagnostico['updated_at'] = \Carbon\Carbon::parse($diagnostico['updated_at']);
            }
            
            // ✅ SOLUCIÓN: Envolver diagnóstico en array para consistencia
            return [
                'diagnostico' => [$diagnostico], // ← Aquí está el cambio
                'preguntas' => $preguntas
            ];
        } catch (\PDOException $e) {
            error_log("Error en obtenerDiagnosticoCompleto: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear diagnóstico
     * @param array $data
     * @return int|null ID del diagnóstico creado
     */
    public function crearDiagnostico($data) 
    {
        $result = $this->callProcedureSingle('sp_crear_diagnostico', [
            $data['title'],
            $data['description'] ?? null,
            $data['subject_area'],
            $data['difficulty_level'],
            $data['time_limit_minutes'] ?? null,
            $data['passing_score'] ?? 70.00
        ]);
        
        return $result ? (int) $result['id'] : null;
    }
    
    /**
     * Actualizar diagnóstico
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizarDiagnostico($id, $data) 
    {
        $result = $this->callProcedureSingle('sp_actualizar_diagnostico', [
            $id,
            $data['title'],
            $data['description'] ?? null,
            $data['subject_area'],
            $data['difficulty_level'],
            $data['time_limit_minutes'] ?? null,
            $data['passing_score'] ?? 70.00,
            $data['active'] ?? 1
        ]);
        
        // ✅ CORRECCIÓN: Verificar si $result es un array antes de acceder
        if (is_string($result) || !is_array($result)) {
            return !empty($result); // Si es string, verificar que no esté vacío
        }
        return $result && isset($result['affected_rows']) && (int) $result['affected_rows'] > 0;
    }
    
    /**
     * Eliminar diagnóstico
     * @param int $id
     * @return bool
     */
    public function eliminarDiagnostico($id) 
    {
        $result = $this->callProcedureSingle('sp_eliminar_diagnostico', [$id]);
        return $result && isset($result['affected_rows']) && (int) $result['affected_rows'] > 0;
    }
    
    // ==========================================
    // GESTIÓN DE PREGUNTAS
    // ==========================================
    
    /**
     * Obtener pregunta por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerPregunta($id) 
    {
        return $this->callProcedureSingle('sp_obtener_pregunta', [$id]);
    }
    
    /**
     * Crear pregunta
     * @param array $data
     * @return int|null ID de la pregunta creada
     */
    public function crearPregunta($data) 
    {
        $result = $this->callProcedureSingle('sp_crear_pregunta', [
            $data['diagnostic_id'],
            $data['question_text'],
            $data['question_type'],
            $data['options'], // JSON string
            $data['correct_answer'],
            $data['points'] ?? 1.00
        ]);
        
        return $result ? (int) $result['id'] : null;
    }
    
    /**
     * Actualizar pregunta
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function actualizarPregunta($id, $data) 
    {
        $result = $this->callProcedureSingle('sp_actualizar_pregunta', [
            $id,
            $data['question_text'],
            $data['question_type'],
            $data['options'], // JSON string
            $data['correct_answer'],
            $data['points'] ?? 1.00
        ]);
        
        return $result && isset($result['affected_rows']) && (int) $result['affected_rows'] > 0;
    }
    
    /**
     * Eliminar pregunta
     * @param int $id
     * @return bool
     */
    public function eliminarPregunta($id) 
    {
        $result = $this->callProcedureSingle('sp_eliminar_pregunta', [$id]);
        return $result && isset($result['affected_rows']) && (int) $result['affected_rows'] > 0;
    }
    
    // ==========================================
    // RESPUESTAS Y RESULTADOS
    // ==========================================
    
    /**
     * Guardar respuesta de diagnóstico
     * @param int $userId
     * @param int $diagnosticId
     * @param int $questionId
     * @param string $userAnswer
     * @param int $timeSpent Tiempo en segundos
     * @return bool
     */
    public function guardarRespuesta($userId, $diagnosticId, $questionId, $userAnswer, $timeSpent) 
    {
        return $this->callProcedureNoReturn('sp_guardar_respuesta_diagnostico', 
            [$userId, $diagnosticId, $questionId, $userAnswer, $timeSpent]
        );
    }
    
    /**
     * Finalizar diagnóstico y calcular resultado
     * @param int $userId
     * @param int $diagnosticId
     * @param int $timeTaken Tiempo total en minutos
     * @return array|null
     */
    public function finalizarDiagnostico($userId, $diagnosticId, $timeTaken) 
    {
        return $this->callProcedureSingle('sp_finalizar_diagnostico', 
            [$userId, $diagnosticId, $timeTaken]
        );
    }
    
    /**
     * Obtener historial de diagnósticos de un usuario
     * @param int $userId
     * @return array
     */
    public function historialDiagnosticos($userId) 
    {
        return $this->callProcedureMultiple('sp_historial_diagnosticos_usuario', [$userId]);
        // **LÍNEA DE MODIFICACIÓN CRÍTICA**
        // Asegura que el resultado sea un array, si no lo es (ej. si es string/null), devuelve un array vacío.
        return is_array($result) ? $result : [];
    }
    
    // ==========================================
    // ESTADÍSTICAS Y REPORTES
    // ==========================================
    
    /**
     * Contar diagnósticos completados por un usuario
     * @param int $userId
     * @return int
     */
    public function contarDiagnosticosCompletados($userId) 
    {
        $result = $this->callProcedureSingle('sp_contar_diagnosticos_completados', [$userId]);
        return $result ? (int) $result['total'] : 0;
    }
    
    /**
     * Obtener rendimiento de un estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerRendimientoEstudiante($userId) 
    {
        $result = $this->callProcedureSingle('sp_obtener_rendimiento_estudiante', [$userId]);
        
        return $result ?: [
            'total_responses' => 0,
            'correct_responses' => 0,
            'percentage' => 0.00
        ];
    }
    
    /**
     * Obtener rendimiento por materia
     * @return array
     */
    public function obtenerRendimientoPorMateria() 
    {
        return $this->callProcedureMultiple('sp_rendimiento_por_materia', []);
    }
    
    /**
     * Obtener rendimiento por mes (últimos 6 meses)
     * @return array
     */
    public function obtenerRendimientoPorMes() 
    {
        return $this->callProcedureMultiple('sp_obtener_rendimiento_por_mes', []);
    }
    
    /**
     * Contar diagnósticos completados hoy
     * @return int
     */
    public function contarDiagnosticosHoy() 
    {
        $result = $this->callProcedureSingle('sp_contar_diagnosticos_hoy', []);
        return $result ? (int) $result['total'] : 0;
    }
    
    // ==========================================
    // MÉTODOS DE UTILIDAD
    // ==========================================
    
    /**
     * Validar datos de diagnóstico
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validarDatosDiagnostico($data)
    {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors[] = 'El título es requerido';
        }
        
        if (empty($data['subject_area'])) {
            $errors[] = 'El área de materia es requerida';
        }
        
        if (empty($data['difficulty_level'])) {
            $errors[] = 'El nivel de dificultad es requerido';
        }
        
        $nivelesPermitidos = ['Básico', 'Intermedio', 'Avanzado'];
        if (!empty($data['difficulty_level']) && !in_array($data['difficulty_level'], $nivelesPermitidos)) {
            $errors[] = 'Nivel de dificultad no válido';
        }
        
        if (isset($data['time_limit_minutes']) && (!is_numeric($data['time_limit_minutes']) || $data['time_limit_minutes'] <= 0)) {
            $errors[] = 'El tiempo límite debe ser un número positivo';
        }
        
        if (isset($data['passing_score']) && (!is_numeric($data['passing_score']) || $data['passing_score'] < 0 || $data['passing_score'] > 100)) {
            $errors[] = 'La nota de aprobación debe estar entre 0 y 100';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validar datos de pregunta
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validarDatosPregunta($data)
    {
        $errors = [];
        
        if (empty($data['diagnostic_id'])) {
            $errors[] = 'El ID del diagnóstico es requerido';
        }
        
        if (empty($data['question_text'])) {
            $errors[] = 'El texto de la pregunta es requerido';
        }
        
        if (empty($data['question_type'])) {
            $errors[] = 'El tipo de pregunta es requerido';
        }
        
        $tiposPermitidos = ['multiple_choice', 'true_false', 'open_ended'];
        if (!empty($data['question_type']) && !in_array($data['question_type'], $tiposPermitidos)) {
            $errors[] = 'Tipo de pregunta no válido';
        }
        
        if (empty($data['correct_answer'])) {
            $errors[] = 'La respuesta correcta es requerida';
        }
        
        if (isset($data['points']) && (!is_numeric($data['points']) || $data['points'] < 0)) {
            $errors[] = 'Los puntos deben ser un número positivo';
        }
        
        // Validar opciones para preguntas de selección múltiple
        if ($data['question_type'] === 'multiple_choice') {
            if (empty($data['options'])) {
                $errors[] = 'Las opciones son requeridas para preguntas de selección múltiple';
            } else {
                $options = is_string($data['options']) ? json_decode($data['options'], true) : $data['options'];
                if (!is_array($options) || count($options) < 2) {
                    $errors[] = 'Debe proporcionar al menos 2 opciones';
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Verificar si un diagnóstico existe
     * @param int $id
     * @return bool
     */
    public function existeDiagnostico($id)
    {
        $diagnostico = $this->obtenerDiagnostico($id);
        return $diagnostico !== null;
    }
    
    /**
     * Verificar si una pregunta existe
     * @param int $id
     * @return bool
     */
    public function existePregunta($id)
    {
        $pregunta = $this->obtenerPregunta($id);
        return $pregunta !== null;
    }
    
    /**
     * Obtener total de preguntas de un diagnóstico
     * @param int $diagnosticId
     * @return int
     */
    public function contarPreguntasDiagnostico($diagnosticId)
    {
        $diagnostico = $this->obtenerDiagnosticoCompleto($diagnosticId);
        return $diagnostico ? count($diagnostico['preguntas']) : 0;
    }
    
    /**
     * Verificar si un usuario ya completó un diagnóstico
     * @param int $userId
     * @param int $diagnosticId
     * @return bool
     */
    public function usuarioCompletoDiagnostico($userId, $diagnosticId)
    {
        $historial = $this->historialDiagnosticos($userId);
        
        foreach ($historial as $item) {
            if ((int) $item['diagnostic_id'] === $diagnosticId) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calcular porcentaje de aprobación de un diagnóstico
     * @param int $diagnosticId
     * @return float
     */
    public function calcularPorcentajeAprobacion($diagnosticId)
    {
        $historial = $this->historialDiagnosticos(null); // Obtener todos
        
        $total = 0;
        $aprobados = 0;
        
        foreach ($historial as $item) {
            if ((int) $item['diagnostic_id'] === $diagnosticId) {
                $total++;
                if ($item['passed']) {
                    $aprobados++;
                }
            }
        }
        
        return $total > 0 ? ($aprobados / $total) * 100 : 0;
    }
    
    /**
     * Obtener promedio de puntaje de un diagnóstico
     * @param int $diagnosticId
     * @return float
     */
    public function obtenerPromedioPuntaje($diagnosticId)
    {
        $historial = $this->historialDiagnosticos(null);
        
        // ✨ CORRECCIÓN ADICIONAL: Asegura que $historial es un array antes de usar foreach.
        if (!is_array($historial)) {
            $historial = [];
        }
        
        $total = 0;
        $suma = 0;
        
        foreach ($historial as $item) {
            if ((int) $item['diagnostic_id'] === $diagnosticId) {
                $total++;
                $suma += $item['score_percentage'];
            }
        }
        
        return $total > 0 ? $suma / $total : 0;
    }

    // ==========================================
    /**
 * Predecir dificultades de aprendizaje
 */
public function predecirDificultades($userId)
{
    $factores = [
        'rendimiento_bajo' => false,
        'tiempo_excesivo' => false,
        'errores_recurrentes' => [],
        'sin_progreso' => false,
        'score' => 0
    ];
    
    try {
        // 1. Verificar rendimiento bajo
        $stmt = $this->pdo->prepare("
            SELECT AVG(score) as avg_score
            FROM diagnostic_responses
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $avgScore = $result['avg_score'] ?? 0;
        
        if ($avgScore > 0 && $avgScore < 50) {
            $factores['rendimiento_bajo'] = true;
            $factores['score'] += 30;
        }
        
        // 2. Detectar patrones de error
        $stmt = $this->pdo->prepare("
            SELECT 
                dq.subject_area,
                COUNT(*) as errores
            FROM diagnostic_responses dr
            JOIN diagnostic_questions dq ON dr.question_id = dq.id
            WHERE dr.user_id = ?
            AND dr.is_correct = 0
            GROUP BY dq.subject_area
            HAVING errores >= 3
            ORDER BY errores DESC
        ");
        $stmt->execute([$userId]);
        $errores = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (!empty($errores)) {
            $factores['errores_recurrentes'] = array_column($errores, 'subject_area');
            $factores['score'] += 25;
        }
        
        // 3. Verificar progreso
        $stmt = $this->pdo->prepare("
            SELECT 
                dr1.score as score_reciente,
                dr2.score as score_anterior
            FROM diagnostic_responses dr1
            LEFT JOIN diagnostic_responses dr2 ON dr2.user_id = dr1.user_id 
                AND dr2.created_at < dr1.created_at
            WHERE dr1.user_id = ?
            ORDER BY dr1.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $progreso = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $sinMejora = 0;
        foreach ($progreso as $p) {
            if ($p['score_anterior'] && $p['score_reciente'] <= $p['score_anterior']) {
                $sinMejora++;
            }
        }
        
        if ($sinMejora >= 3) {
            $factores['sin_progreso'] = true;
            $factores['score'] += 20;
        }
        
    } catch (\PDOException $e) {
        error_log("Error prediciendo dificultades: " . $e->getMessage());
    }
    
    return $factores;
}

/**
 * Obtener áreas de mejora sugeridas
 */
public function obtenerAreasMejora($userId)
{
    try {
        $stmt = $this->pdo->prepare("
            SELECT 
                dq.subject_area,
                COUNT(*) as total_preguntas,
                SUM(CASE WHEN dr.is_correct = 1 THEN 1 ELSE 0 END) as correctas,
                ROUND((SUM(CASE WHEN dr.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as porcentaje
            FROM diagnostic_responses dr
            JOIN diagnostic_questions dq ON dr.question_id = dq.id
            WHERE dr.user_id = ?
            GROUP BY dq.subject_area
            /* ⭐ ARREGLO CLAVE: Se elimina la cláusula HAVING para traer TODAS las áreas,
               y el ReportController filtra las débiles (por debajo de 60%).
               Si deseas filtrar por 70%, cambia < 60 en el controlador.
               SE QUITÓ: HAVING porcentaje < 70
            */
            ORDER BY porcentaje ASC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Error obteniendo áreas de mejora: " . $e->getMessage());
        return [];
    }
}

// La función obtenerRendimientoEstudiante está bien, la dejamos igual:
/*
public function obtenerRendimientoEstudiante($userId) 
{
    $result = $this->callProcedureSingle('sp_obtener_rendimiento_estudiante', [$userId]);
    
    return $result ?: [
        'total_responses' => 0,
        'correct_responses' => 0,
        'percentage' => 0.00
    ];
}
*/
}