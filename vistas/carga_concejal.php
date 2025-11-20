<?php
// ======================================================================
// Archivo: carga_concejal.php (Vista COMPLETA y MODIFICADA)
// Objetivo: Formulario para crear un Concejal con Historial de Bloques.
// ======================================================================
session_start();
require 'header.php';
require 'head.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Nuevo Concejal</title>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php 
            // Esto asume que tiene un archivo 'sidebar.php'
            require 'sidebar.php'; 
            
            // Lógica para recuperar datos en caso de error
            $form_data = $_SESSION['form_data'] ?? [];
            $bloques_anteriores_data = $form_data['bloques_anteriores'] ?? [];
            // Limpiamos los datos de sesión para evitar que se muestren de nuevo al recargar
            unset($_SESSION['form_data']);
            ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Nuevo Concejal</h1>
                    <div>
                        <a href="acciones_iniciadores.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="listar_concejales.php" class="btn btn-primary px-4">
                            <i class="bi bi-journal-text"></i> Ver Listado
                        </a>
                    </div>
                </div>

                <form action="procesar_carga_concejal_historial.php" method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person text-primary me-2"></i>
                                        Datos Personales
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="apellido" class="form-label">Apellido *</label>
                                                <input type="text" class="form-control" id="apellido" name="apellido" 
                                                    value="<?= htmlspecialchars($form_data['apellido'] ?? '') ?>" required>
                                                <div class="invalid-feedback">El apellido es obligatorio.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="nombre" class="form-label">Nombre *</label>
                                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                                    value="<?= htmlspecialchars($form_data['nombre'] ?? '') ?>" required>
                                                <div class="invalid-feedback">El nombre es obligatorio.</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="dni" class="form-label">DNI *</label>
                                        <input type="text" class="form-control" id="dni" name="dni" 
                                               placeholder="Ingrese DNI"
                                               value="<?= htmlspecialchars($form_data['dni'] ?? '') ?>" required>
                                        <div class="invalid-feedback">El DNI es obligatorio.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion" 
                                            value="<?= htmlspecialchars($form_data['direccion'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-telephone-fill text-success me-2"></i>
                                        Contacto
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                            value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                                        <div class="invalid-feedback">Por favor, ingrese un email válido.</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel" class="form-label">Teléfono Fijo</label>
                                                <input type="tel" class="form-control" id="tel" name="tel" 
                                                    value="<?= htmlspecialchars($form_data['tel'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cel" class="form-label">Teléfono Celular</label>
                                                <input type="tel" class="form-control" id="cel" name="cel" 
                                                    value="<?= htmlspecialchars($form_data['cel'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-building text-warning me-2"></i>
                                Información Política
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bloque_actual" class="form-label">
                                            <i class="bi bi-star-fill text-warning me-1"></i>
                                            Bloque Actual *
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="bloque_actual" name="bloque_actual" 
                                               placeholder="Ingrese el bloque actual del concejal"
                                               value="<?= htmlspecialchars($form_data['bloque_actual'] ?? '') ?>" required>
                                        <div class="invalid-feedback">El bloque actual es obligatorio.</div>
                                        <div class="form-text">Este será el bloque principal y actual.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_inicio_bloque" class="form-label">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            Fecha de Inicio en el Bloque
                                        </label>
                                        <input type="date" class="form-control" id="fecha_inicio_bloque" name="fecha_inicio_bloque" 
                                               value="<?= htmlspecialchars($form_data['fecha_inicio_bloque'] ?? date('Y-m-d')) ?>">
                                        <div class="form-text">Fecha en que se incorporó al bloque.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="observacion" class="form-label">
                                            <i class="bi bi-chat-text me-1"></i>
                                            Observaciones Generales
                                        </label>
                                        <textarea class="form-control" id="observacion" name="observacion" rows="3"
                                                     placeholder="Observaciones adicionales sobre el concejal"><?= htmlspecialchars($form_data['observacion'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>
                                Historial de Bloques Anteriores
                            </h5>
                            <button type="button" class="btn btn-light btn-sm" id="agregar_bloque_anterior">
                                <i class="bi bi-plus-circle"></i> Agregar Bloque
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="bloques_anteriores" class="row g-3">
                                </div>
                            
                            <p class="text-muted text-center mt-3 mb-0" id="mensaje_sin_bloques">
                                No hay bloques anteriores cargados. Use el botón "Agregar Bloque" para añadir historial.
                            </p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <button type="reset" class="btn btn-outline-secondary px-4 me-2">
                                <i class="bi bi-eraser"></i> Limpiar Campos
                            </button>
                            <a href="acciones_iniciadores.php" class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save"></i> Guardar Concejal
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Inicialización para la gestión de bloques anteriores
        let contadorBloques = 0;
        let bloquesContainer = document.getElementById('bloques_anteriores');
        let mensajeSinBloques = document.getElementById('mensaje_sin_bloques');

        function actualizarMensajeVacio() {
            if (bloquesContainer.children.length === 0) {
                mensajeSinBloques.style.display = 'block';
            } else {
                mensajeSinBloques.style.display = 'none';
            }
        }
        
        // Función para eliminar un bloque
        window.eliminarBloque = function(id) {
            Swal.fire({
                title: '¿Eliminar bloque?',
                text: 'Esta acción eliminará este bloque de historial',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`bloque_${id}`).remove();
                    actualizarMensajeVacio();
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El bloque ha sido eliminado',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }
        
        // Lógica para agregar un bloque
        document.getElementById('agregar_bloque_anterior').addEventListener('click', function() {
            contadorBloques++;
            
            const bloqueHTML = `
                <div class="card border-secondary mb-3" id="bloque_${contadorBloques}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <small class="text-muted">
                            <i class="bi bi-building me-1"></i>
                            Bloque Anterior #${contadorBloques}
                        </small>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarBloque(${contadorBloques})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="nombre_bloque_${contadorBloques}" class="form-label small fw-bold">Nombre del Bloque</label>
                                <input type="text" class="form-control" 
                                        id="nombre_bloque_${contadorBloques}" 
                                        name="bloques_anteriores[${contadorBloques}][nombre]" 
                                        placeholder="Ej: Frente para la Victoria">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="fecha_inicio_${contadorBloques}" class="form-label small fw-bold">Fecha de Inicio</label>
                                <input type="date" class="form-control" 
                                        id="fecha_inicio_${contadorBloques}" 
                                        name="bloques_anteriores[${contadorBloques}][fecha_inicio]">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="fecha_fin_${contadorBloques}" class="form-label small fw-bold">Fecha de Fin</label>
                                <input type="date" class="form-control" 
                                        id="fecha_fin_${contadorBloques}" 
                                        name="bloques_anteriores[${contadorBloques}][fecha_fin]">
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label for="observacion_${contadorBloques}" class="form-label small fw-bold">Observaciones</label>
                                <input type="text" class="form-control form-control-sm" 
                                        id="observacion_${contadorBloques}" 
                                        name="bloques_anteriores[${contadorBloques}][observacion]" 
                                        placeholder="Observaciones sobre este bloque">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            bloquesContainer.insertAdjacentHTML('beforeend', bloqueHTML);
            actualizarMensajeVacio();
            
            // Hacer scroll suave al nuevo bloque
            setTimeout(() => {
                document.getElementById(`bloque_${contadorBloques}`).scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 100);
        });

        // RELLENO DE BLOQUES ANTERIORES EN CASO DE ERROR DE VALIDACIÓN
        const dataRecuperada = <?= json_encode($bloques_anteriores_data) ?>;
        if (Object.keys(dataRecuperada).length > 0) {
            document.addEventListener('DOMContentLoaded', () => {
                for (const key in dataRecuperada) {
                    if (dataRecuperada.hasOwnProperty(key)) {
                        const bloque = dataRecuperada[key];
                        // Simular clic para generar el HTML del bloque con el contador actual
                        document.getElementById('agregar_bloque_anterior').click();
                        const idActual = contadorBloques;
                        
                        // Rellenar los campos con los datos recuperados
                        document.querySelector(`input[name="bloques_anteriores[${idActual}][nombre]"]`).value = bloque.nombre || '';
                        document.querySelector(`input[name="bloques_anteriores[${idActual}][fecha_inicio]"]`).value = bloque.fecha_inicio || '';
                        document.querySelector(`input[name="bloques_anteriores[${idActual}][fecha_fin]"]`).value = bloque.fecha_fin || '';
                        document.querySelector(`input[name="bloques_anteriores[${idActual}][observacion]"]`).value = bloque.observacion || '';
                    }
                }
                actualizarMensajeVacio();
                // Una vez rellenados, aseguramos que el scroll vaya al principio de la sección
                document.getElementById('bloques_anteriores').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        } else {
            // Si no hay datos recuperados, solo mostramos el mensaje de vacío inicial
            document.addEventListener('DOMContentLoaded', actualizarMensajeVacio);
        }


        // Validación de fechas (Fecha Fin > Fecha Inicio)
        document.addEventListener('change', function(e) {
            if (e.target.type === 'date' && e.target.name && e.target.name.includes('fecha_fin')) {
                const bloqueIdMatch = e.target.name.match(/\[(\d+)\]/);
                if (!bloqueIdMatch) return;
                
                const bloqueId = bloqueIdMatch[1];
                const fechaInicio = document.querySelector(`input[name="bloques_anteriores[${bloqueId}][fecha_inicio]"]`);
                
                if (fechaInicio && fechaInicio.value && e.target.value) {
                    if (new Date(e.target.value) <= new Date(fechaInicio.value)) {
                        Swal.fire({
                            title: 'Fecha incorrecta',
                            text: 'La fecha de fin debe ser posterior a la fecha de inicio',
                            icon: 'warning',
                            confirmButtonColor: '#ffc107'
                        });
                        e.target.value = ''; // Limpia el campo con error
                    }
                }
            }
        });

        // Validación de formulario (existente en su código)
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Mostrar mensaje de error con SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Formulario Incompleto',
                            text: 'Por favor, complete todos los campos obligatorios marcados con (*)',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                    form.classList.add('was-validated');
                });
            });
        })();

        // Validación de email en tiempo real (existente en su código)
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !isValidEmail(email)) {
                this.setCustomValidity('Por favor, ingrese un email válido');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // SweetAlert para mensajes de sesión (existente en su código)
        <?php if (isset($_SESSION['mensaje'])): ?>
            <?php 
            $mensaje = $_SESSION['mensaje'];
            $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
            
            unset($_SESSION['mensaje']);
            unset($_SESSION['tipo_mensaje']);
            ?>
            
            <?php if ($tipo === 'success'): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= addslashes($mensaje) ?>',
                showCancelButton: true,
                confirmButtonText: 'Ir al Listado',
                cancelButtonText: 'Crear Otro',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'listar_concejales.php';
                } else {
                    // Limpiar el formulario para crear otro
                    document.querySelector('form').reset();
                    document.querySelector('form').classList.remove('was-validated');
                }
            });
            <?php elseif ($tipo === 'danger' || $tipo === 'error'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error al Guardar',
                text: '<?= addslashes($mensaje) ?>',
                confirmButtonColor: '#dc3545',
                footer: '<small>Verifique los datos ingresados e intente nuevamente</small>'
            });
            <?php else: ?>
            Swal.fire({
                icon: 'info',
                title: 'Información',
                text: '<?= addslashes($mensaje) ?>',
                confirmButtonColor: '#0d6efd'
            });
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>