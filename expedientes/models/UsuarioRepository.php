<?php

require_once __DIR__ . '/../db/Database.php';

class UsuarioRepository {
    
    private $db;

    public function __construct() {
        // Obtenemos la conexión de la clase estática Database
        $this->db = Database::conectar();
    }

    /**
     * Busca un usuario por su nombre de usuario.
     * Usado para la autenticación.
     * @param string $usuario Nombre de usuario.
     * @return array|false Retorna los datos del usuario incluyendo el hash de la contraseña, o false.
     */
    public function buscarPorUsuario(string $usuario): array|false {
        // Necesitas el campo 'password' para verificar el hash.
        $sql = "SELECT id, usuario, password, rol, is_superuser FROM usuarios WHERE usuario = :usuario LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            
            // Usamos PDO::FETCH_ASSOC por defecto (ya definido en Database.php)
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error al buscar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los usuarios del sistema.
     * @return array Retorna la lista de todos los usuarios (nunca la contraseña).
     */
    public function obtenerTodos(): array {
        $sql = "SELECT id, usuario, rol, nombre, apellido, email, created_at FROM usuarios ORDER BY id DESC";

        try {
            // Utilizamos el método prepare para evitar posibles inyecciones SQL
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
}