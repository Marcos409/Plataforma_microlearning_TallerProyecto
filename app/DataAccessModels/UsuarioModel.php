<?php
// app/DataAccessModels/UsuarioModel.php

namespace App\DataAccessModels;

use \PDO;

class UsuarioModel extends BaseModel 
{
    public function obtenerUsuario($id) 
    {
        return $this->callProcedureSingle('sp_obtener_usuario', [$id]);
    }

    public function listarUsuarios() 
    {
        return $this->callProcedureMultiple('sp_listar_usuarios', []);
    }

    public function buscarUsuarios($search = null, $roleId = null, $active = null) 
    {
        return $this->callProcedureMultiple('sp_buscar_usuarios', [$search, $roleId, $active]);
    }

    public function crearUsuario($nombre, $email, $password, $roleId, $studentCode = null, $career = null) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_crear_usuario(?, ?, ?, ?, ?, ?, @user_id)");
            $stmt->execute([$nombre, $email, $password, $roleId, $studentCode, $career]);
            $stmt->closeCursor();
            
            $result = $this->pdo->query("SELECT @user_id as user_id")->fetch(PDO::FETCH_ASSOC);
            return $result['user_id'];
        } catch (\PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return null;
        }
    }

    public function actualizarUsuario($id, $nombre, $email, $roleId, $studentCode = null, $career = null) 
    {
        return $this->callProcedureNoReturn('sp_actualizar_usuario', 
            [$id, $nombre, $email, $roleId, $studentCode, $career]
        );
    }

    public function eliminarUsuario($id) 
    {
        return $this->callProcedureNoReturn('sp_eliminar_usuario', [$id]);
    }

    public function listarRoles() 
    {
        return $this->callProcedureMultiple('sp_listar_roles', []);
    }

        /**
     * Actualizar contraseña de usuario
     */
    public function actualizarPassword($id, $password) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_actualizar_password(?, ?)");
            $stmt->execute([$id, $password]);
            $stmt->closeCursor();
            return true;
        } catch (\PDOException $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuarios pendientes de rol (role_id = 4)
     */
    public function usuariosPendientesRol() 
    {
        return $this->buscarUsuarios(null, 4, null);
    }

    /**
     * Actualizar rol masivo (múltiples usuarios)
     */
    public function actualizarRolMasivo($userIds, $roleId) 
    {
        try {
            // Convertir array a string separado por comas
            $idsString = implode(',', $userIds);
            
            $stmt = $this->pdo->prepare("CALL sp_actualizar_rol_masivo(?, ?)");
            $stmt->execute([$idsString, $roleId]);
            $stmt->closeCursor();
            
            return count($userIds);
        } catch (\PDOException $e) {
            error_log("Error al actualizar roles masivos: " . $e->getMessage());
            return 0;
        }
    }
        /**
     * Cambiar estado de usuario (activar/desactivar)
     */
    public function cambiarEstadoUsuario($id, $active) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_cambiar_estado_usuario(?, ?)");
            $stmt->bindParam(1, $id, \PDO::PARAM_INT);
            $stmt->bindParam(2, $active, \PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
            return true;
        } catch (\PDOException $e) {
            error_log("Error al cambiar estado: " . $e->getMessage());
            return false;
        }
    }

    // Agregar al final de UsuarioModel.php

    /**
     * Contar usuarios por rol
     */
    public function contarPorRol($roleId) 
    {
        try {
            $result = $this->callProcedureSingle('sp_contar_usuarios_por_rol', [$roleId]);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            error_log("Error al contar usuarios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener estudiantes (role_id = 3)
     */
    public function obtenerEstudiantes() 
    {
        return $this->buscarUsuarios(null, 3, null);
    }

    /**
     * Buscar usuario por ID (alias de obtenerUsuario)
     */
    public function buscarPorId($id) 
    {
        return $this->obtenerUsuario($id);
    }

    /**
     * Contar actividades completadas de un usuario
     */
    public function contarActividadesCompletadas($userId) 
    {
        try {
            $result = $this->callProcedureSingle('sp_contar_actividades_usuario', [$userId]);
            return $result['total'] ?? 0;
        } catch (\Exception $e) {
            error_log("Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener estudiantes con filtros
     */
    public function obtenerEstudiantesFiltros($search = null, $status = null) 
    {
        return $this->callProcedureMultiple('sp_obtener_estudiantes_filtros', [$search, $status]);
    }

        /**
     * Actualizar usuario completo (incluye todos los campos)
     */
    public function actualizarUsuarioCompleto($id, $nombre, $email, $roleId, $studentCode, $career, $semester, $phone) 
    {
        return $this->callProcedureNoReturn('sp_actualizar_usuario_completo', 
            [$id, $nombre, $email, $roleId, $studentCode, $career, $semester, $phone]
        );
    }

    /**
 * Obtener progreso del estudiante
 */
public function obtenerProgresoEstudiante($userId) 
{
    return $this->callProcedureMultiple('sp_obtener_progreso_estudiante', [$userId]);
}

/**
 * Obtener rutas de aprendizaje del estudiante
 */
public function obtenerRutasAprendizaje($userId) 
{
    return $this->callProcedureMultiple('sp_obtener_rutas_usuario', [$userId]);
}

/**
 * Obtener recomendaciones del estudiante
 */
public function obtenerRecomendaciones($userId) 
{
    return $this->callProcedureMultiple('sp_obtener_recomendaciones', [$userId]);
}

/**
 * Obtener historial de diagnósticos del estudiante
 */
public function obtenerHistorialDiagnosticos($userId) 
{
    return $this->callProcedureMultiple('sp_historial_diagnosticos_usuario', [$userId]);
}


/**
 * Contar nuevos usuarios en los últimos N días
 */
public function contarNuevosUsuarios($days = 7) 
{
    try {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND active = 1
        ");
        $stmt->execute([$days]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (\PDOException $e) {
        error_log("Error al contar nuevos usuarios: " . $e->getMessage());
        return 0;
    }
}

/**
 * Contar nuevos usuarios del mes anterior
 */
public function contarNuevosUsuariosMesAnterior() 
{
    try {
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as total
            FROM users
            WHERE MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            AND active = 1
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (\PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return 0;
    }
}
}