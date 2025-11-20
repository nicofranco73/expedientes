// La variable global ORIGINAL_DATA, SESSION_MESSAGE y MESSAGE_TYPE se definen en editar_concejal_view.php

/**
 * Función para restaurar los campos del formulario a sus valores originales
 * obtenidos de la base de datos.
 */
function resetForm() {
    const data = ORIGINAL_DATA;
    
    // Lista de campos a restaurar
    const fields = [
        'apellido', 'nombre', 'dni', 'direccion', 'email', 
        'tel', 'cel', 'bloque', 'observacion'
    ];

    fields.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento && data[campo] !== undefined) {
            elemento.value = data[campo] || '';
            // Limpiar clases de validación de Bootstrap
            elemento.classList.remove('is-invalid', 'is-valid');
        }
    });

    // Remover la clase de validación del formulario
    const form = document.getElementById('form-concejal');
    if (form) {
        form.classList.remove('was-validated');
    }
}

/**
 * Valida un formato de email básico.
 * @param {string} email
 * @returns {boolean}
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Asignar la función de restauración al botón
    const btnRestaurar = document.getElementById('btn-restaurar');
    if (btnRestaurar) {
        btnRestaurar.addEventListener('click', resetForm);
    }
    
    // 2. Validación de formulario de Bootstrap y SweetAlert en envío
    const form = document.getElementById('form-concejal');
    
    if (form) {
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
        }, false);
    }

    // 3. Validación de email en tiempo real (al perder el foco)
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email) { // Solo validar si no está vacío
                if (!isValidEmail(email)) {
                    this.setCustomValidity('Por favor, ingrese un email válido');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid', 'is-valid');
            }
        });
    }

    // 4. Mostrar mensajes de sesión con SweetAlert (provienen de PHP)
    if (SESSION_MESSAGE) {
        if (MESSAGE_TYPE === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Actualización Exitosa!',
                text: SESSION_MESSAGE,
                showCancelButton: true,
                confirmButtonText: 'Ir al Listado',
                cancelButtonText: 'Seguir Editando',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'listar_concejales.php';
                }
            });
        } else if (MESSAGE_TYPE === 'danger' || MESSAGE_TYPE === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Error al Actualizar',
                text: SESSION_MESSAGE,
                confirmButtonColor: '#dc3545',
                footer: '<small>Verifique los datos ingresados e intente nuevamente</small>'
            });
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Información',
                text: SESSION_MESSAGE,
                confirmButtonColor: '#0d6efd'
            });
        }
    }
});