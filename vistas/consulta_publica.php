<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Consulta Pública de Expedientes</h1>
</div>

<div class="p-4 bg-white border rounded shadow-sm">
    <p class="lead">Ingrese el número de expediente para rastrear su estado y ubicación actual.</p>
    
    <!-- Formulario de Búsqueda -->
    <form class="row g-3 align-items-center mb-5">
        <div class="col-auto">
            <label for="numeroExpediente" class="visually-hidden">Número de Expediente</label>
            <input type="text" class="form-control form-control-lg" id="numeroExpediente" placeholder="Ej: 2025-001234" required>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-search me-2"></i> Buscar
            </button>
        </div>
    </form>

    <!-- Área de Resultados (Oculta hasta la búsqueda) -->
    <div id="resultadoConsulta" class="mt-4 alert alert-info text-center" style="display: none;">
        <p>Los resultados de la consulta aparecerán aquí.</p>
    </div>
</div>