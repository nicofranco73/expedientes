/**
 * Lógica de Validación en el lado del Cliente para el formulario de Concejal
 * Se utiliza la validación nativa de Bootstrap (HTML5) para los campos requeridos.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Obtener el formulario
    const form = document.getElementById('concejalForm');

    // Escuchador de eventos para el envío del formulario
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            // Evitar doble envío o envío de datos inválidos
        }

        // Agregar la clase 'was-validated' para mostrar los estilos de validación de Bootstrap
        form.classList.add('was-validated');
    }, false);

    // Opcional: Puedes agregar aquí lógica JavaScript más compleja, como:
    // 1. Validación de formato de DNI/CUIT con expresiones regulares.
    // 2. Comprobación de unicidad de DNI usando AJAX (si fuera necesario antes del envío).
    // 3. Manejo de autocompletado para campos como 'partido'.
    
    // Ejemplo de validación de DNI/CUIT (solo números)
    const dniInput = document.getElementById('dni');
    if (dniInput) {
        dniInput.addEventListener('input', function() {
            // Elimina cualquier caracter que no sea un dígito
            this.value = this.value.replace(/\D/g, '');
        });
    }

});