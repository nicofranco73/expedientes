<?php
// ====================================================================
// VISTA: vistas/accionesIniciadores_view.php (Ola 2 - Corregido)
// Contiene solo el HTML del contenido principal.
// ====================================================================

/* * 📌 MARCA DE CENTRALIZACIÓN DE ASSETS:
 * * 1. Los CSS globales (Bootstrap, Bootstrap Icons, estilos.css) deben 
 * ser centralizados y llamados en 'header.php'.
 * 2. El JavaScript global (Bootstrap Bundle JS) debe ser centralizado 
 * y llamado en 'footer.php'.
 */
?>

<main class="col-12 col-md-10 ms-sm-auto px-4">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 text-center">
                <h2 class="mb-4">Seleccione la acción</h2>
                
                <div class="row g-4 justify-content-center">
                    
                    <div class="col-12 col-md-5">
                        <a href="carga_iniciador.php" class="text-decoration-none">
                            <div class="card role-card h-100 shadow-sm hover-card">
                                <div class="card-body p-4">
                                    <div class="role-icon mb-3">
                                        <i class="bi bi-person fs-1"></i>
                                    </div>
                                    <h3 class="h4 fw-bold mb-2">Persona Física</h3>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-5">
                        <a href="carga_persona_juri_entidad.php" class="text-decoration-none">
                            <div class="card role-card h-100 shadow-sm hover-card">
                                <div class="card-body p-4">
                                    <div class="role-icon mb-3">
                                        <i class="bi bi-person-vcard fs-1"></i>
                                    </div>
                                    <h3 class="h4 fw-bold mb-2">Persona Jurídica / Entidad</h3>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-5">
                        <a href="carga_concejal.php" class="text-decoration-none">
                            <div class="card role-card h-100 shadow-sm hover-card">
                                <div class="card-body p-4">
                                    <div class="role-icon mb-3">
                                        <i class="bi bi-person-workspace fs-1"></i>
                                    </div>
                                    <h3 class="h4 fw-bold mb-2">Concejal</h3>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>