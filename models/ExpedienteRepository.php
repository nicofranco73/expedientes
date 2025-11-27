<?php
// Nota: No necesitas el require de Database.php aquí si usas autoload. 
// Si aún no usas autoload, puedes incluir la conexión al inicio.

class ExpedienteRepository {
    private $db;

    // Recibe la conexión PDO en el constructor
    public function __construct(PDO $db_conexion) {
        $this->db = $db_conexion;
    }

    /**
     * Obtiene los datos de un expediente por su ID.
     * @param int $id ID del expediente.
     * @return array|false Retorna el expediente o false si no existe.
     */
    public function obtenerExpedientePorId(int $id) {
        // Usa sentencias preparadas para prevenir inyección SQL
        $sql = "SELECT * FROM expedientes WHERE id = :id LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(); // Devuelve un array asociativo (gracias a PDO::FETCH_ASSOC)
        } catch (PDOException $e) {
            // Manejo de errores: loguear el error y devolver un resultado seguro
            error_log("Error al obtener expediente: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Crea un nuevo pase en el historial para un expediente.
     * @param array $datos_pase Array asociativo con los datos del pase.
     * @return bool Retorna true en caso de éxito, false en caso de error.
     */
    public function crearPase(array $datos_pase): bool {
        // Campos necesarios para la inserción
        $sql = "INSERT INTO pases_expediente (id_expediente, fecha_pase, destino, observaciones, usuario_id) 
                VALUES (:id_expediente, :fecha_pase, :destino, :observaciones, :usuario_id)";

        try {
            $stmt = $this->db->prepare($sql);
            
            // Ejecutamos pasando el array asociativo directamente, que debe coincidir con los marcadores.
            // Esto es mucho más limpio que usar bindParam() múltiples veces.
            return $stmt->execute([
                ':id_expediente' => $datos_pase['id_expediente'],
                ':fecha_pase'    => $datos_pase['fecha_pase'], 
                ':destino'       => $datos_pase['destino'], 
                ':observaciones' => $datos_pase['observaciones'],
                ':usuario_id'    => $datos_pase['usuario_id'] 
            ]);
            
        } catch (PDOException $e) {
            error_log("Error al crear pase: " . $e->getMessage());
            return false;
        }
    }
}


/**
     * Obtiene el historial de pases para un expediente específico.
     * @param int $id_expediente ID del expediente.
     * @return array Retorna la lista de pases o un array vacío si no hay pases.
     */
    public function obtenerHistorialPases(int $id_expediente): array {
        // Asegúrate de ordenar los pases lógicamente (ej. por fecha o ID de pase)
        $sql = "SELECT * FROM pases_expediente WHERE id_expediente = :id ORDER BY id ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_expediente, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(); // Devuelve todos los registros del historial
        } catch (PDOException $e) {
            error_log("Error al obtener historial de pases: " . $e->getMessage());
            // Es mejor devolver un array vacío que 'false' cuando se espera una colección
            return []; 
        }
    }


