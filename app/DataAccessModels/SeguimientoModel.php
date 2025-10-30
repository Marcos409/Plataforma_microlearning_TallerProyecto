<?php
// app/DataAccessModels/SeguimientoModel.php

namespace App\DataAccessModels;

class SeguimientoModel extends BaseModel 
{
    /**
     * Crear seguimiento
     */
    public function crear($data) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_crear_seguimiento(?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['user_id'],
                $data['admin_id'],
                $data['type'],
                $data['scheduled_at'],
                $data['notes'] ?? null
            ]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result['id'] ?? null;
        } catch (\PDOException $e) {
            error_log("Error al crear seguimiento: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener seguimiento por ID
     */
    public function obtenerPorId($id) 
    {
        return $this->callProcedureSingle('sp_obtener_seguimiento', [$id]);
    }

    /**
     * Listar seguimientos de un usuario
     */
    public function listarPorUsuario($userId) 
    {
        return $this->callProcedureMultiple('sp_listar_seguimientos_usuario', [$userId]);
    }

    /**
     * Listar seguimientos pendientes
     */
    public function listarPendientes() 
    {
        return $this->callProcedureMultiple('sp_listar_seguimientos_pendientes', []);
    }

    /**
     * Completar seguimiento
     */
    public function completar($id, $notes = null) 
    {
        return $this->callProcedureNoReturn('sp_completar_seguimiento', [$id, $notes]);
    }

    /**
     * Cancelar seguimiento
     */
    public function cancelar($id) 
    {
        return $this->callProcedureNoReturn('sp_cancelar_seguimiento', [$id]);
    }

    /**
 * Actualizar un seguimiento
 */
public function actualizar($id, $data)
{
    return $this->callProcedureNoReturn('sp_actualizar_seguimiento', [
        $id,
        $data['scheduled_at'],
        $data['type'],
        $data['notes'] ?? null
    ]);
}

/**
 * Eliminar un seguimiento (hard delete)
 */
public function eliminar($id)
{
    return $this->callProcedureNoReturn('sp_eliminar_seguimiento', [$id]);
}
}