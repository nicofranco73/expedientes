// ====================================================================
// SCRIPT: vistas/actualizarExpedientes_script.js (Ola 3 - Nuevo)
// Contiene el JavaScript específico de la vista actualizarExpedientes.
// ====================================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Inicialización de Select2
    // Se asume que jQuery y Select2 ya fueron cargados en footer.php
    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        $('#iniciador').select2({ 
            theme: 'bootstrap-5', 
            width: '100%', 
            placeholder: 'Seleccione o busque un iniciador...', 
            allowClear: true, 
            language: 'es' 
        });
    } else {
        console.error("jQuery o Select2 no están definidos. Verifique las inclusiones en header/footer.");
    }

    // 2. Contador de caracteres para Extracto
    const extracto = document.getElementById('extracto');
    if (extracto) {
        extracto.addEventListener('input', function() {
            const caracteresActuales = this.value.length;
            const formText = this.nextElementSibling;
            if (formText && formText.classList.contains('form-text')) {
                formText.textContent = `Caracteres: ${caracteresActuales} (sin límite)`;
            }
        });
    }

    // 3. Validación y confirmación del formulario (usando SweetAlert)
    const form = document.querySelector('form');
    if (form && typeof Swal !== 'undefined') { // Verificar que SweetAlert exista
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Desea guardar los cambios?',
                text: 'Verifique que los datos sean correctos',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }

    // 4. Redirección si falta el ID del expediente
    const params = new URLSearchParams(window.location.search);
    if (!params.has('id')) {
        // Usamos setTimeout para asegurar que SweetAlert esté cargado si es asíncrono
        setTimeout(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Error', text: 'No se especificó un expediente para editar', icon: 'error', confirmButtonText: 'Volver' }).then(() => {
                    window.location.href = 'listar_expedientes.php';
                });
            } else {
                 window.location.href = 'listar_expedientes.php';
            }
        }, 100); 
    }
    
    // 5. Función de Ver Historial (Manejador de evento)
    const btnHistorial = document.getElementById('btn-ver-historial');
    if (btnHistorial) {
        btnHistorial.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (id && id !== '0') {
                verHistorial(id);
            } else {
                Swal.fire({ title: 'Error', text: 'ID de expediente no válido.', icon: 'error' });
            }
        });
    }
});

// 6. Función verHistorial con fetch AJAX y sanitización
async function verHistorial(id) {
    if (typeof Swal === 'undefined') { return; } // Salir si SweetAlert no está disponible

    try {
        Swal.fire({ title: 'Cargando historial...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });
        // Simulación: Cambie 'obtener_historial.php' por su URL real
        const response = await fetch(`obtener_historial.php?id=${id}`);
        const resultado = await response.json();
        Swal.close();

        if (!resultado.success) { throw new Error(resultado.message || 'Error al obtener datos del historial.'); }
        if (!resultado.data || resultado.data.length === 0) {
            Swal.fire({ title: 'Sin cambios', text: 'Este expediente no tiene historial de cambios registrados', icon: 'info' });
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Ubicación Anterior</th>
                            <th>Nueva Ubicación</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        resultado.data.forEach(cambio => {
            const fecha = new Date(cambio.fecha_cambio).toLocaleString('es-AR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
            
            // SANITIZACIÓN: Evitar XSS al mostrar contenido dinámico
            const parser = new DOMParser();
            const lugarAnterior = parser.parseFromString(cambio.lugar_anterior || 'N/A', 'text/html').body.textContent;
            const lugarNuevo = parser.parseFromString(cambio.lugar_nuevo || 'N/A', 'text/html').body.textContent;

            html += `
                <tr>
                    <td>${fecha}</td>
                    <td>${lugarAnterior}</td>
                    <td>${lugarNuevo}</td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;

        Swal.fire({
            title: 'Historial de Cambios', html: html, width: '800px', confirmButtonText: 'Cerrar', customClass: { container: 'historial-modal' }
        });

    } catch (error) {
        console.error('Error:', error);
        Swal.fire({ title: 'Error', text: 'No se pudo cargar el historial: ' + error.message, icon: 'error' });
    }
}