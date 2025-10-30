<?php
// app/DataAccessModels/BaseModel.php

namespace App\DataAccessModels;

use PDO;
use PDOException;

class BaseModel 
{
    protected $pdo;

    public function __construct() 
    {
        try {
            $host = env('DB_HOST', 'localhost');
            $dbname = env('DB_DATABASE', 'bd_microlearning_uc');
            $user = env('DB_USERNAME', 'root');
            $pass = env('DB_PASSWORD', '');

            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception("Error de conexión: " . $e->getMessage());
        }
    }

    /**
     * Ejecutar un stored procedure que retorna un solo registro
     */
    protected function callProcedureSingle($procedureName, $params = [])
    {
        try {
            $placeholders = implode(',', array_fill(0, count($params), '?'));
            $stmt = $this->pdo->prepare("CALL $procedureName($placeholders)");
            
            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }
            
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
     */
    protected function callProcedureMultiple($procedureName, $params = [])
    {
        try {
            $placeholders = implode(',', array_fill(0, count($params), '?'));
            $stmt = $this->pdo->prepare("CALL $procedureName($placeholders)");
            
            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }
            
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
     * Ejecutar un stored procedure sin retorno
     */
    protected function callProcedureNoReturn($procedureName, $params = [])
    {
        try {
            $placeholders = implode(',', array_fill(0, count($params), '?'));
            $stmt = $this->pdo->prepare("CALL $procedureName($placeholders)");
            
            foreach ($params as $index => $value) {
                $stmt->bindValue($index + 1, $value);
            }
            
            $stmt->execute();
            $stmt->closeCursor();
            
            return true;
        } catch (PDOException $e) {
            error_log("Error en $procedureName: " . $e->getMessage());
            return false;
        }
    }
}