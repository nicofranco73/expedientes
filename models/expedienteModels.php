
class ExpedienteModel {
    private $db;

    public function __construct(PDO $db_connection) {
        $this->db = $db_connection; // Inyección de dependencia de la conexión
    }

    public function obtenerExpediente(int $id) {
        // Lógica de consulta SELECT * FROM expedientes WHERE id = :id
    }

    public function obtenerHistorialPases(int $id) {
        // Lógica de consulta SELECT * FROM pases_expedientes WHERE id_expediente = :id
    }
}
