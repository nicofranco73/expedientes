<?php
// ====================================================================
// VISTA: vistas/actualizarExpedientes_view.php (Ola 3 - Corregido)
// Contiene solo el HTML del contenido principal.
// ====================================================================

/* * 📌 MARCA DE CENTRALIZACIÓN DE ASSETS:
 * * 1. Los CSS globales (Bootstrap, estilos.css) deben estar en 'header.php'.
 * * 2. Los CSS de Select2/Select2-Bootstrap (LINKS) deben estar en 'header.php' 
 * (o al final de <head>) para evitar FOUC.
 * * 3. Los JS de librerías globales (Bootstrap, jQuery, Select2) deben estar en 'footer.php'.
 */
?>

<main class="col-12 col-md-10 ms-sm-auto px-4 main-dashboard">
    <div class="main-box carga">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="titulo-principal text-center">Actualizar Expediente</h1>
            <a href="listar_expedientes.php" class="btn btn-primary px-4">
                <i class="bi bi-list-ul"></i> Listar Expedientes
            </a>
        </div>
        
        <?php 
        // 📌 INCLUSIÓN DE LÓGICA PHP (SweetAlert)
        // Se incluye el script de SweetAlert si existe un mensaje de sesión,
        // generado en el controlador.
        echo $script_sweetalert; 
        ?>
        
        <form action="procesar_actualizacion.php" method="post" autocomplete="off">
            <input type="hidden" name="id" value="<?= htmlspecialchars($expediente['id'] ?? '') ?>">
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="numero" class="form-label">Número</label>
                    <input type="text" id="numero" name="numero" class="form-control" 
                            value="<?= htmlspecialchars($expediente['numero'] ?? '') ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label for="letra" class="form-label">Letra</label>
                    <input type="text" id="letra" name="letra" class="form-control" 
                            value="<?= htmlspecialchars($expediente['letra'] ?? '') ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label for="folio" class="form-label">Folio</label>
                    <input type="text" id="folio" name="folio" class="form-control" 
                            value="<?= htmlspecialchars($expediente['folio'] ?? '') ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label for="anio" class="form-label">Año</label>
                    <input type="text" id="anio" name="anio" class="form-control" 
                            value="<?= htmlspecialchars($expediente['anio'] ?? '') ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label for="lugar" class="form-label">Lugar actual</label>
                    <input type="text" id="lugar" name="lugar" class="form-control" 
                            value="<?= htmlspecialchars($expediente['lugar'] ?? '') ?>" readonly>
                </div>

                <div class="col-12">
                    <label for="extracto" class="form-label">Extracto</label>
                    <textarea id="extracto" name="extracto" class="form-control" 
                            rows="3"><?= htmlspecialchars($expediente['extracto'] ?? '') ?></textarea>
                    <div class="form-text">Sin límite de caracteres (opcional)</div>
                </div>

                <div class="col-12 mb-2">
                    <label for="iniciador" class="form-label">Iniciador</label>
                    <?php if (!$iniciador_id): // Alerta si no se encontró en la BD de iniciadores ?>
                        <div class="alert alert-warning mb-2">
                            <small><strong>Iniciador actual:</strong> <?= htmlspecialchars($expediente['iniciador'] ?? 'N/A') ?></small><br>
                            <small><em>No se pudo encontrar en la base de datos de iniciadores. Seleccione uno nuevo o verifique los datos.</em></small>
                        </div>
                    <?php endif; ?>
                    <select id="iniciador" name="iniciador" class="form-select" required>
                        <option value="">Seleccione un iniciador...</option>
                        
                        <?php if (!$iniciador_id): ?>
                            <option value="<?= htmlspecialchars($expediente['iniciador'] ?? '') ?>" selected>
                                <?= htmlspecialchars($expediente['iniciador'] ?? '') ?> (ACTUAL)
                            </option>
                        <?php endif; ?>
                        
                        <?php if (!empty($personas_fisicas)): ?>
                            <optgroup label="Personas Físicas">
                                <?php foreach ($personas_fisicas as $persona): ?>
                                    <option value="PF-<?= $persona['id'] ?>" <?= ($iniciador_id === 'PF-'.$persona['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($persona['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>

                        <?php if (!empty($personas_juridicas)): ?>
                            <optgroup label="Personas Jurídicas">
                                <?php foreach ($personas_juridicas as $entidad): ?>
                                    <option value="PJ-<?= $entidad['id'] ?>" <?= ($iniciador_id === 'PJ-'.$entidad['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($entidad['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>

                        <?php if (!empty($concejales)): ?>
                            <optgroup label="Concejales">
                                <?php foreach ($concejales as $concejal): ?>
                                    <option value="CO-<?= $concejal['id'] ?>" <?= ($iniciador_id === 'CO-'.$concejal['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($concejal['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <div class="invalid-feedback">Por favor seleccione un iniciador</div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <div>
                    <a href="listar_expedientes.php" class="btn btn-secondary px-4">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    
                    <button type="button" class="btn btn-info text-white px-4" id="btn-ver-historial" data-id="<?= htmlspecialchars($expediente['id'] ?? '0') ?>">
                        <i class="bi bi-clock-history"></i> Ver Historial
                    </button>
                </div>
                
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</main>