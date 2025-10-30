<?php
// app/DataAccessModels/RiskAlertModel.php

namespace App\DataAccessModels;

use \PDO;

class RiskAlertModel extends BaseModel 
{
    /**
     * Obtener alertas de riesgo de un estudiante
     * Usa el SP existente o crea uno nuevo
     */
    public function obtenerAlertasEstudiante($userId) 
    {
        // Necesitas crear este SP: sp_obtener_alertas_riesgo
        return $this->callProcedureMultiple('sp_obtener_alertas_riesgo', [$userId]);
    }

    /**
     * Calcular nivel de riesgo de un estudiante
     */
    public function calcularNivelRiesgo($userId) 
    {
        // Necesitas crear este SP: sp_calcular_nivel_riesgo
        $result = $this->callProcedureSingle('sp_calcular_nivel_riesgo', [$userId]);
        
        return $result ?? [
            'risk_level' => 'bajo',
            'avg_score' => 0,
            'total_subjects' => 0
        ];
    }
}