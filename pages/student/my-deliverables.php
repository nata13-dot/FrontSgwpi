<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/validations.php';

if (!is_authenticated() || !is_student()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Entregables - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .badge-calificacion {
            font-size: 1rem;
            padding: 0.5rem 0.8rem;
        }
        .badge-calificacion.alto { background-color: #28a745; }
        .badge-calificacion.medio { background-color: #ffc107; color: #000; }
        .badge-calificacion.bajo { background-color: #dc3545; }
        .card-entregable {
            transition: transform 0.2s;
        }
        .card-entregable:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <h1 class="mb-4">Mis Entregables</h1>

                <div id="alertContainer" class="mb-3"></div>

                <div class="row g-4" id="deliverablesContainer">
                    <div class="col-12 text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando entregables...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Subir Archivo -->
    <div class="modal fade" id="modalSubirArchivo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subir Archivo - <span id="modalDeliverableName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalAlertSubir" class="mb-3"></div>
                    <input type="hidden" id="deliverableIdSubir">
                    <div class="mb-3">
                        <label for="archivoInput" class="form-label">Seleccionar archivo</label>
                        <input type="file" id="archivoInput" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                        <small class="form-text text-muted d-block mt-2">
                            <strong>Máximo 50MB</strong><br>
                            <strong>Permitidos:</strong> PDF, DOC, DOCX, XLS, XLSX, ZIP
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarArchivo()">Subir Archivo</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'https://swapi-production-8341.up.railway.app/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>

    <script>
        let currentDeliverableId = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || '';
        }

        function gradedByName(deliverable) {
            const grader = deliverable.calificadoPor || deliverable.calificado_por;
            return grader && typeof grader === 'object' ? fullName(grader) : 'Sistema';
        }

        async function loadDeliverables() {
            try {
                const response = await api.get('/my-deliverables');
                const container = document.getElementById('deliverablesContainer');
                container.innerHTML = '';

                if (!response.data || response.data.length === 0) {
                    container.innerHTML = '<div class="col-12"><p class="text-center text-muted">No hay entregables asignados</p></div>';
                    return;
                }

                response.data.forEach(deliverable => {
                    const statusBadge = deliverable.estado === 'completado' ? 'success' : 'warning';
                    const statusText = deliverable.estado === 'completado' ? 'Completado' : 'Pendiente';
                    
                    // Calificación
                    let calificacionHTML = '<span class="text-muted small">Sin calificar</span>';
                    if (deliverable.calificacion !== null) {
                        let badgeClass = 'badge-calificacion';
                        if (deliverable.calificacion >= 70) badgeClass += ' alto';
                        else if (deliverable.calificacion >= 50) badgeClass += ' medio';
                        else badgeClass += ' bajo';
                        calificacionHTML = `<span class="badge ${badgeClass}">${deliverable.calificacion}%</span>`;
                    }

                    // Archivo
                    let archivoHTML = '<span class="text-muted small">-</span>';
                    let btnDescargar = '';
                    if (deliverable.file_path) {
                        archivoHTML = '<i class="bi bi-file-earmark text-primary"></i> Presente';
                        btnDescargar = `
                            <button class="btn btn-sm btn-outline-info w-100 mt-2" 
                                    onclick="descargarEntregable(${deliverable.id}, '${deliverable.nombre}')">
                                <i class="bi bi-download"></i> Descargar Archivo
                            </button>
                        `;
                    }

                    // Información de calificación
                    let infoCalificacion = '';
                    if (deliverable.fecha_calificacion) {
                        const fecha = new Date(deliverable.fecha_calificacion);
                        infoCalificacion = `
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-check-circle text-success"></i> 
                                Calificado el ${fecha.toLocaleDateString()} por ${escapeHtml(gradedByName(deliverable))}
                            </small>
                        `;
                    }

                    const card = `
                        <div class="col-lg-6 col-md-12">
                            <div class="card h-100 card-entregable border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0">${escapeHtml(deliverable.nombre)}</h5>
                                        <span class="badge bg-${statusBadge}">${statusText}</span>
                                    </div>
                                    
                                    <p class="card-text text-muted mb-3">${escapeHtml(deliverable.descripcion || 'Sin descripcion')}</p>
                                    
                                    <div class="mb-3 pb-3 border-bottom">
                                        <small class="text-muted d-block mb-2">
                                            <i class="bi bi-folder"></i> ${escapeHtml(deliverable.project?.title || 'N/A')}
                                        </small>
                                        <small class="text-muted d-block">
                                            <i class="bi bi-calendar"></i> 
                                            Creado: ${new Date(deliverable.created_at).toLocaleDateString('es-MX')}
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <strong class="d-block mb-2 small">Calificación:</strong>
                                        ${calificacionHTML}
                                        ${infoCalificacion}
                                    </div>

                                    <div class="mb-3">
                                        <strong class="d-block mb-2 small">Archivo:</strong>
                                        ${archivoHTML}
                                    </div>
                                </div>
                                
                                <div class="card-footer border-0 bg-light">
                                    ${btnDescargar}
                                    <button class="btn btn-sm btn-primary w-100" 
                                            onclick="abrirSubirArchivo(${deliverable.id}, '${deliverable.nombre}')">
                                        <i class="bi bi-cloud-upload"></i> Subir/Actualizar Archivo
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    container.innerHTML += card;
                });
            } catch (error) {
                console.error('Error:', error);
                showAlert('#alertContainer', 'danger', 'Error cargando entregables: ' + error.message);
            }
        }

        function abrirSubirArchivo(deliverableId, nombre) {
            currentDeliverableId = deliverableId;
            document.getElementById('deliverableIdSubir').value = deliverableId;
            document.getElementById('modalDeliverableName').textContent = nombre;
            document.getElementById('archivoInput').value = '';
            document.getElementById('modalAlertSubir').innerHTML = '';
            const modal = new bootstrap.Modal(document.getElementById('modalSubirArchivo'));
            modal.show();
        }

        async function guardarArchivo() {
            const file = document.getElementById('archivoInput').files[0];
            
            if (!file) {
                showAlert('#modalAlertSubir', 'danger', 'Por favor selecciona un archivo');
                return;
            }

            const result = await subirArchivo(currentDeliverableId, file);
            if (result) {
                bootstrap.Modal.getInstance(document.getElementById('modalSubirArchivo')).hide();
                loadDeliverables();
            }
        }

        document.addEventListener('DOMContentLoaded', loadDeliverables);
    </script>
</body>
</html>
