<?php
// require_once 'db/Database.php'; // Asumimos que Database.php ya está cargado

class UsuarioRepository {
    private $db;

    public function __construct(PDO $db_conexion) {
        $this->db = $db_conexion;
    }

    /**
     * Busca un usuario por su nombre de usuario. Usado para la autenticación.
     * @param string $usuario Nombre de usuario.
     * @return array|false Retorna los datos del usuario incluyendo el hash de la contraseña, o false.
     */
    public function buscarPorUsuario(string $usuario) {
        // CORRECCIÓN: Usamos el nombre de columna correcto confirmado en la BD: 'password_hash'
        $sql = "SELECT id, username, password_hash, rol, is_superuser FROM usuarios WHERE username = :usuario";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error al buscar usuario: " . $e->getMessage());
            return false;
        }
    }
}