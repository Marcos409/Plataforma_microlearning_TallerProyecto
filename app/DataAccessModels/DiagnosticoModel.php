<?php
// app/DataAccessModels/DiagnosticoModel.php

namespace App\DataAccessModels;

use \PDO;  // ← AGREGAR ESTA LÍNEA

class DiagnosticoModel extends BaseModel 
{
    /**
     * Listar diagnósticos activos
     */
    public function listarDiagnosticos() 
    {
        return $this->callProcedureMultiple('sp_listar_diagnosticos', []);
    }

    /**
     * Obtener diagnóstico completo con preguntas
     */
    public function obtenerDiagnosticoCompleto($diagnosticId) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_diagnostico_completo(?)");
            $stmt->execute([$diagnosticId]);
            
            $diagnostico = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return [
                'diagnostico' => $diagnostico,
                'preguntas' => $preguntas
            ];
        } catch (\PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Guardar respuesta de diagnóstico
     */
    public function guardarRespuesta($userId, $diagnosticId, $questionId, $userAnswer, $timeSpent) 
    {
        return $this->callProcedureNoReturn('sp_guardar_respuesta_diagnostico', 
            [$userId, $diagnosticId, $questionId, $userAnswer, $timeSpent]
        );
    }

    /**
     * Finalizar diagnóstico y calcular resultado
     */
    public function finalizarDiagnostico($userId, $diagnosticId, $timeTaken) 
    {
        return $this->callProcedureSingle('sp_finalizar_diagnostico', 
            [$userId, $diagnosticId, $timeTaken]
        );
    }

    /**
     * Obtener historial de diagnósticos de un usuario
     */
    public function historialDiagnosticos($userId) 
    {
        return $this->callProcedureMultiple('sp_historial_diagnosticos_usuario', [$userId]);
    }

    // =============================================
    // ✅ MÉTODOS CORREGIDOS - AHORA USAN SPs
    // =============================================

    /**
     * Contar diagnósticos completados por un usuario
     */
    public function contarDiagnosticosCompletados($userId) 
    {
        try {
            $result = $this->callProcedureSingle('sp_contar_diagnosticos_completados', [$userId]);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            error_log("Error al contar diagnósticos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener rendimiento de un estudiante
     */
    public function obtenerRendimientoEstudiante($userId) 
    {
        try {
            $result = $this->callProcedureSingle('sp_obtener_rendimiento_estudiante', [$userId]);
            
            return $result ?? [
                'total_responses' => 0,
                'correct_responses' => 0,
                'percentage' => 0
            ];
        } catch (\Exception $e) {
            error_log("Error al obtener rendimiento: " . $e->getMessage());
            return [
                'total_responses' => 0,
                'correct_responses' => 0,
                'percentage' => 0
            ];
        }
    }

    /**
     * Obtener rendimiento por materia
     */
    public function obtenerRendimientoPorMateria() 
    {
        return $this->callProcedureMultiple('sp_rendimiento_por_materia', []);
    }

    /**
     * Obtener rendimiento por mes (últimos 6 meses)
     */
    public function obtenerRendimientoPorMes() 
    {
        return $this->callProcedureMultiple('sp_obtener_rendimiento_por_mes', []);
    }

    /**
     * Obtener diagnóstico por ID
     */
    public function obtenerDiagnostico($id) 
    {
        return $this->callProcedureSingle('sp_obtener_diagnostico', [$id]);
    }

    /**
     * Crear diagnóstico
     */
    public function crearDiagnostico($title, $description, $subjectArea, $difficultyLevel, $timeLimit, $passingScore) 
    {
        try {
            $result = $this->callProcedureSingle('sp_crear_diagnostico', [
                $title, $description, $subjectArea, $difficultyLevel, $timeLimit, $passingScore
            ]);
            return $result['id'] ?? null;
        } catch (\Exception $e) {
            error_log("Error al crear diagnóstico: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar diagnóstico
     */
    public function actualizarDiagnostico($id, $title, $description, $subjectArea, $difficultyLevel, $timeLimit, $passingScore, $active) 
    {
        try {
            $result = $this->callProcedureSingle('sp_actualizar_diagnostico', [
                $id, $title, $description, $subjectArea, $difficultyLevel, $timeLimit, $passingScore, $active
            ]);
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (\Exception $e) {
            error_log("Error al actualizar diagnóstico: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar diagnóstico
     */
    public function eliminarDiagnostico($id) 
    {
        return $this->callProcedureNoReturn('sp_eliminar_diagnostico', [$id]);
    }

    /**
     * Crear pregunta
     */
    public function crearPregunta($diagnosticId, $questionText, $questionType, $options, $correctAnswer, $points) 
    {
        try {
            $result = $this->callProcedureSingle('sp_crear_pregunta', [
                $diagnosticId, $questionText, $questionType, $options, $correctAnswer, $points
            ]);
            return $result['id'] ?? null;
        } catch (\Exception $e) {
            error_log("Error al crear pregunta: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener pregunta por ID
     */
    public function obtenerPregunta($id) 
    {
        return $this->callProcedureSingle('sp_obtener_pregunta', [$id]);
    }

    /**
     * Actualizar pregunta
     */
    public function actualizarPregunta($id, $questionText, $questionType, $options, $correctAnswer, $points) 
    {
        try {
            $result = $this->callProcedureSingle('sp_actualizar_pregunta', [
                $id, $questionText, $questionType, $options, $correctAnswer, $points
            ]);
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (\Exception $e) {
            error_log("Error al actualizar pregunta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar pregunta
     */
    public function eliminarPregunta($id) 
    {
        return $this->callProcedureNoReturn('sp_eliminar_pregunta', [$id]);
    }

    /**
     * Contar diagnósticos completados hoy
     */
    public function contarDiagnosticosHoy() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total
                FROM diagnostic_results
                WHERE DATE(completed_at) = CURDATE()
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC); // ← Ahora funciona con el use PDO arriba
            return $result['total'] ?? 0;
        } catch (\PDOException $e) {
            error_log("Error: " . $e->getMessage());
            return 0;
        }
    }
}