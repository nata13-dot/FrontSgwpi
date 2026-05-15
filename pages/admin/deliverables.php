<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Entregables - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .badge-calificacion { font-size: 0.95rem; padding: 0.45rem 0.65rem; }
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
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="mb-0">Gestion de Entregables</h1>
                        <div id="activeFilter" class="small text-muted mt-1"></div>
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-primary" href="/pages/admin/deliverables.php" id="clearFilterBtn" style="display:none">
                            <i class="bi bi-x-circle"></i> Quitar filtro
                        </a>
                        <button class="btn btn-primary" onclick="openDeliverableModal()">
                            <i class="bi bi-plus-circle"></i> Nuevo Entregable
                        </button>
                    </div>
                </div>
                <div id="alertContainer"></div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Proyecto</th>
                                        <th>Competencia</th>
                                        <th>Calificacion</th>
                                        <th>Archivo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="deliverablesTable">
                                    <tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deliverableModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="deliverableForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="deliverableModalTitle">Nuevo Entregable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="deliverableId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_id" class="form-label">Proyecto</label>
                            <select class="form-select" id="project_id"></select>
                            <div class="form-text">Opcional. El alcance principal se toma desde la competencia y su materia.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="competencia_id" class="form-label">Competencia</label>
                            <select class="form-select" id="competencia_id" required>
                                <option value="">Selecciona una competencia</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripcion</label>
                        <textarea class="form-control" id="descripcion" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tipo_documento" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo_documento">
                                <option value="documento">Documento</option>
                                <option value="reporte">Reporte</option>
                                <option value="video">Video</option>
                                <option value="presentacion">Presentacion</option>
                                <option value="codigo">Codigo</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado">
                                <option value="pendiente">Pendiente</option>
                                <option value="enviado">Enviado</option>
                                <option value="revisado">Revisado</option>
                                <option value="aprobado">Aprobado</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="rama_asociada" class="form-label">Rama asociada</label>
                            <input type="text" class="form-control" id="rama_asociada">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="autores" class="form-label">Autores</label>
                        <input type="text" class="form-control" id="autores">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalCalificar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Calificar Entregable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="deliverableIdCalificar">
                    <label for="calificacionInput" class="form-label">Calificacion (0-100)</label>
                    <input type="number" id="calificacionInput" class="form-control" min="0" max="100" step="0.01">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarCalificacion()">Calificar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSubirArchivo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subir Archivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="deliverableIdSubir">
                    <label for="archivoInput" class="form-label">Seleccionar archivo</label>
                    <input type="file" id="archivoInput" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                    <small class="form-text text-muted">Max. 50MB. PDF, DOC, DOCX, XLS, XLSX, ZIP</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarArchivo()">Subir</button>
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
        let deliverables = [];
        let projects = [];
        let competencias = [];
        let deliverableModal;
        let modalCalificar;
        let modalSubirArchivo;
        let currentDeliverableId = null;
        const urlParams = new URLSearchParams(window.location.search);
        const competenciaFilter = urlParams.get('competencia_id');

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        async function loadCatalogs() {
            const [projectsResponse, competenciasResponse] = await Promise.all([
                api.get('/projects'),
                api.get('/competencias')
            ]);
            projects = projectsResponse.data || [];
            competencias = competenciasResponse.data || [];

            const projectSelect = document.getElementById('project_id');
            projectSelect.innerHTML = '<option value="">Selecciona un proyecto</option>';
            projects.forEach(project => projectSelect.innerHTML += `<option value="${project.id}">${escapeHtml(project.title)}</option>`);

            const competenciaSelect = document.getElementById('competencia_id');
            competenciaSelect.innerHTML = '<option value="">Selecciona una competencia</option>';
            competencias.forEach(competencia => {
                const subject = competencia.asignatura?.nombre ? ` - ${competencia.asignatura.nombre}` : '';
                competenciaSelect.innerHTML += `<option value="${competencia.id}">${escapeHtml(competencia.nombre + subject)}</option>`;
            });

            if (competenciaFilter) {
                const competencia = competencias.find(item => Number(item.id) === Number(competenciaFilter));
                document.getElementById('activeFilter').innerHTML = competencia
                    ? `Mostrando entregables de la competencia <strong>${escapeHtml(competencia.nombre)}</strong>`
                    : 'Mostrando entregables filtrados por competencia';
                document.getElementById('clearFilterBtn').style.display = '';
            }
        }

        async function loadDeliverables(page = 1) {
            try {
                const params = { page };
                if (competenciaFilter) params.competencia_id = competenciaFilter;
                const response = await api.get('/deliverables', params);
                deliverables = response.data || [];
                const tbody = document.getElementById('deliverablesTable');
                tbody.innerHTML = '';

                if (deliverables.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay entregables</td></tr>';
                    return;
                }

                deliverables.forEach(deliverable => {
                    const calificacion = deliverable.calificacion;
                    let badgeClass = '';
                    let badgeText = 'Sin calificar';
                    if (calificacion !== null && calificacion !== undefined) {
                        badgeText = calificacion + '%';
                        if (calificacion >= 70) badgeClass = 'badge-calificacion alto';
                        else if (calificacion >= 50) badgeClass = 'badge-calificacion medio';
                        else badgeClass = 'badge-calificacion bajo';
                    }

                    const tieneArchivo = deliverable.archivo_path
                        ? '<i class="bi bi-file-earmark text-primary"></i>'
                        : '<span class="text-muted">-</span>';
                    const downloadButton = deliverable.archivo_path
                        ? `<button class="btn btn-outline-info" onclick="descargarEntregable(${deliverable.id}, '${escapeHtml(deliverable.nombre)}')" title="Descargar"><i class="bi bi-download"></i></button>`
                        : '';

                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${escapeHtml(deliverable.nombre)}</strong></td>
                            <td>${escapeHtml(deliverable.project?.title || 'N/A')}</td>
                            <td>${escapeHtml(deliverable.competencia?.nombre || '-')}</td>
                            <td>${calificacion !== null && calificacion !== undefined ? `<span class="badge ${badgeClass}">${badgeText}</span>` : '<span class="text-muted small">-</span>'}</td>
                            <td>${tieneArchivo}</td>
                            <td><span class="badge bg-secondary">${escapeHtml(deliverable.estado || 'pendiente')}</span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="openDeliverableModal(${deliverable.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-warning" onclick="abrirCalificar(${deliverable.id})" title="Calificar"><i class="bi bi-star"></i></button>
                                    ${downloadButton}
                                    <button class="btn btn-outline-success" onclick="abrirSubirArchivo(${deliverable.id})" title="Subir archivo"><i class="bi bi-cloud-upload"></i></button>
                                    <button class="btn btn-outline-danger" onclick="deleteDeliverable(${deliverable.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>`;
                });
            } catch (error) {
                showAlert('#alertContainer', 'danger', 'Error cargando entregables: ' + error.message);
            }
        }

        function openDeliverableModal(id = null) {
            document.getElementById('deliverableForm').reset();
            document.getElementById('deliverableId').value = id || '';
            document.getElementById('deliverableModalTitle').textContent = id ? 'Editar Entregable' : 'Nuevo Entregable';
            document.getElementById('estado').value = 'pendiente';
            document.getElementById('tipo_documento').value = 'documento';
            if (competenciaFilter) document.getElementById('competencia_id').value = competenciaFilter;

            if (id) {
                const deliverable = deliverables.find(item => item.id === id);
                document.getElementById('project_id').value = deliverable?.project_id || '';
                document.getElementById('competencia_id').value = deliverable?.competencia_id || '';
                document.getElementById('nombre').value = deliverable?.nombre || '';
                document.getElementById('descripcion').value = deliverable?.descripcion || '';
                document.getElementById('tipo_documento').value = deliverable?.tipo_documento || 'documento';
                document.getElementById('estado').value = deliverable?.estado || 'pendiente';
                document.getElementById('rama_asociada').value = deliverable?.rama_asociada || '';
                document.getElementById('autores').value = deliverable?.autores || '';
            }
            deliverableModal.show();
        }

        document.getElementById('deliverableForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const id = document.getElementById('deliverableId').value;
            const data = {
                project_id: document.getElementById('project_id').value || null,
                competencia_id: document.getElementById('competencia_id').value || null,
                nombre: document.getElementById('nombre').value.trim(),
                descripcion: document.getElementById('descripcion').value.trim() || null,
                tipo_documento: document.getElementById('tipo_documento').value,
                estado: document.getElementById('estado').value,
                rama_asociada: document.getElementById('rama_asociada').value.trim() || null,
                autores: document.getElementById('autores').value.trim() || null
            };

            try {
                if (id) await api.put(`/deliverables/${id}`, data);
                else await api.post('/deliverables', data);
                deliverableModal.hide();
                showAlert('#alertContainer', 'success', 'Entregable guardado correctamente');
                loadDeliverables();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando entregable');
            }
        });

        async function deleteDeliverable(id) {
            if (!await confirmAction({ title: 'Eliminar entregable', text: '¿Eliminar este entregable?', confirmButtonText: 'Si, eliminar' })) return;
            try {
                await api.delete(`/deliverables/${id}`);
                showAlert('#alertContainer', 'success', 'Entregable eliminado');
                loadDeliverables();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error eliminando entregable');
            }
        }

        function abrirCalificar(id) {
            currentDeliverableId = id;
            document.getElementById('calificacionInput').value = '';
            modalCalificar.show();
        }

        async function guardarCalificacion() {
            const calificacion = document.getElementById('calificacionInput').value.trim();
            const result = await calificarEntregable(currentDeliverableId, calificacion);
            if (result) {
                modalCalificar.hide();
                loadDeliverables();
            }
        }

        function abrirSubirArchivo(id) {
            currentDeliverableId = id;
            document.getElementById('archivoInput').value = '';
            modalSubirArchivo.show();
        }

        async function guardarArchivo() {
            const file = document.getElementById('archivoInput').files[0];
            if (!file) {
                showAlert('#alertContainer', 'danger', 'Selecciona un archivo');
                return;
            }
            const result = await subirArchivo(currentDeliverableId, file);
            if (result) {
                modalSubirArchivo.hide();
                loadDeliverables();
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            deliverableModal = new bootstrap.Modal(document.getElementById('deliverableModal'));
            modalCalificar = new bootstrap.Modal(document.getElementById('modalCalificar'));
            modalSubirArchivo = new bootstrap.Modal(document.getElementById('modalSubirArchivo'));
            await loadCatalogs();
            loadDeliverables();
        });
    </script>
</body>
</html>
