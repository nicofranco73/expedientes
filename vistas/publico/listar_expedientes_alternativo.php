<?php
session_start();
require 'header.php';

// Limpiar mensajes de error que no corresponden a esta página
if (isset($_SESSION['mensaje']) && strpos($_SESSION['mensaje'], 'ID de expediente') !== false) {
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
}


try {
     // Conectar a la base de datos
    $db = new PDO(
        "mysql:host=localhost;dbname=c2810161_iniciad;charset=utf8mb4",
        "c2810161_iniciad",
        "li62veMAdu",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Configuración de paginación
    $por_pagina = 10;
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $por_pagina;

    // Preparar condiciones WHERE
    $where = [];
    $params = [];

    if (!empty($_GET['numero'])) {
        $where[] = "numero LIKE :numero";
        $params[':numero'] = '%' . $_GET['numero'] . '%';
    }

    if (!empty($_GET['letra'])) {
        $where[] = "letra LIKE :letra";
        $params[':letra'] = '%' . $_GET['letra'] . '%';
    }

    if (!empty($_GET['folio'])) {
        $where[] = "folio LIKE :folio";
        $params[':folio'] = '%' . $_GET['folio'] . '%';
    }

    if (!empty($_GET['libro'])) {
        $where[] = "libro LIKE :libro";
        $params[':libro'] = '%' . $_GET['libro'] . '%';
    }

    if (!empty($_GET['anio'])) {
        $where[] = "anio LIKE :anio";
        $params[':anio'] = '%' . $_GET['anio'] . '%';
    }

    if (!empty($_GET['lugar'])) {
        $where[] = "lugar LIKE :lugar";
        $params[':lugar'] = '%' . $_GET['lugar'] . '%';
    }

    if (!empty($_GET['fecha_desde'])) {
        $where[] = "DATE(fecha_hora_ingreso) >= :fecha_desde";
        $params[':fecha_desde'] = $_GET['fecha_desde'];
    }

    if (!empty($_GET['fecha_hasta'])) {
        $where[] = "DATE(fecha_hora_ingreso) <= :fecha_hasta";
        $params[':fecha_hasta'] = $_GET['fecha_hasta'];
    }

    if (!empty($_GET['iniciador'])) {
        $where[] = "iniciador LIKE :iniciador";
        $params[':iniciador'] = '%' . $_GET['iniciador'] . '%';
    }

    // Construir consulta SQL
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Consulta total de registros con filtros
    $sql_count = "SELECT COUNT(*) FROM expedientes $whereClause";
    $stmt = $db->prepare($sql_count);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    $total_paginas = ceil($total / $por_pagina);

    // Consulta de expedientes con filtros y paginación
    $sql = "SELECT * FROM expedientes 
            $whereClause
            ORDER BY fecha_hora_ingreso DESC 
            LIMIT :offset, :limit";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);

    // Vincular parámetros de filtros
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }

    $stmt->execute();
    $expedientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al cargar los expedientes: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>Dashboard | Sistema de Expedientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/publico/css/estilos.css?v=3">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>


<body>

    
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="titulo-principal">Listado de Expedientes</h1>
                </div>

                <!-- Agregar el filtro -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-2">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="numero" name="numero" 
                                       value="<?= htmlspecialchars($_GET['numero'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="letra" class="form-label">Letra</label>
                                <input type="text" class="form-control" id="letra" name="letra" 
                                       value="<?= htmlspecialchars($_GET['letra'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="folio" class="form-label">Folio</label>
                                <input type="text" class="form-control" id="folio" name="folio" 
                                       value="<?= htmlspecialchars($_GET['folio'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="libro" class="form-label">Libro</label>
                                <input type="text" class="form-control" id="libro" name="libro" 
                                       value="<?= htmlspecialchars($_GET['libro'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="anio" class="form-label">Año</label>
                                <input type="text" class="form-control" id="anio" name="anio" 
                                       value="<?= htmlspecialchars($_GET['anio'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="lugar" class="form-label">Lugar actual</label>
                                <input type="text" class="form-control" id="lugar" name="lugar" 
                                       value="<?= htmlspecialchars($_GET['lugar'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label">Fecha desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                       value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_hasta" class="form-label">Fecha hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                       value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="iniciador" class="form-label">Iniciador</label>
                                <input type="text" class="form-control" id="iniciador" name="iniciador" 
                                       value="<?= htmlspecialchars($_GET['iniciador'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <a href="listar_expedientes.php" class="btn btn-outline-secondary px-4">
                                    <i class="bi bi-x-circle"></i> Limpiar filtros
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                                
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!empty($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Letra</th>
                                        <th>Folio</th>
                                        <th>Libro</th>
                                        <th>Año</th>
                                        <th>Lugar actual</th>
                                        <th>Fecha Ingreso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expedientes as $exp): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($exp['numero']) ?></td>
                                        <td><?= htmlspecialchars($exp['letra']) ?></td>
                                        <td><?= htmlspecialchars($exp['folio']) ?></td>
                                        <td><?= htmlspecialchars($exp['libro']) ?></td>
                                        <td><?= htmlspecialchars($exp['anio']) ?></td>
                                        <td><?= htmlspecialchars($exp['lugar']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($exp['fecha_hora_ingreso'])) ?></td>
                                        <td>
                                            <a href="actualizar_expedientes.php?id=<?= htmlspecialchars($exp['id']) ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-info"
                                                    onclick="verDetalles(<?= $exp['id'] ?>)"
                                                    title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <a href="pases_expediente.php?id=<?= htmlspecialchars($exp['id']) ?>" 
                                               class="btn btn-sm btn-outline-success"
                                               title="Pases">
                                                <i class="bi bi-arrow-left-right"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmarBorrado(<?= $exp['id'] ?>, '<?= htmlspecialchars($exp['numero']) ?>', '<?= htmlspecialchars($exp['letra']) ?>','<?= htmlspecialchars($exp['folio']) ?>', '<?= htmlspecialchars($exp['libro']) ?>', '<?= htmlspecialchars($exp['anio']) ?>')"
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
<?php
// Obtener todos los parámetros actuales excepto 'pagina'
$params = $_GET;
unset($params['pagina']);
$query_string = http_build_query($params);
$query_string = $query_string ? '&' . $query_string : '';
?>

<nav aria-label="Navegación de páginas" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
            <a class="page-link" href="?pagina=<?= $pagina-1 ?><?= $query_string ?>">Anterior</a>
        </li>
        
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                <a class="page-link" href="?pagina=<?= $i ?><?= $query_string ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
            <a class="page-link" href="?pagina=<?= $pagina+1 ?><?= $query_string ?>">Siguiente</a>
        </li>
    </ul>
</nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    async function verDetalles(id) {
        try {
            // Mostrar loader
            Swal.fire({
                title: 'Cargando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            // Obtener datos del expediente e historial en paralelo
            const [expedienteResp, historialResp] = await Promise.all([
                fetch(`obtener_expediente.php?id=${id}`),
                fetch(`obtener_historial_pases.php?id=${id}`)
            ]);

            const expediente = await expedienteResp.json();
            const historial = await historialResp.json();

            // Crear tabla de historial
            let historialHTML = '';
            if (historial.success && historial.data.length > 0) {
                // Ya no necesitamos invertir el array porque viene ordenado de la base de datos
                const historialOrdenado = historial.data;
                
                historialHTML = `
                    <h6 class="mt-4 mb-3">Historial de Pases</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Desde <i class="bi bi-arrow-right"></i></th>
                                    <th>Hacia</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${historialOrdenado.map(pase => `
                                    <tr>
                                        <td>${pase.fecha_formateada}</td>
                                        <td>${pase.lugar_anterior}</td>
                                        <td>${pase.lugar_nuevo}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>`;
            }

            // Mostrar modal con toda la información
            Swal.fire({
                title: `Expediente ${expediente.numero}-${expediente.letra}-${expediente.folio}-${expediente.libro}-${expediente.anio}`,
                html: `
                    <div class="text-start">
                        <p><strong>Iniciador:</strong> ${expediente.iniciador}</p>
                        <p><strong>Extracto:</strong> ${expediente.extracto}</p>


                        <p><strong>Fecha de ingreso:</strong> <span class="badge rounded-pill text-bg-secondary">${
                            expediente.fecha_hora_ingreso
                                ? (() => {
                                    const d = new Date(expediente.fecha_hora_ingreso);
                                    const pad = n => n.toString().padStart(2, '0');
                                    return `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
                                })()
                                : ''
                        }</span></p>

                        <p><strong>Lugar actual:</strong> <span class="badge rounded-pill text-bg-warning">${expediente.lugar}</span></p>




                        
                        ${historialHTML}
                    </div>
                `,
                width: '800px',
                customClass: {
                    htmlContainer: 'swal2-html-container text-start'
                }
            });

        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cargar la información', 'error');
        }
    }

    function confirmarBorrado(id, numero, letra, folio, libro, anio) {
        Swal.fire({
            title: '¿Eliminar expediente?',
            html: `¿Está seguro que desea eliminar el expediente <br><strong>${numero}/${letra}/${folio}/${libro}/${anio}</strong>?<br>Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarExpediente(id);
            }
        });
    }

    async function eliminarExpediente(id) {
        try {
            const response = await fetch('eliminar_expediente.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: 'Eliminado',
                    text: 'El expediente ha sido eliminado correctamente',
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'Error al eliminar el expediente');
            }
        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: error.message,
                icon: 'error'
            });
        }
    }
    </script>
</body>
</html>