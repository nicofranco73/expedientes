<?php
// Asegurarse de iniciar la sesión si es necesario para permisos
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

try {
    // ----------------------------------------------------
    // CORRECCIÓN 1: Centralizar la Conexión PDO
    // El archivo está en 'controladores/consultas/', necesitamos salir dos niveles (../../)
    // para llegar a la carpeta 'db/'.
    require_once('../../db/connection.php');
    // La variable $db ahora está disponible.
    // ----------------------------------------------------

    // Validar inputs
    $numero = filter_var($_GET['numero'] ?? '', FILTER_VALIDATE_INT);
    $letra = $_GET['letra'] ?? '';
    $anio = filter_var($_GET['anio'] ?? '', FILTER_VALIDATE_INT);

    if (!$numero || !preg_match('/^[A-Z]$/', $letra) || !$anio) {
        throw new Exception("Datos de búsqueda inválidos");
    }

    // ----------------------------------------------------
    // CÓDIGO ELIMINADO: (Se ha eliminado el bloque 'new PDO(...)')
    /*
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    */
    // ----------------------------------------------------

    // Consultar expediente
    $sql = "SELECT * FROM expedientes 
            WHERE numero = :numero 
            AND letra = :letra 
            AND anio = :anio 
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':numero' => $numero,
        ':letra' => $letra,
        ':anio' => $anio
    ]);

    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expediente) {
        throw new Exception("No se encontró el expediente");
    }

    echo json_encode([
        'success' => true,
        'data' => $expediente
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
<script>
document.getElementById('formBusqueda').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const numero = document.getElementById('buscar_numero').value;
    const letra = document.getElementById('buscar_letra').value;
    const anio = document.getElementById('buscar_anio').value;

    if (!numero || !letra || !anio) {
        Swal.fire({
            title: 'Error',
            text: 'Complete todos los campos de búsqueda',
            icon: 'warning'
        });
        return;
    }

    try {
        // NOTA: La URL amigable (sin .php) se corregirá en el Paso 3, pero por ahora se deja así.
        const response = await fetch(`buscar_expediente.php?numero=${numero}&letra=${letra}&anio=${anio}`);
        const data = await response.json();

        if (data.success) {
            // Llenar el formulario con los datos
            document.getElementById('numero').value = data.data.numero;
            document.getElementById('letra').value = data.data.letra;
            document.getElementById('folio').value = data.data.folio;
            document.getElementById('libro').value = data.data.libro;
            document.getElementById('anio').value = data.data.anio;
            document.getElementById('fecha_hora_ingreso').value = data.data.fecha_hora_ingreso;
            document.getElementById('lugar').value = data.data.lugar;
            document.getElementById('extracto').value = data.data.extracto;
            document.getElementById('iniciador').value = data.data.iniciador;

            // Actualizar campos de lugar
            document.querySelector('input[name="lugar_anterior"]').value = data.data.lugar;
            document.querySelector('input[disabled]').value = data.data.lugar;

            // Deshabilitar campos que no deben modificarse
            ['numero', 'letra', 'folio', 'libro', 'anio'].forEach(id => {
                document.getElementById(id).disabled = true;
            });

            Swal.fire({
                title: 'Expediente encontrado',
                text: 'Los datos han sido cargados',
                icon: 'success'
            });
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        Swal.fire({
            title: 'Error',
            text: error.message || 'Error al buscar el expediente',
            icon: 'error'
        });
    }
});

// Para el botón reset, habilitar campos
document.querySelector('button[type="reset"]').addEventListener('click', function() {
    ['numero', 'letra', 'folio', 'libro', 'anio'].forEach(id => {
        document.getElementById(id).disabled = false;
    });
});
</script>