<?php
require_once 'IniciadorModel.php';

class IniciadorController {
    private IniciadorModel $model;
    
    public function __construct() {
        session_start();
        $this->model = new IniciadorModel();
    }

    private function redirectToListing() {
        header('Location: listar_iniciadores.php');
        exit;
    }

    private function validateData(array $data, int $id): array {
        $errores = [];

        $apellido = trim($data['apellido'] ?? '');
        $nombre = trim($data['nombre'] ?? '');
        $dni = trim($data['dni'] ?? '');

        if (empty($apellido)) $errores[] = "El apellido es obligatorio";
        if (empty($nombre)) $errores[] = "El nombre es obligatorio";
        if (empty($dni)) $errores[] = "El DNI es obligatorio";
        
        if (!empty($dni) && !$errores && $this->model->checkDuplicateDni($dni, $id)) {
            $errores[] = "Ya existe un iniciador con este DNI";
        }
        
        return $errores;
    }

    public function handleRequest() {
        $iniciador = null;
        $errores = [];
        $actualizado_exitosamente = false;

        // 1. Obtener ID y verificar
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_SESSION['mensaje'] = "ID de iniciador no válido";
            $_SESSION['tipo_mensaje'] = "danger";
            $this->redirectToListing();
        }

        $id = intval($_GET['id']);

        try {
            // 2. Procesar POST (Actualización)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $errores = $this->validateData($_POST, $id);
                
                if (empty($errores)) {
                    if ($this->model->updateIniciador($id, $_POST)) {
                        $actualizado_exitosamente = true;
                        // Forzar a recargar los datos actualizados después del éxito si no redirige.
                        $iniciador = $this->model->getIniciadorById($id); 
                    } else {
                        $errores[] = "Error al actualizar el iniciador";
                    }
                }
            }
            
            // 3. Obtener o recargar datos del iniciador (para GET o si hay errores POST)
            if (!$iniciador) {
                $iniciador = $this->model->getIniciadorById($id);
            }

            if (!$iniciador) {
                $_SESSION['mensaje'] = "Iniciador no encontrado";
                $_SESSION['tipo_mensaje'] = "danger";
                $this->redirectToListing();
            }

        } catch (PDOException $e) {
            $errores[] = "Error de base de datos: " . $e->getMessage();
        }

        // 4. Cargar la vista
        require 'editar_iniciador_view.php';
    }
}