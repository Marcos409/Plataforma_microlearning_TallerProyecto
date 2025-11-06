<?php
// app/DataAccessModels/BaseModel.php

namespace App\DataAccessModels;

use PDO;
use PDOException;

class BaseModel 
{
    protected $pdo;
    private static $connection = null;

    public function __construct() 
    {
        // Usar conexión singleton
        if (self::$connection === null) {
            self::$connection = $this->createConnection();
        }
        $this->pdo = self::$connection;
    }

    /**
     * Crear conexión PDO (solo se ejecuta una vez)
     */
    private function createConnection()
    {
        try {
            // Soporte para env() de Laravel o variables de entorno directas
            $host = $this->getEnv('DB_HOST', 'localhost');
            $dbname = $this->getEnv('DB_DATABASE', 'bd_microlearning_uc');
            $user = $this->getEnv('DB_USERNAME', 'root');
            $pass = $this->getEnv('DB_PASSWORD', '');
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,  // ✅ Mejor seguridad
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset"  // ✅ Charset correcto
            ];

            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log("Error de conexión PDO: " . $e->getMessage());
            throw new \Exception("No se pudo conectar a la base de datos");
        }
    }

    /**
     * Obtener variable de entorno (compatible con/sin Laravel)
     */
    private function getEnv($key, $default = null)
    {
        // Si existe función env() (Laravel)
        if (function_exists('env')) {
            return env($key, $default);
        }
        
        // Si no, usar getenv() o $_ENV
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $_ENV[$key] ?? $default;
    }

    /**
     * Ejecutar un stored procedure que retorna un solo registro
     * @param string $procedureName
     * @param array $params
     * @return array|null
     */
    protected function callProcedureSingle($procedureName, $params = [])
    {
        try {
            $stmt = $this->prepareCall($procedureName, $params);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error en $procedureName: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Ejecutar un stored procedure que retorna múltiples registros
     * @param string $procedureName
     * @param array $params
     * @return array
     */
    protected function callProcedureMultiple($procedureName, $params = [])
    {
        try {
            $stmt = $this->prepareCall($procedureName, $params);
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en $procedureName: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ejecutar un stored procedure sin retorno (INSERT, UPDATE, DELETE)
     * @param string $procedureName
     * @param array $params
     * @return bool
     */
    protected function callProcedureNoReturn($procedureName, $params = [])
    {
        try {
            $stmt = $this->prepareCall($procedureName, $params);
            $stmt->execute();
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en $procedureName: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Preparar llamada a stored procedure
     * @param string $procedureName
     * @param array $params
     * @return \PDOStatement
     */
    private function prepareCall($procedureName, $params = [])
    {
        // ✅ Manejo correcto de parámetros vacíos
        $placeholders = count($params) > 0 
            ? implode(',', array_fill(0, count($params), '?')) 
            : '';
        
        $sql = "CALL $procedureName($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        
        // Bind de parámetros
        foreach ($params as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        
        return $stmt;
    }

    /**
     * Obtener la última ID insertada
     * @return int
     */
    protected function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Iniciar transacción
     * @return bool
     */
    protected function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirmar transacción
     * @return bool
     */
    protected function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Revertir transacción
     * @return bool
     */
    protected function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * Verificar si hay una transacción activa
     * @return bool
     */
    protected function inTransaction()
    {
        return $this->pdo->inTransaction();
    }
}