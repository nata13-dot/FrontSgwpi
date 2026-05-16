<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/validations.php';

if (!is_authenticated() || !is_teacher()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregas por Calificar - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .badge-calificacion {
            font-size: 1.1rem;
            padding: 0.5rem 0.8rem;
        }
        .badge-calificacion.alto { background-color: #28a745; }
        .badge-calificacion.medio { background-color: #ffc107; color: #000; }
        .badge-calificacion.bajo { background-color: #dc3545; }
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <h1 class="mb-4">Entregas por Calificar</h1>

                <div id="alertContainer" class="mb-3"></div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Entregable</th>
                                        <th>Proyecto</th>
                                        <th>Grupo/Estudiante</th>
                                        <th>Calificación</th>
                                        <th>Archivo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="deliverableTable">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Calificar -->
    <div class="modal fade" id="modalCalificar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Calificar Entregable - <span id="modalDeliverableName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalAlertCalificar" class="mb-3"></div>
                    <input type="hidden" id="deliverableIdCalificar">
                    <div class="mb-3">
                        <label for="calificacionInput" class="form-label">Calificación (0-100)</label>
                        <div class="input-group">
                            <input type="number" id="calificacionInput" class="form-control" min="0" max="100" step="0.01" placeholder="85.5">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="form-text text-muted">Ingresa un valor entre 0 y 100</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarCalificacion()">Calificar</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>

    <script>
        let currentDeliverableId = null;

        async function loadDeliverables() {
            try {
                // Obtener proyectos del docente
                const projectsResponse = await api.get('/my-projects');
                
                if (!projectsResponse.data || projectsResponse.data.length === 0) {
                    document.getElementById('deliverableTable').innerHTML = 
                        '<tr><td colspan="6" class="text-center text-muted">No tienes proyectos asignados</td></tr>';
                    return;
                }

                // Obtener todas las entregas
                const deliverablesResponse = await api.get('/deliverables');
                const tbody = document.getElementById('deliverableTable');
                tbody.innerHTML = '';

                if (!deliverablesResponse.data || deliverablesResponse.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay entregas</td></tr>';
                    return;
                }

                // Filtrar entregas de los proyectos del docente
                const projectIds = projectsResponse.data.map(p => p.id);
                const filteredDeliverables = deliverablesResponse.data.filter(d => 
                    projectIds.includes(d.project_id)
                );

                if (filteredDeliverables.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay entregas en tus proyectos</td></tr>';
                    return;
                }

                filteredDeliverables.forEach(deliverable => {
                    const calificacion = deliverable.calificacion;
                    let badgeClass = '';
                    let badgeText = 'Sin calificar';
                    
                    if (calificacion !== null) {
                        badgeText = calificacion + '%';
                        if (calificacion >= 70) badgeClass = 'badge-calificacion alto';
                        else if (calificacion >= 50) badgeClass = 'badge-calificacion medio';
                        else badgeClass = 'badge-calificacion bajo';
                    }

                    const tieneArchivo = deliverable.file_path ? '<i class="bi bi-file-earmark text-primary"></i>' : '<span class="text-muted">-</span>';

                    let botonesAccion = `
                        <button class="btn btn-sm btn-outline-warning" onclick="abrirCalificar(${deliverable.id}, '${deliverable.nombre}')" 
                                title="Calificar">
                            <i class="bi bi-star"></i>
                        </button>
                    `;

                    if (deliverable.file_path) {
                        botonesAccion += ` <button class="btn btn-sm btn-outline-info" onclick="descargarEntregable(${deliverable.id}, '${deliverable.nombre}')" 
                                title="Descargar">
                            <i class="bi bi-download"></i>
                        </button>`;
                    }

                    const row = `
                        <tr>
                            <td><strong>${deliverable.nombre}</strong></td>
                            <td><small>${deliverable.project?.title || 'N/A'}</small></td>
                            <td><small>${deliverable.grupo || 'N/A'}</small></td>
                            <td>
                                ${calificacion !== null 
                                    ? `<span class="badge ${badgeClass}">${badgeText}</span>` 
                                    : '<span class="text-muted small">-</span>'}
                            </td>
                            <td>${tieneArchivo}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    ${botonesAccion}
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } catch (error) {
                console.error('Error:', error);
                showAlert('#alertContainer', 'danger', 'Error cargando entregas: ' + error.message);
            }
        }

        function abrirCalificar(deliverableId, nombre) {
            currentDeliverableId = deliverableId;
            document.getElementById('deliverableIdCalificar').value = deliverableId;
            document.getElementById('modalDeliverableName').textContent = nombre;
            document.getElementById('calificacionInput').value = '';
            document.getElementById('modalAlertCalificar').innerHTML = '';
            const modal = new bootstrap.Modal(document.getElementById('modalCalificar'));
            modal.show();
        }

        async function guardarCalificacion() {
            const calificacion = document.getElementById('calificacionInput').value.trim();
            
            if (!calificacion) {
                showAlert('#modalAlertCalificar', 'danger', 'Por favor ingresa una calificación');
                return;
            }

            if (!validarCalificacion(calificacion)) {
                showAlert('#modalAlertCalificar', 'danger', 'La calificación debe estar entre 0 y 100');
                return;
            }

            const result = await calificarEntregable(currentDeliverableId, calificacion);
            if (result) {
                bootstrap.Modal.getInstance(document.getElementById('modalCalificar')).hide();
                loadDeliverables();
            }
        }

        document.addEventListener('DOMContentLoaded', () => loadDeliverables());
    </script>
</body>
</html>