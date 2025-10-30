<?php
// app/DataAccessModels/RecomendacionModel.php

namespace App\DataAccessModels;

class RecomendacionModel extends BaseModel 
{
    // De Recommendation.php
    public function obtenerRecomendaciones($userId) 
    {
        return $this->callProcedureMultiple('sp_obtener_recomendaciones', [$userId]);
    }

    public function marcarRecomendacionVista($recommendationId) 
    {
        return $this->callProcedureNoReturn('sp_marcar_recomendacion_vista', [$recommendationId]);
    }

    public function crearRecomendacion($userId, $contentId, $reason, $priority, $generatedBy) 
    {
        return $this->callProcedureNoReturn('sp_crear_recomendacion', 
            [$userId, $contentId, $reason, $priority, $generatedBy]
        );
    }

    // De RiskAlert.php (si tienes SPs para alertas)
    public function obtenerAlertasRiesgo($userId) 
    {
        return $this->callProcedureMultiple('sp_obtener_alertas_riesgo', [$userId]);
    }

    // De FollowUp.php (si tienes SPs para seguimiento)
    public function registrarSeguimiento($userId, $docenteId, $observaciones) 
    {
        return $this->callProcedureNoReturn('sp_registrar_seguimiento', 
            [$userId, $docenteId, $observaciones]
        );
    }
}