<?php
// app/DataAccessModels/RutaAprendizajeModel.php

namespace App\DataAccessModels;

class RutaAprendizajeModel extends BaseModel 
{
    // De LearningPath.php
    public function obtenerRutasUsuario($userId) 
    {
        return $this->callProcedureMultiple('sp_obtener_rutas_usuario', [$userId]);
    }

    // De LearningPath.php + LearningPathContent.php
    public function obtenerContenidosRuta($learningPathId) 
    {
        return $this->callProcedureMultiple('sp_obtener_contenidos_ruta', [$learningPathId]);
    }

    // De LearningPathContent.php
    public function completarContenidoRuta($learningPathContentId, $timeSpent) 
    {
        return $this->callProcedureNoReturn('sp_completar_contenido_ruta', 
            [$learningPathContentId, $timeSpent]
        );
    }
}