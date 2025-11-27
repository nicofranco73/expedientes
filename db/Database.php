<?php

class Database {
    // 1. PROPIEDADES ESTÁTICAS DE CONEXIÓN
    private static $host = 'localhost';
    private static $dbname = 'databaseauxiliar';
    private static $user = 'root';
    private static $password = ''; // Probamos con 'mysql' como alternativa común
    private static $conexion = null;
    
    /**
     * Establece la conexión a la base de datos usando PDO.
     * @return PDO Retorna la instancia de PDO.
     */
    public static function conectar(): PDO {
        if (self::$conexion === null) {
            try {
                // 2. CONEXIÓN USANDO LAS PROPIEDADES DECLARADAS ARRIBA
                self::$conexion = new PDO(
                    "mysql:host=".self::$host.";dbname=".self::$dbname.";charset=utf8mb4",
                    self::$user,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                error_log("Error de conexión BD: " . $e->getMessage());
                die("Error de conexión a la base de datos.");
            }
        }
        return self::$conexion;
    }
    
    // Puedes añadir un método para cerrar la conexión si es necesario
    public static function cerrar() {
        self::$conexion = null;
    }
}