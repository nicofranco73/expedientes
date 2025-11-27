// vistas/includes/pases_expediente_scripts.js

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Lógica del campo "Otro lugar" ---
    const lugarSelect = document.getElementById('lugar_nuevo');
    const otroLugarContainer = document.getElementById('otro_lugar_container');
    const otroLugarTexto = document.getElementById('otro_lugar_texto');
    const lugarNuevoFinal = document.getElementById('lugar_nuevo_final');
    const formPase = document.getElementById('formPase');

    function actualizarCampoLugar() {
        if (lugarSelect.value === 'Otro') {
            otroLugarContainer.style.display = 'block';
            otroLugarTexto.setAttribute('required', 'required');
            lugarNuevoFinal.value = otroLugarTexto.value;
        } else {
            otroLugarContainer.style.display = 'none';
            otroLugarTexto.removeAttribute('required');
            lugarNuevoFinal.value = lugarSelect.value;
        }
    }

    // Event listeners
    lugarSelect.addEventListener('change', actualizarCampoLugar);
    otroLugarTexto.addEventListener('input', () => {
        lugarNuevoFinal.value = otroLugarTexto.value;
    });

    // Inicializar el valor oculto y el estado del campo
    actualizarCampoLugar(); 
    
    // Al enviar el formulario, asegurar el valor final
    formPase.addEventListener('submit', function(e) {
        actualizarCampoLugar();
        // Puedes agregar más validaciones aquí si es necesario
    });
    
    // --- Lógica de ordenamiento de tabla (se mantendría) ---
    const table = document.getElementById('historialTable');
    if (table) {
        const headers = table.querySelectorAll('th.sortable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        let sortColumn = 0;
        let sortDirection = 'desc'; // Inicialmente ordenado por fecha descendente

        // Función de comparación (se simplifica aquí, el código original es más complejo)
        function compare(rowA, rowB, index, type, direction) {
            const cellA = rowA.children[index];
            const cellB = rowB.children[index];
            let valA = cellA.getAttribute('data-sort') || cellA.textContent.trim();
            let valB = cellB.getAttribute('data-sort') || cellB.textContent.trim();

            if (type === 'numeric') {
                valA = parseFloat(valA);
                valB = parseFloat(valB);
            } else if (type === 'date') {
                 // date-type usa timestamp en data-sort
                valA = parseInt(valA, 10);
                valB = parseInt(valB, 10);
            } else {
                valA = valA.toLowerCase();
                valB = valB.toLowerCase();
            }

            let comparison = 0;
            if (valA > valB) {
                comparison = 1;
            } else if (valA < valB) {
                comparison = -1;
            }
            
            return direction === 'asc' ? comparison : comparison * -1;
        }

        headers.forEach(header => {
            header.addEventListener('click', function() {
                const column = parseInt(this.getAttribute('data-column'));
                const type = this.getAttribute('data-type');
                
                if (sortColumn === column) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = column;
                    sortDirection = type === 'date' ? 'desc' : 'asc';
                }

                // Limpiar clases
                headers.forEach(h => h.classList.remove('asc', 'desc'));

                // Aplicar clase
                this.classList.add(sortDirection);

                // Ordenar filas
                rows.sort((rowA, rowB) => compare(rowA, rowB, sortColumn, type, sortDirection));

                // Reinsertar filas
                rows.forEach(row => tbody.appendChild(row));
            });
            
            // Inicializar la clase de ordenamiento inicial (si la tabla tiene contenido)
            if (parseInt(header.getAttribute('data-column')) === sortColumn && rows.length > 0) {
                 header.classList.add(sortDirection);
            }
        });
    }
    
    // --- Lógica de SweetAlert para confirmación de eliminación ---
    window.confirmarEliminacion = function(paseId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminarlo!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí se realizaría la llamada AJAX o la redirección al script de eliminación
                // Por ejemplo: window.location.href = 'eliminar_pase.php?id=' + paseId;
                Swal.fire(
                    'Eliminado!',
                    'El pase ha sido eliminado.',
                    'success'
                );
            }
        });
    }

});