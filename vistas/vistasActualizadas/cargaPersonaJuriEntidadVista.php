<?php
// view/CargaJuriEntidad.view.php
// La vista recibe los datos a través de la variable $data (definida en el controlador).

// Se asume que 'head.php' y 'sidebar.php' contienen la estructura HTML inicial y el sidebar.
require 'head.php'; // Encabezado de la página (CSS, meta, etc.)

// Extracción de datos para simplificar el uso en el HTML
$form_data = $data['form_data'];
$mensaje = $data['mensaje'];
$tipo_mensaje = $data['tipo_mensaje'];
$title = $data['title'];
?>

<!DOCTYPE html>
<html lang="es">

<body>
    <div class="container-fluid">
        <div class="row">
            <?php require 'sidebar.php'; // Barra lateral ?>
            
            <main class="col-12 col-md-10 ms-sm-auto px-4">
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1><?= htmlspecialchars($title) ?></h1>
                    <div>
                        <a href="acciones_iniciadores.php" class="btn btn-secondary px-4 me-2">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <a href="listar_persona_juri_entidad.php" class="btn btn-primary px-4">
                            <i class="bi bi-journal-text"></i> Ver Listado
                        </a>
                    </div>
                </div>

                <form action="procesar_carga_entidad.php" method="POST" class="needs-validation" novalidate id="form-entidad">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-building text-primary me-2"></i>
                                        Datos de la Entidad
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="razon_social" class="form-label">Razón Social (Nombre)</label>
                                        <input type="text" class="form-control" id="razon_social" name="razon_social" 
                                               value="<?= htmlspecialchars($form_data['razon_social'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="cuit" class="form-label">CUIT</label>
                                                <input type="text" class="form-control" id="cuit" name="cuit" 
                                                       placeholder="Ingrese CUIT"
                                                       value="<?= htmlspecialchars($form_data['cuit'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="personeria" class="form-label">Nº Personería Jurídica</label>
                                                <input type="text" class="form-control" id="personeria" name="personeria" 
                                                       value="<?= htmlspecialchars($form_data['personeria'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tipo_entidad" class="form-label">Tipo de Entidad *</label>
                                        <select class="form-select" id="tipo_entidad" name="tipo_entidad" required>
                                            <option value="">Seleccionar...</option>
                                            <?php 
                                            // Array de Tipos de Entidad (idealmente cargado desde el controlador)
                                            $tipos = [
                                                'SA' => 'Sociedad Anónima', 'SR' => 'Sociedad de Responsabilidad Limitada',
                                                'AS' => 'Sociedad por Acciones Simplificada', 'SC' => 'Sociedad Colectiva',
                                                'CS' => 'Sociedad en Comandita Simple', 'CP' => 'Sociedad en Comandita por Acciones',
                                                'SE' => 'Sociedad del Estado', 'SP' => 'Sociedad Anónima con Participación Estatal Mayoritaria (SAPEM)',
                                                'EU' => 'Empresa Unipersonal', 'MO' => 'Monotributista / Autónomo',
                                                'AC' => 'Asociación Civil', 'FU' => 'Fundación', 'CO' => 'Cooperativa',
                                                'MU' => 'Mutual', 'SI' => 'Sindicato', 'FE' => 'Federación',
                                                'CF' => 'Confederación', 'UT' => 'Unión Transitoria de Empresas',
                                                'AI' => 'Agrupación de Interés Económico', 'EN' => 'Entidad sin Fines de Lucro',
                                                'ON' => 'Organización No Gubernamental', 'COOPR' => 'Consorcio de Copropietarios',
                                                'MIN' => 'Ministerio', 'SEC' => 'Secretaría', 'MUN' => 'Municipalidad',
                                                'COM' => 'Comisión Municipal', 'CD' => 'Concejo Deliberante',
                                                'OD' => 'Organismo Descentralizado / Ente Autárquico', 'EP' => 'Empresa Pública',
                                                'CL' => 'Club Deportivo', 'ADE' => 'Asociación Deportiva',
                                                'FDE' => 'Federación Deportiva', 'LDE' => 'Liga Deportiva / Liga Barrial',
                                                'ACD' => 'Asociación de Clubes', 'CC' => 'Cámara de Comercio',
                                                'CI' => 'Colegio de Ingenieros', 'CM' => 'Colegio de Médicos',
                                                'CA' => 'Colegio de Abogados', 'IN' => 'Instituto',
                                                'UN' => 'Universidad', 'ES' => 'Escuela', 'JI' => 'Jardín de Infantes',
                                                'ET' => 'Escuela Técnica', 'CE' => 'Centro Educativo',
                                                'ITS' => 'Instituto Terciario / Superior', 'CEI' => 'Centro de Investigación',
                                                'ACA' => 'Academia', 'CES' => 'Consejo Escolar', 'HO' => 'Hospital',
                                                'SN' => 'Sanatorio', 'CLN' => 'Clínica', 'CX' => 'Centro de Salud',
                                                'CCOM' => 'Centro Comunitario', 'CREH' => 'Centro de Rehabilitación',
                                                'RGA' => 'Residencia Geriátrica / Hogar de Ancianos', 'CCO' => 'Comedor Comunitario',
                                                'IG' => 'Iglesia', 'PA' => 'Parroquia', 'TEM' => 'Templo',
                                                'CAP' => 'Capilla', 'HER' => 'Hermandad / Cofradía', 'BP' => 'Biblioteca Popular',
                                                'CCU' => 'Centro Cultural', 'TEI' => 'Teatro Independiente',
                                                'AVE' => 'Asociación Vecinal / Centro Vecinal', 'SF' => 'Sociedad de Fomento',
                                                'OT' => 'Otro'
                                            ];
                                            $selected_tipo = $form_data['tipo_entidad'] ?? '';
                                            foreach ($tipos as $code => $name) {
                                                $selected = ($selected_tipo === $code) ? 'selected' : '';
                                                echo "<option value=\"{$code}\" {$selected}>{$name}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="otro-tipo-container" style="display: <?= ($selected_tipo === 'OT') ? 'block' : 'none' ?>;">
                                        <label for="otro_tipo" class="form-label">Especificar otro tipo</label>
                                        <input type="text" class="form-control" id="otro_tipo" name="otro_tipo" 
                                               placeholder="Ingrese el tipo de entidad"
                                               value="<?= htmlspecialchars($form_data['otro_tipo'] ?? '') ?>"
                                               <?= ($selected_tipo === 'OT') ? 'required' : '' ?>>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="web" class="form-label">Página Web</label>
                                        <input type="url" class="form-control" id="web" name="web" 
                                               placeholder="https://"
                                               value="<?= htmlspecialchars($form_data['web'] ?? '') ?>">
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
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel_fijo" class="form-label">Teléfono Fijo</label>
                                                <input type="tel" class="form-control" id="tel_fijo" name="tel_fijo" 
                                                       value="<?= htmlspecialchars($form_data['tel_fijo'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tel_celular" class="form-label">Teléfono Celular</label>
                                                <input type="tel" class="form-control" id="tel_celular" name="tel_celular" 
                                                       value="<?= htmlspecialchars($form_data['tel_celular'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-house-fill text-info me-2"></i>
                                        Domicilio
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="domicilio" class="form-label">Domicilio *</label>
                                        <input type="text" class="form-control" id="domicilio" name="domicilio" 
                                               value="<?= htmlspecialchars($form_data['domicilio'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label for="localidad" class="form-label">Localidad *</label>
                                                <input type="text" class="form-control" id="localidad" name="localidad" 
                                                       value="<?= htmlspecialchars($form_data['localidad'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="provincia" class="form-label">Provincia *</label>
                                                <input type="text" class="form-control" id="provincia" name="provincia" 
                                                       value="<?= htmlspecialchars($form_data['provincia'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person-badge text-warning me-2"></i>
                                Representante Legal
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rep_nombre" class="form-label">Nombre y Apellido</label>
                                        <input type="text" class="form-control" id="rep_nombre" name="rep_nombre" 
                                               value="<?= htmlspecialchars($form_data['rep_nombre'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rep_documento" class="form-label">Número de Documento</label>
                                        <input type="text" class="form-control" id="rep_documento" name="rep_documento" 
                                               value="<?= htmlspecialchars($form_data['rep_documento'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rep_cargo" class="form-label">Cargo</label>
                                        <select class="form-select" id="rep_cargo" name="rep_cargo">
                                            <option value="">Seleccionar...</option>
                                            <?php 
                                            // Array de Cargos (idealmente cargado desde el controlador)
                                            $cargos = [
                                                'PR' => 'Presidente', 'VP' => 'Vicepresidente', 'SE' => 'Secretario',
                                                'TE' => 'Tesorero', 'DI' => 'Director', 'GE' => 'Gerente',
                                                'AP' => 'Apoderado', 'AD' => 'Administrador', 'SY' => 'Síndico',
                                                'RE' => 'Rector', 'DE' => 'Decano', 'CO' => 'Coordinador'
                                            ];
                                            $selected_cargo = $form_data['rep_cargo'] ?? '';
                                            foreach ($cargos as $code => $name) {
                                                $selected = ($selected_cargo === $code) ? 'selected' : '';
                                                echo "<option value=\"{$code}\" {$selected}>{$name}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rep_tel_fijo" class="form-label">Teléfono Fijo</label>
                                        <input type="tel" class="form-control" id="rep_tel_fijo" name="rep_tel_fijo" 
                                               value="<?= htmlspecialchars($form_data['rep_tel_fijo'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="rep_tel_celular" class="form-label">Teléfono Celular</label>
                                        <input type="tel" class="form-control" id="rep_tel_celular" name="rep_tel_celular" 
                                               value="<?= htmlspecialchars($form_data['rep_tel_celular'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rep_domicilio" class="form-label">Domicilio</label>
                                        <input type="text" class="form-control" id="rep_domicilio" name="rep_domicilio" 
                                               value="<?= htmlspecialchars($form_data['rep_domicilio'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rep_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="rep_email" name="rep_email" 
                                               value="<?= htmlspecialchars($form_data['rep_email'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
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
                            <i class="bi bi-save"></i> Guardar Entidad
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/CargaJuriEntidad.js"></script>

    <script>
        // Esta parte del SweetAlert debe permanecer en la vista porque usa variables PHP
        <?php if ($mensaje): ?>
            Swal.fire({
                icon: '<?= $tipo_mensaje === 'danger' || $tipo_mensaje === 'error' ? 'error' : 'success' ?>',
                title: '<?= $tipo_mensaje === 'danger' || $tipo_mensaje === 'error' ? 'Error' : '¡Éxito!' ?>',
                text: '<?= addslashes($mensaje) ?>',
                <?php if ($tipo_mensaje === 'success'): ?>
                    showCancelButton: true,
                    confirmButtonText: 'Ir al Listado',
                    cancelButtonText: 'Crear Otra',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d'
                <?php else: ?>
                    confirmButtonColor: '<?= $tipo_mensaje === 'danger' || $tipo_mensaje === 'error' ? '#dc3545' : '#0d6efd' ?>'
                <?php endif; ?>
            }).then((result) => {
                if (result.isConfirmed && '<?= $tipo_mensaje ?>' === 'success') {
                    window.location.href = 'listar_persona_juri_entidad.php';
                } else if (!result.isConfirmed && result.dismiss !== Swal.DismissReason.cancel && '<?= $tipo_mensaje ?>' === 'success') {
                     // Si el usuario presiona "Crear Otra" (cancel) o cierra la modal.
                    document.getElementById('form-entidad').reset();
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>