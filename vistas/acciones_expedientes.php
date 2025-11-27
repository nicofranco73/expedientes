<?php
session_start();
 require '../vistas/sidebar.php'; 
require 'header.php';
require 'head.php';
require 'vistas/publico/css/custom.css';
?>
<!DOCTYPE html>
<html lang="es">

<body class="bg-light">
    
    <div class="container-fluid">
        <div class="row">
           

            <main class="col-12 col-md-10 ms-sm-auto px-4">
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-8 text-center">
                            <h2 class="mb-4">Seleccione la acción</h2>
                            
                            <div class="row g-4 justify-content-center">
                                <div class="col-12 col-md-5">
                                    <a href="carga_expedientes.php" class="text-decoration-none">
                                        <div class="card role-card h-100 shadow-sm hover-card">
                                            <div class="card-body p-4">
                                                <div class="role-icon mb-3">
                                                    <i class="bi bi-plus-circle fs-1"></i>
                                                </div>
                                                <h3 class="h4 fw-bold mb-2">Nuevo Expediente</h3>
                                                <p class="text-secondary mb-0">
                                                    Cargar un nuevo expediente al sistema
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-12 col-md-5">
                                    <a href="actualizar_expedientes.php" class="text-decoration-none">
                                        <div class="card role-card h-100 shadow-sm hover-card">
                                            <div class="card-body p-4">
                                                <div class="role-icon mb-3">
                                                    <i class="bi bi-list-task fs-1"></i>
                                                </div>
                                                <h3 class="h4 fw-bold mb-2">Listar Expedientes</h3>
                                                <p class="text-secondary mb-0">
                                                    Ver todos los expedientes existentes
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>


</html>