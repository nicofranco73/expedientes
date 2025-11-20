// js/CargaJuriEntidad.js

document.addEventListener('DOMContentLoaded', function() {

    // --- 1. Lógica de Validación de Bootstrap (Formulario) ---
    (function () {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');

        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // --- 2. Lógica para Mostrar/Ocultar el campo "Otro Tipo" ---
    const tipoSelect = document.getElementById('tipo_entidad');
    const otroContainer = document.getElementById('otro-tipo-container');
    const otroInput = document.getElementById('otro_tipo');
    
    // Función para manejar el estado
    function toggleOtroTipo() {
        if (tipoSelect.value === 'OT') {
            otroContainer.style.display = 'block';
            otroInput.required = true;
        } else {
            otroContainer.style.display = 'none';
            otroInput.required = false;
            // Limpiar el valor si se oculta, para evitar enviar datos innecesarios
            otroInput.value = ''; 
        }
    }

    // A. Evento 'change'
    tipoSelect.addEventListener('change', toggleOtroTipo);

    // B. Comprobación inicial al cargar la página (para el caso de datos pre-cargados por error de sesión)
    toggleOtroTipo();
    
});