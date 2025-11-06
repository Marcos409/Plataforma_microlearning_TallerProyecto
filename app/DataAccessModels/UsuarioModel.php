<?php
// app/DataAccessModels/UsuarioModel.php

namespace App\DataAccessModels;

use PDO;
use PDOException;

class UsuarioModel extends BaseModel 
{
    // ==========================================
    // CRUD BÁSICO DE USUARIOS
    // ==========================================
    
    /**
     * Obtener usuario por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerUsuario($id) 
    {
        if (!is_numeric($id) || $id <= 0) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_usuario(?)");
            $stmt->execute([$id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerUsuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Listar todos los usuarios
     * @return array
     */
    public function listarUsuarios() 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_listar_usuarios()");
            $stmt->execute();
            
            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = $row;
            }
            
            $stmt->closeCursor();
            return $usuarios;
        } catch (PDOException $e) {
            error_log("Error en listarUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar usuarios con filtros
     * @param string|null $search
     * @param int|null $roleId
     * @param bool|null $active
     * @return array
     */
    public function buscarUsuarios($search = null, $roleId = null, $active = null) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_buscar_usuarios(?, ?, ?)");
            $stmt->execute([$search, $roleId, $active]);
            
            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuarios[] = $row;
            }
            
            $stmt->closeCursor();
            return $usuarios;
        } catch (PDOException $e) {
            error_log("Error en buscarUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear nuevo usuario
     * @param string $nombre
     * @param string $email
     * @param string $password
     * @param int $roleId
     * @param string|null $studentCode
     * @param string|null $career
     * @return int|false ID del usuario creado o false
     */
    public function crearUsuario($nombre, $email, $password, $roleId, $studentCode = null, $career = null) 
    {
        if (empty($nombre) || empty($email) || empty($password) || empty($roleId)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_crear_usuario(?, ?, ?, ?, ?, ?, @user_id)");
            $stmt->execute([$nombre, $email, $password, $roleId, $studentCode, $career]);
            $stmt->closeCursor();
            
            $result = $this->pdo->query("SELECT @user_id as user_id")->fetch(PDO::FETCH_ASSOC);
            return $result['user_id'] ?? false;
        } catch (PDOException $e) {
            error_log("Error en crearUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear usuario completo (con todos los campos)
     * @param array $datos
     * @return int|false
     */
    public function crearUsuarioCompleto($datos) 
    {
        if (empty($datos['name']) || empty($datos['email']) || empty($datos['password'])) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_crear_usuario_completo(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $datos['name'],
                $datos['email'],
                $datos['password'],
                $datos['role_id'],
                $datos['student_code'] ?? null,
                $datos['career'] ?? null,
                $datos['semester'] ?? null,
                $datos['phone'] ?? null
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result['id'] ?? false;
        } catch (PDOException $e) {
            error_log("Error en crearUsuarioCompleto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario
     * @param int $id
     * @param string $nombre
     * @param string $email
     * @param int $roleId
     * @param string|null $studentCode
     * @param string|null $career
     * @return bool
     */
    public function actualizarUsuario($id, $nombre, $email, $roleId, $studentCode = null, $career = null) 
    {
        if (!is_numeric($id) || $id <= 0 || empty($nombre) || empty($email)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_actualizar_usuario(?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $nombre, $email, $roleId, $studentCode, $career]);
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en actualizarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario completo (todos los campos)
     * @param int $id
     * @param string $nombre
     * @param string $email
     * @param int $roleId
     * @param string|null $studentCode
     * @param string|null $career
     * @param int|null $semester
     * @param string|null $phone
     * @return bool
     */
    public function actualizarUsuarioCompleto($id, $nombre, $email, $roleId, $studentCode, $career, $semester, $phone) 
    {
        if (!is_numeric($id) || $id <= 0 || empty($nombre) || empty($email)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_actualizar_usuario_completo(?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $nombre, $email, $roleId, $studentCode, $career, $semester, $phone]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['affected_rows'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizarUsuarioCompleto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar usuario
     * @param int $id
     * @return bool
     */
    public function eliminarUsuario($id) 
    {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_eliminar_usuario(?)");
            $stmt->execute([$id]);
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en eliminarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado de usuario (activar/desactivar)
     * @param int $id
     * @param bool $active
     * @return bool
     */
    public function cambiarEstadoUsuario($id, $active) 
    {
        if (!is_numeric($id) || $id <= 0) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_cambiar_estado_usuario(?, ?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->bindParam(2, $active, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en cambiarEstadoUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar contraseña
     * @param int $id
     * @param string $password
     * @return bool
     */
    public function actualizarPassword($id, $password) 
    {
        if (!is_numeric($id) || $id <= 0 || empty($password)) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_actualizar_password(?, ?)");
            $stmt->execute([$id, $password]);
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en actualizarPassword: " . $e->getMessage());
            return false;
        }
    }

    // ==========================================
    // GESTIÓN DE ROLES
    // ==========================================

    /**
     * Listar todos los roles
     * @return array
     */
    public function listarRoles() 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_listar_roles()");
            $stmt->execute();
            
            $roles = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $roles[] = $row;
            }
            
            $stmt->closeCursor();
            return $roles;
        } catch (PDOException $e) {
            error_log("Error en listarRoles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar rol de múltiples usuarios
     * @param array $userIds
     * @param int $roleId
     * @return int Número de usuarios actualizados
     */
    public function actualizarRolMasivo($userIds, $roleId) 
    {
        if (empty($userIds) || !is_array($userIds) || !is_numeric($roleId)) {
            return 0;
        }
        
        try {
            $idsString = implode(',', $userIds);
            
            $stmt = $this->pdo->prepare("CALL sp_actualizar_rol_masivo(?, ?)");
            $stmt->execute([$idsString, $roleId]);
            $stmt->closeCursor();
            
            return count($userIds);
        } catch (PDOException $e) {
            error_log("Error en actualizarRolMasivo: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar usuarios por rol
     * @param int $roleId
     * @return int
     */
    public function contarPorRol($roleId) 
    {
        if (!is_numeric($roleId) || $roleId <= 0) {
            return 0;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_contar_usuarios_por_rol(?)");
            $stmt->execute([$roleId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en contarPorRol: " . $e->getMessage());
            return 0;
        }
    }

    // ==========================================
    // USUARIOS ESPECÍFICOS POR ROL
    // ==========================================

    /**
     * Obtener estudiantes (role_id = 3)
     * @return array
     */
    public function obtenerEstudiantes() 
    {
        return $this->buscarUsuarios(null, 3, null);
    }

    /**
     * Obtener estudiantes con filtros
     * @param string|null $search
     * @param bool|null $status
     * @return array
     */
    public function obtenerEstudiantesFiltros($search = null, $status = null) 
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_estudiantes_filtros(?, ?)");
            $stmt->execute([$search, $status]);
            
            $estudiantes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $estudiantes[] = $row;
            }
            
            $stmt->closeCursor();
            return $estudiantes;
        } catch (PDOException $e) {
            error_log("Error en obtenerEstudiantesFiltros: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener usuarios pendientes de asignación de rol (role_id = 4)
     * @return array
     */
    public function usuariosPendientesRol() 
    {
        return $this->buscarUsuarios(null, 4, null);
    }

    /**
     * Obtener docentes (role_id = 2)
     * @return array
     */
    public function obtenerDocentes() 
    {
        return $this->buscarUsuarios(null, 2, null);
    }

    /**
     * Obtener administradores (role_id = 1)
     * @return array
     */
    public function obtenerAdministradores() 
    {
        return $this->buscarUsuarios(null, 1, null);
    }

    // ==========================================
    // DATOS DEL ESTUDIANTE
    // ==========================================

    /**
     * Obtener progreso del estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerProgresoEstudiante($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_progreso_estudiante(?)");
            $stmt->execute([$userId]);
            
            $progreso = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $progreso[] = $row;
            }
            
            $stmt->closeCursor();
            return $progreso;
        } catch (PDOException $e) {
            error_log("Error en obtenerProgresoEstudiante: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener rutas de aprendizaje del estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerRutasAprendizaje($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_rutas_usuario(?)");
            $stmt->execute([$userId]);
            
            $rutas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rutas[] = $row;
            }
            
            $stmt->closeCursor();
            return $rutas;
        } catch (PDOException $e) {
            error_log("Error en obtenerRutasAprendizaje: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener recomendaciones del estudiante
     * @param int $userId
     * @return array
     */
    public function obtenerRecomendaciones($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return [];
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_obtener_recomendaciones(?)");
            $stmt->execute([$userId]);
            
            $recomendaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $recomendaciones[] = $row;
            }
            
            $stmt->closeCursor();
            return $recomendaciones;
        } catch (PDOException $e) {
            error_log("Error en obtenerRecomendaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener historial de diagnósticos del estudiante
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

    /**
     * Contar actividades completadas de un usuario
     * @param int $userId
     * @return int
     */
    public function contarActividadesCompletadas($userId) 
    {
        if (!is_numeric($userId) || $userId <= 0) {
            return 0;
        }
        
        try {
            $stmt = $this->pdo->prepare("CALL sp_contar_actividades_usuario(?)");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en contarActividadesCompletadas: " . $e->getMessage());
            return 0;
        }
    }

    // ==========================================
    // ESTADÍSTICAS Y CONTADORES
    // ==========================================

    /**
     * Contar nuevos usuarios en los últimos N días
     * @param int $days
     * @return int
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
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en contarNuevosUsuarios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Contar nuevos usuarios del mes anterior
     * @return int
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
            $stmt->closeCursor();
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error en contarNuevosUsuariosMesAnterior: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener estadísticas generales de usuarios
     * @return array
     */
    public function obtenerEstadisticasGenerales() 
    {
        try {
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as usuarios_activos,
                    SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) as usuarios_inactivos,
                    SUM(CASE WHEN role_id = 1 THEN 1 ELSE 0 END) as total_admins,
                    SUM(CASE WHEN role_id = 2 THEN 1 ELSE 0 END) as total_docentes,
                    SUM(CASE WHEN role_id = 3 THEN 1 ELSE 0 END) as total_estudiantes,
                    SUM(CASE WHEN role_id = 4 THEN 1 ELSE 0 END) as pendientes_rol
                FROM users
            ");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return [
                'total_usuarios' => (int)($result['total_usuarios'] ?? 0),
                'usuarios_activos' => (int)($result['usuarios_activos'] ?? 0),
                'usuarios_inactivos' => (int)($result['usuarios_inactivos'] ?? 0),
                'total_admins' => (int)($result['total_admins'] ?? 0),
                'total_docentes' => (int)($result['total_docentes'] ?? 0),
                'total_estudiantes' => (int)($result['total_estudiantes'] ?? 0),
                'pendientes_rol' => (int)($result['pendientes_rol'] ?? 0)
            ];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasGenerales: " . $e->getMessage());
            return [
                'total_usuarios' => 0,
                'usuarios_activos' => 0,
                'usuarios_inactivos' => 0,
                'total_admins' => 0,
                'total_docentes' => 0,
                'total_estudiantes' => 0,
                'pendientes_rol' => 0
            ];
        }
    }

    // ==========================================
    // UTILIDADES Y VALIDACIONES
    // ==========================================

    /**
     * Verificar si existe un email
     * @param string $email
     * @param int|null $excludeId ID a excluir (para actualizaciones)
     * @return bool
     */
    public function existeEmail($email, $excludeId = null) 
    {
        if (empty($email)) {
            return false;
        }
        
        try {
            if ($excludeId) {
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as total
                    FROM users
                    WHERE email = ? AND id != ?
                ");
                $stmt->execute([$email, $excludeId]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*) as total
                    FROM users
                    WHERE email = ?
                ");
                $stmt->execute([$email]);
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return ($result['total'] ?? 0) > 0;
        } catch (PDOException $e) {
            error_log("Error en existeEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar usuario por email
     * @param string $email
     * @return array|null
     */
    public function buscarPorEmail($email) 
    {
        if (empty($email)) {
            return null;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarPorEmail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar usuario por ID (alias)
     * @param int $id
     * @return array|null
     */
    public function buscarPorId($id) 
    {
        return $this->obtenerUsuario($id);
    }

    /**
     * Obtener perfil completo del usuario
     * @param int $userId
     * @return array|null
     */
    public function obtenerPerfilCompleto($userId) 
    {
        $usuario = $this->obtenerUsuario($userId);
        
        if (!$usuario) {
            return null;
        }
        
        // Si es estudiante, agregar datos adicionales
        if ($usuario['role_id'] == 3) {
            $usuario['progreso'] = $this->obtenerProgresoEstudiante($userId);
            $usuario['rutas'] = $this->obtenerRutasAprendizaje($userId);
            $usuario['recomendaciones'] = $this->obtenerRecomendaciones($userId);
            $usuario['historial_diagnosticos'] = $this->obtenerHistorialDiagnosticos($userId);
            $usuario['actividades_completadas'] = $this->contarActividadesCompletadas($userId);
        }
        
        return $usuario;
    }
}