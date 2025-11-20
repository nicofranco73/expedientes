<?php

class ConcejalModel
{
    private $db;

    /**
     * Constructor que establece la conexión PDO.
     * En una aplicación real, las credenciales deberían cargarse desde un archivo de configuración.
     */
    public function __construct()
    {
        $host = "localhost";
        $dbname = "c2810161_iniciad";
        $user = "c2810161_iniciad";
        $pass = "li62veMAdu";
        $charset = "utf8mb4";
        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->db = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // Manejo de error de conexión más detallado para debug,
            // pero que se debe ocultar en producción.
            error_log("Error de conexión a la BD: " . $e->getMessage());
            throw new \Exception("No se pudo conectar a la base de datos.");
        }
    }

    /**
     * Obtiene los datos de un concejal por su ID.
     * @param int $id El ID del concejal.
     * @return array|false Los datos del concejal o false si no existe.
     */
    public function getConcejalById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM concejales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Aquí se agregarían métodos para actualizar, insertar, eliminar, etc.
}