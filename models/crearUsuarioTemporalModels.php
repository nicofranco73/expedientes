<?php

/**
 * Clase que maneja la conexión y la persistencia de datos del usuario.
 * Actúa como el Modelo.
 */
class UserModel
{
    private PDO $db;

    /**
     * Conexión a la base de datos usando las credenciales proporcionadas.
     * En un entorno real, estas credenciales deberían estar en un archivo de configuración separado.
     */
    public function __construct()
    {
        try {
            $this->db = new PDO(
                "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
                "c2810161_iniciad",
                "li62veMAdu",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            // En un entorno de producción, esto debería registrarse en un log en lugar de mostrarse directamente.
            throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    /**
     * Verifica si un nombre de usuario ya existe en la base de datos.
     * @param string $username
     * @return bool
     */
    public function checkUserExists(string $username): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        return (bool) $stmt->fetch();
    }

    /**
     * Verifica si un email ya existe en la base de datos.
     * @param string $email
     * @return bool
     */
    public function checkEmailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    /**
     * Inserta un nuevo usuario en la base de datos.
     * @param array $data Los datos del usuario, incluyendo el hash de la contraseña.
     * @return bool
     */
    public function createUser(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (username, password_hash, nombre, apellido, email, role, is_active, is_superuser) 
            VALUES (?, ?, ?, ?, ?, ?, 1, ?)
        ");
        
        $is_superuser = ($data['role'] === 'admin') ? 1 : 0;
        
        return $stmt->execute([
            $data['username'],
            $data['password_hash'],
            $data['nombre'],
            $data['apellido'],
            $data['email'],
            $data['role'],
            $is_superuser
        ]);
    }
}