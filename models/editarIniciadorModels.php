<?php
require_once 'config.php';

class IniciadorModel {
    private PDO $db;

    public function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $this->db = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function getIniciadorById(int $id): ?array {
        $sql = "SELECT * FROM persona_fisica WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $iniciador = $stmt->fetch();
        return $iniciador ?: null;
    }

    public function checkDuplicateDni(string $dni, int $id): bool {
        $sql = "SELECT id FROM persona_fisica WHERE dni = :dni AND id != :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dni' => $dni, ':id' => $id]);
        return (bool)$stmt->fetch();
    }

    public function updateIniciador(int $id, array $data): bool {
        $sql = "UPDATE persona_fisica SET 
                    apellido = :apellido, nombre = :nombre, dni = :dni, cuil = :cuil, 
                    fecha_nacimiento = :fecha_nacimiento, nacionalidad = :nacionalidad, 
                    estado_civil = :estado_civil, profesion = :profesion, 
                    email = :email, tel = :tel, cel = :cel, calle = :calle, 
                    numero = :numero, piso = :piso, depto = :depto, 
                    localidad = :localidad, cp = :cp, observaciones = :observaciones
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':apellido' => $data['apellido'],
            ':nombre' => $data['nombre'],
            ':dni' => $data['dni'],
            ':cuil' => $data['cuil'] ?? '',
            ':fecha_nacimiento' => !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null,
            ':nacionalidad' => $data['nacionalidad'] ?? '',
            ':estado_civil' => $data['estado_civil'] ?? '',
            ':profesion' => $data['profesion'] ?? '',
            ':email' => $data['email'] ?? '',
            ':tel' => $data['tel'] ?? '',
            ':cel' => $data['cel'] ?? '',
            ':calle' => $data['calle'] ?? '',
            ':numero' => $data['numero'] ?? '',
            ':piso' => $data['piso'] ?? '',
            ':depto' => $data['depto'] ?? '',
            ':localidad' => $data['localidad'] ?? '',
            ':cp' => $data['cp'] ?? '',
            ':observaciones' => $data['observaciones'] ?? '',
            ':id' => $id
        ]);
    }
}