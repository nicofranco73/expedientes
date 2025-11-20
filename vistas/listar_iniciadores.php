
<?php
session_start();

// Headers para evitar caché - CRÍTICO para mostrar nuevos iniciadores en DonWeb
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
    $registros_por_pagina = 10;
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $registros_por_pagina;

    // Búsqueda
    $busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    
    // Inicializar arrays
    $personas_fisicas = [];
    $personas_juridicas = [];
    $concejales = [];
    $iniciadores = [];
    
    // LOG DE DEBUG
    $debug_log = "===== DEBUG BÚSQUEDA INICIADORES =====\n";
    $debug_log .= "Término de búsqueda: '" . $busqueda . "'\n";
    $debug_log .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $debug_log .= "GET['buscar']: " . (isset($_GET['buscar']) ? "YES - valor: '" . $_GET['buscar'] . "'" : "NO") . "\n";

    // Construir parámetro de búsqueda
    $param_busqueda = !empty($busqueda) ? "%$busqueda%" : null;

    // ============================================
    // QUERY 1: PERSONAS FÍSICAS
    // ============================================
    $sql_pf = "SELECT id, apellido, nombre, dni, 'Persona Física' as tipo, 
                      NULL as bloque, NULL as cuit, NULL as cel, NULL as tel, NULL as email,
                      (SELECT COUNT(*) FROM expedientes e 
                       WHERE e.iniciador LIKE CONCAT('%', CONCAT(pf.apellido, ', ', pf.nombre), '%')
                      ) as expedientes_asociados
               FROM persona_fisica pf";
    
    if ($param_busqueda !== null) {
        $sql_pf .= " WHERE LOWER(CONCAT(apellido, ' ', nombre, ' ', dni)) LIKE LOWER(?)";
    }
    
    $sql_pf .= " ORDER BY apellido, nombre LIMIT 100";
    
    try {
        $stmt = $db->prepare($sql_pf);
        if ($param_busqueda !== null) {
            $stmt->execute([$param_busqueda]);
            $debug_log .= "PF Query: " . $sql_pf . " | Param: " . $param_busqueda . "\n";
        } else {
            $stmt->execute();
        }
        $personas_fisicas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $debug_log .= "Personas Físicas: " . count($personas_fisicas) . "\n";
    } catch (Exception $e) {
        $debug_log .= "ERROR PF: " . $e->getMessage() . "\n";
        $personas_fisicas = [];
    }

    // ============================================
    // QUERY 2: PERSONAS JURÍDICAS
    // ============================================
    $sql_pj = "SELECT id, razon_social as apellido, '' as nombre, cuit as dni, 'Persona Jurídica' as tipo, 
                      NULL as bloque, cuit, NULL as cel, NULL as tel, NULL as email,
                      (SELECT COUNT(*) FROM expedientes e 
                       WHERE e.iniciador LIKE CONCAT('%', pj.razon_social, '%')
                      ) as expedientes_asociados
               FROM persona_juri_entidad pj";
    
    if ($param_busqueda !== null) {
        $sql_pj .= " WHERE LOWER(CONCAT(razon_social, ' ', cuit)) LIKE LOWER(?)";
    }
    
    $sql_pj .= " ORDER BY razon_social LIMIT 100";
    
    try {
        $stmt = $db->prepare($sql_pj);
        if ($param_busqueda !== null) {
            $stmt->execute([$param_busqueda]);
            $debug_log .= "PJ Query: " . $sql_pj . " | Param: " . $param_busqueda . "\n";
        } else {
            $stmt->execute();
        }
        $personas_juridicas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $debug_log .= "Personas Jurídicas: " . count($personas_juridicas) . "\n";
    } catch (Exception $e) {
        $debug_log .= "ERROR PJ: " . $e->getMessage() . "\n";
        $personas_juridicas = [];
    }

    // ============================================
    // QUERY 3: CONCEJALES - CRÍTICA PARA TIOZZO
    // ============================================
    $sql_co = "SELECT id, apellido, nombre, '' as dni, 'Concejal' as tipo, 
                      bloque, NULL as cuit, cel, tel, email,
                      (SELECT COUNT(*) FROM expedientes e 
                       WHERE e.iniciador LIKE CONCAT('%', CONCAT(c.apellido, ', ', c.nombre), '%')
                      ) as expedientes_asociados
               FROM concejales c";
    
    if ($param_busqueda !== null) {
        $sql_co .= " WHERE LOWER(CONCAT(apellido, ' ', nombre, ' ', bloque)) LIKE LOWER(?)";
    }
    
    $sql_co .= " ORDER BY apellido, nombre LIMIT 100";
    
    try {
        $stmt = $db->prepare($sql_co);
        if ($param_busqueda !== null) {
            $debug_log .= "CO Query: " . $sql_co . " | Param: " . $param_busqueda . "\n";
            $stmt->execute([$param_busqueda]);
        } else {
            $stmt->execute();
        }
        $concejales = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $debug_log .= "Concejales: " . count($concejales) . "\n";
        
        // Debug específico para Tiozzo
        if ($param_busqueda !== null && $busqueda !== '') {
            $has_tiozzo = false;
            foreach ($concejales as $co) {
                if (stripos($co['apellido'], 'tiozzo') !== false) {
                    $has_tiozzo = true;
                    $debug_log .= "✅ TIOZZO ENCONTRADO: " . json_encode($co, JSON_UNESCAPED_UNICODE) . "\n";
                    break;
                }
            }
            if (!$has_tiozzo) {
                $debug_log .= "⚠️ TIOZZO NO ENCONTRADO en resultados\n";
            }
        }
    } catch (Exception $e) {
        $debug_log .= "ERROR CO: " . $e->getMessage() . "\n";
        $concejales = [];
    }

    // ============================================
    // COMBINAR RESULTADOS
    // ============================================
    $iniciadores = array_merge($personas_fisicas, $personas_juridicas, $concejales);
    $total_registros = count($iniciadores);
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    
    $debug_log .= "TOTAL MERGE: " . $total_registros . " | Paginas: " . $total_paginas . "\n";
    
    // Guardar log
    $log_file = '/home/c2810161/public_html/expedientes/logs/busqueda_debug.log';
    @file_put_contents($log_file, $debug_log . "\n", FILE_APPEND);
    
    // Paginar
    $iniciadores = array_slice($iniciadores, $offset, $registros_por_pagina);} catch (PDOException $e) {
    $_SESSION['mensaje'] = "Error al cargar los iniciadores: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "danger";
    error_log("Error en listar_iniciadores.php: " . $e->getMessage() . " | " . $e->getTraceAsString());
}
?>

<!DOCTYPE html>
<html lang="es">

<body>
    
    
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Listado de Iniciadores</h1>
                    <div>
                        <div class="btn-group me-2">
                            
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="verificar_proteccion_eliminacion.php">
                                    <i class="bi bi-shield-check"></i> Verificar Protecciones
                                </a></li>
                                <li><a class="dropdown-item" href="diagnostico_tabla.php">
                                    <i class="bi bi-bug"></i> Diagnóstico de Base de Datos
                                </a></li>
                            </ul>
                        </div>
                        <a href="acciones_iniciadores.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="carga_iniciador.php" class="btn btn-primary px-4">
                            <i class="bi bi-plus-circle"></i> Nuevo Iniciador
                        </a>
                    </div>
                </div>

                <!-- Mensaje de éxito o error -->
                <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['mensaje'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
                <?php 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
                endif; ?>

                <!-- Buscador -->
                <form class="mb-4">
                    <div class="input-group">
                        <input type="text" 
                               name="buscar" 
                               class="form-control" 
                               placeholder="Buscar por apellido, nombre, DNI, CUIT o bloque..."
                               value="<?= htmlspecialchars($busqueda) ?>">
                        <button class="btn btn-outline-secondary px-4" type="submit">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <?php if (!empty($busqueda)): ?>
                            <a href="listar_iniciadores.php" class="btn btn-outline-warning px-4">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- PANEL DE DEBUG MEJORADO -->
                <?php if (!empty($busqueda)): ?>
                <div class="alert alert-warning mb-3" style="border-left: 4px solid #ff9800;">
                    <strong>🔍 BÚSQUEDA ACTIVA</strong><br>
                    <small>
                        <strong>Término:</strong> "<?= htmlspecialchars($busqueda) ?>"<br>
                        <strong>Resultados por tipo:</strong>
                        <span class="badge bg-primary">👤 PF: <?= count($personas_fisicas) ?></span>
                        <span class="badge bg-info">🏢 PJ: <?= count($personas_juridicas) ?></span>
                        <span class="badge bg-success">🏛️ CO: <?= count($concejales) ?></span>
                        <strong class="ms-3">TOTAL: <?= count($personas_fisicas) + count($personas_juridicas) + count($concejales) ?></strong>
                        
                        <?php if (strtolower($busqueda) === 'tiozzo' || strpos(strtolower($busqueda), 'tiozzo') !== false): ?>
                        <div style="margin-top: 8px; padding: 8px; background: #e3f2fd; border-radius: 3px;">
                            <strong>🎯 BÚSQUEDA DE TIOZZO DETECTADA</strong><br>
                            Concejales encontrados: <span class="badge bg-success"><?= count($concejales) ?></span>
                            <?php if (count($concejales) === 0): ?>
                                <span style="color: red;"><strong>⚠️ SIN RESULTADOS (ERROR!)</strong></span>
                            <?php else: ?>
                                <span style="color: green;">✅ Tiozzo debería aparecer en la tabla abajo</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </small>
                </div>
                <?php endif; ?>

                <!-- Información del total de registros -->
                <div class="alert alert-info mb-3" role="alert">
                    <i class="bi bi-info-circle"></i>
                    <strong>Total de iniciadores encontrados:</strong> 
                    <span class="badge bg-primary fs-6"><?= $total_registros ?></span>
                    <?php if (!empty($busqueda)): ?>
                        <span class="ms-2">para la búsqueda: "<strong><?= htmlspecialchars($busqueda) ?></strong>"</span>
                    <?php endif; ?>
                </div>

                <!-- Tabla de iniciadores -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Apellido y Nombre / Razón Social</th>
                                <th>Documento (DNI/CUIT)</th>
                                <th>Teléfono/Celular</th>
                                <th>Email</th>
                                <th>Expedientes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($iniciadores)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox"></i> No se encontraron iniciadores
                                        <?php if (!empty($busqueda)): ?>
                                            para la búsqueda "<strong><?= htmlspecialchars($busqueda) ?></strong>"
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($iniciadores as $iniciador): ?>
                                <tr>
                                    <td>
                                        <?php 
                                            $tipo = $iniciador['tipo'] ?? 'Desconocido';
                                            if ($tipo === 'Persona Física') {
                                                echo '<span class="badge bg-primary">👤 PF</span>';
                                            } elseif ($tipo === 'Persona Jurídica') {
                                                echo '<span class="badge bg-info">🏢 PJ</span>';
                                            } elseif ($tipo === 'Concejal') {
                                                echo '<span class="badge bg-success">🏛️ CO</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">' . htmlspecialchars($tipo) . '</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($iniciador['apellido'] . (empty($iniciador['nombre']) ? '' : ', ' . $iniciador['nombre'])) ?></strong>
                                        <?php if ($iniciador['tipo'] === 'Concejal' && !empty($iniciador['bloque'])): ?>
                                            <br><small class="text-muted">📍 <?= htmlspecialchars($iniciador['bloque']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $documento = $iniciador['dni'] ?? $iniciador['cuit'] ?? '-';
                                            echo htmlspecialchars($documento);
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $telefonos = [];
                                            if (!empty($iniciador['cel'])) {
                                                $telefonos[] = '<i class="bi bi-phone"></i> ' . htmlspecialchars($iniciador['cel']);
                                            }
                                            if (!empty($iniciador['tel'])) {
                                                $telefonos[] = '<i class="bi bi-telephone"></i> ' . htmlspecialchars($iniciador['tel']);
                                            }
                                            if (empty($telefonos)) {
                                                echo '<span class="text-muted">-</span>';
                                            } else {
                                                echo implode('<br>', $telefonos);
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $email = $iniciador['email'] ?? null;
                                            if (!empty($email)) {
                                                echo '<a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a>';
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($iniciador['expedientes_asociados'] > 0): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-folder"></i> <?= $iniciador['expedientes_asociados'] ?>
                                            </span>
                                            <small class="text-muted d-block">expediente(s)</small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin expedientes</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="verDetalles(<?= $iniciador['id'] ?>)"
                                                    title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="editar_iniciador.php?id=<?= $iniciador['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($iniciador['expedientes_asociados'] > 0): ?>
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        disabled
                                                        title="No se puede eliminar: tiene <?= $iniciador['expedientes_asociados'] ?> expediente(s) asociado(s)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmarEliminar(<?= $iniciador['id'] ?>, '<?= htmlspecialchars($iniciador['apellido'] . (empty($iniciador['nombre']) ? '' : ', ' . $iniciador['nombre'])) ?>')"
                                                        title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegación de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina-1 ?>&buscar=<?= urlencode($busqueda) ?>">Anterior</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= ($pagina == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>&buscar=<?= urlencode($busqueda) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $pagina+1 ?>&buscar=<?= urlencode($busqueda) ?>">Siguiente</a>
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
    function verDetalles(id) {
        // Hacer petición AJAX para obtener los detalles del iniciador
        fetch(`obtener_iniciador_detalles.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const iniciador = data.iniciador;
                    
                    // Construir el HTML con los detalles
                    const detallesHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Datos Personales</h6>
                                <p><strong>Nombre completo:</strong><br>${iniciador.apellido}, ${iniciador.nombre}</p>
                                <p><strong>DNI:</strong> ${iniciador.dni}</p>
                                <p><strong>CUIL:</strong> ${iniciador.cuil || 'No especificado'}</p>
                                <p><strong>Fecha de nacimiento:</strong> ${iniciador.fecha_nacimiento || 'No especificada'}</p>
                                <p><strong>Nacionalidad:</strong> ${iniciador.nacionalidad || 'No especificada'}</p>
                                <p><strong>Estado civil:</strong> ${iniciador.estado_civil || 'No especificado'}</p>
                                <p><strong>Profesión:</strong> ${iniciador.profesion || 'No especificada'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Contacto</h6>
                                <p><strong>Email:</strong> ${iniciador.email || 'No especificado'}</p>
                                <p><strong>Teléfono:</strong> ${iniciador.tel || 'No especificado'}</p>
                                <p><strong>Celular:</strong> ${iniciador.cel || 'No especificado'}</p>
                                
                                <h6 class="text-primary mt-3">Domicilio</h6>
                                <p><strong>Calle:</strong> ${iniciador.calle || 'No especificada'}</p>
                                <p><strong>Número:</strong> ${iniciador.numero || 'No especificado'}</p>
                                <p><strong>Piso:</strong> ${iniciador.piso || 'No especificado'}</p>
                                <p><strong>Departamento:</strong> ${iniciador.depto || 'No especificado'}</p>
                                <p><strong>Localidad:</strong> ${iniciador.localidad || 'No especificada'}</p>
                                <p><strong>Código Postal:</strong> ${iniciador.cp || 'No especificado'}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-primary">Información Adicional</h6>
                                <p><strong>Observaciones:</strong> ${iniciador.observaciones || 'Sin observaciones'}</p>
                                <p><strong>Fecha de registro:</strong> ${iniciador.fecha_creacion || 'No disponible'}</p>
                            </div>
                        </div>
                    `;
                    
                    // Mostrar modal con SweetAlert2
                    Swal.fire({
                        title: `<strong>Detalles del Iniciador</strong>`,
                        html: detallesHtml,
                        width: '800px',
                        showCloseButton: true,
                        showConfirmButton: false,
                        customClass: {
                            htmlContainer: 'text-start'
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'No se pudieron cargar los detalles',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error de conexión al cargar los detalles',
                    icon: 'error'
                });
            });
    }

    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar iniciador?',
            html: `¿Está seguro que desea eliminar a <br><strong>${nombre}</strong>?<br>Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `eliminar_iniciador.php?id=${id}`;
            }
        });
    }

    // ===== SISTEMA DE AUTO-ACTUALIZACIÓN =====
    // Detectar cambios en tiempo real sin refrescar toda la página
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar nuevos iniciadores cada 30 segundos
        setInterval(function() {
            verificarNuevosIniciadores();
        }, 30000); // 30 segundos
        
        // Si el usuario regresa a la pestaña, refrescar inmediatamente
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // La página se hizo visible nuevamente
                verificarNuevosIniciadores();
            }
        });
        
        // Función para verificar si hay nuevos iniciadores
        function verificarNuevosIniciadores() {
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