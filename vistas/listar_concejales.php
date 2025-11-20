<?php
session_start();

// Headers para evitar caché - CRÍTICO para mostrar nuevos concejales en DonWeb
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require 'header.php';
require 'head.php';

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

    // Búsqueda
    $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    $where = '';
    $params = [];

    if ($buscar !== '') {
        $where = "WHERE apellido LIKE :buscar 
                  OR nombre LIKE :buscar 
                  OR dni LIKE :buscar
                  OR bloque LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }

    // Obtener total de registros
    $sql_total = "SELECT COUNT(*) FROM concejales $where";
    $stmt = $db->prepare($sql_total);
    if ($buscar !== '') {
        $stmt->bindParam(':buscar', $params[':buscar']);
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();
    $total_paginas = ceil($total / $por_pagina);

    // Obtener registros (sin contar expedientes por ahora hasta verificar estructura)
    $sql = "SELECT c.*
            FROM concejales c 
            $where 
            ORDER BY c.apellido, c.nombre 
            LIMIT :offset, :limit";
    
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $por_pagina, PDO::PARAM_INT);
    if ($buscar !== '') {
        $stmt->bindParam(':buscar', $params[':buscar']);
    }
    $stmt->execute();
    $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
}
?>

<!DOCTYPE html>
<html lang="es">

<body>
    
    
    <div class="container-fluid">
        <div class="row">
            <?php require '../vistas/sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Listado de Concejales</h1>
                    <div>
                       
                        <a href="carga_concejal.php" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle"></i> Nuevo Concejal
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show">
                        <?= $_SESSION['mensaje'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
                <?php endif; ?>

                <!-- Buscador -->
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" name="buscar" class="form-control" 
                               placeholder="Buscar por apellido, nombre, DNI o bloque..."
                               value="<?= htmlspecialchars($buscar) ?>">
                        <button class="btn btn-outline-secondary px-4" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </form>

                <!-- Tabla -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Apellido y Nombre</th>
                                <th>DNI</th>
                                <th>Bloque</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($concejales as $concejal): ?>
                            <tr>
                                <td><?= htmlspecialchars($concejal['apellido'] . ', ' . $concejal['nombre']) ?></td>
                                <td><?= htmlspecialchars($concejal['dni']) ?></td>
                                <td><?= htmlspecialchars($concejal['bloque'] ?? '-') ?></td>
                                <td>
                                    <?php if ($concejal['email']): ?>
                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($concejal['email']) ?><br>
                                    <?php endif; ?>
                                    <?php if ($concejal['cel']): ?>
                                        <i class="bi bi-phone"></i> <?= htmlspecialchars($concejal['cel']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="verDetalles(<?= $concejal['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="historial_bloques_concejal.php?id=<?= $concejal['id'] ?>" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Ver historial de bloques">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                        <a href="editar_concejal.php?id=<?= $concejal['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmarEliminar(<?= $concejal['id'] ?>, '<?= htmlspecialchars($concejal['apellido'] . ', ' . $concejal['nombre']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegación de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina-1 ?>&buscar=<?= urlencode($buscar) ?>">Anterior</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($buscar) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina+1 ?>&buscar=<?= urlencode($buscar) ?>">Siguiente</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    function verDetalles(id) {
        // Hacer petición AJAX para obtener detalles del concejal
        fetch(`obtener_concejal.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const concejal = data.concejal;
                    
                    // Construir HTML para mostrar detalles
                    let detallesHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>Datos Personales:</strong></h6>
                                <p><strong>Nombre completo:</strong> ${concejal.apellido}, ${concejal.nombre}</p>
                                <p><strong>DNI:</strong> ${concejal.dni}</p>
                                ${concejal.direccion ? `<p><strong>Dirección:</strong> ${concejal.direccion}</p>` : ''}
                            </div>
                            <div class="col-md-6">
                                <h6><strong>Contacto:</strong></h6>
                                ${concejal.email ? `<p><strong>Email:</strong> ${concejal.email}</p>` : ''}
                                ${concejal.tel ? `<p><strong>Teléfono:</strong> ${concejal.tel}</p>` : ''}
                                ${concejal.cel ? `<p><strong>Celular:</strong> ${concejal.cel}</p>` : ''}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6><strong>Información Política:</strong></h6>
                                ${concejal.bloque ? `<p><strong>Bloque:</strong> ${concejal.bloque}</p>` : '<p><em>Sin bloque asignado</em></p>'}
                                ${concejal.observacion ? `<p><strong>Observaciones:</strong> ${concejal.observacion}</p>` : ''}
                            </div>
                        </div>
                    `;

                    Swal.fire({
                        title: 'Detalles del Concejal',
                        html: detallesHtml,
                        width: '600px',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-pencil"></i> Editar',
                        cancelButtonText: 'Cerrar',
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `editar_concejal.php?id=${id}`;
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudieron cargar los detalles del concejal',
                        confirmButtonColor: '#dc3545'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#dc3545'
                });
            });
    }

    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar concejal?',
            html: `¿Está seguro que desea eliminar a <br><strong>${nombre}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `eliminar_concejal.php?id=${id}`;
            }
        });
    }

    // ===== SISTEMA DE AUTO-ACTUALIZACIÓN =====
    // Detectar cambios en tiempo real sin refrescar toda la página
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar nuevos concejales cada 30 segundos
        setInterval(function() {
            verificarNuevosConcejales();
        }, 30000); // 30 segundos
        
        // Si el usuario regresa a la pestaña, refrescar inmediatamente
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // La página se hizo visible nuevamente
                verificarNuevosConcejales();
            }
        });
        
        // Función para verificar si hay nuevos concejales
        function verificarNuevosConcejales() {
            // Mantener el término de búsqueda actual
            const urlParams = new URLSearchParams(window.location.search);
            let queryString = '';
            
            if (urlParams.has('buscar')) {
                queryString = '?buscar=' + encodeURIComponent(urlParams.get('buscar'));
            }
            
            // Recargar la página si estamos en la primera página
            const paginaActual = urlParams.get('pagina') || '1';
            if (paginaActual === '1') {
                // Forzar recarga sin caché
                window.location.href = window.location.href.split('?')[0] + queryString;
            }
        }
    });
    </script>
</body>
</html>