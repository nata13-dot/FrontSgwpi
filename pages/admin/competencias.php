<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || !is_admin()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Competencias - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        <div class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h1 class="mb-0">Gestion de Competencias</h1>
                    <button class="btn btn-primary" onclick="openCompetenciaModal()">
                        <i class="bi bi-plus-circle"></i> Nueva Competencia
                    </button>
                </div>
                <div id="alertContainer"></div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Asignatura</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Entregables</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="competenciasTable">
                                    <tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="competenciaModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="competenciaForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="competenciaModalTitle">Nueva Competencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="competenciaId">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="asignatura_id" class="form-label">Asignatura</label>
                        <select class="form-select" id="asignatura_id">
                            <option value="">Sin asignatura</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'https://apiswgpi-production-0e59.up.railway.app/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    <script>
        let competencias = [];
        let asignaturas = [];
        let competenciaModal;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function toDateInput(value) {
            return value ? String(value).substring(0, 10) : '';
        }

        function getEstado(fechaInicio, fechaFin) {
            if (!fechaInicio || !fechaFin) return '<span class="text-muted small">-</span>';
            const now = new Date();
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            if (now >= inicio && now <= fin) return '<span class="badge bg-success">En rango</span>';
            if (now > fin) return '<span class="badge bg-danger">Vencida</span>';
            return '<span class="badge bg-warning text-dark">Proxima</span>';
        }

        async function loadCatalogs() {
            const response = await api.get('/asignaturas');
            asignaturas = response.data || [];
            const select = document.getElementById('asignatura_id');
            select.innerHTML = '<option value="">Sin asignatura</option>';
            asignaturas.forEach(asignatura => {
                select.innerHTML += `<option value="${asignatura.id}">${escapeHtml(asignatura.nombre)}</option>`;
            });
        }

        async function loadCompetencias(page = 1) {
            try {
                const response = await api.get('/competencias', { page });
                competencias = response.data || [];
                const tbody = document.getElementById('competenciasTable');
                tbody.innerHTML = '';

                if (competencias.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay competencias</td></tr>';
                    return;
                }

                competencias.forEach(competencia => {
                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${escapeHtml(competencia.nombre)}</strong></td>
                            <td>${escapeHtml(competencia.asignatura?.nombre || '-')}</td>
                            <td><small>${competencia.fecha_inicio ? new Date(competencia.fecha_inicio).toLocaleDateString('es-MX') : '-'}</small></td>
                            <td><small>${competencia.fecha_fin ? new Date(competencia.fecha_fin).toLocaleDateString('es-MX') : '-'}</small></td>
                            <td><span class="badge bg-primary">${Number(competencia.deliverables_count || 0)}</span></td>
                            <td>${getEstado(competencia.fecha_inicio, competencia.fecha_fin)}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-outline-info" href="/pages/admin/deliverables.php?competencia_id=${competencia.id}" title="Ver entregables"><i class="bi bi-file-earmark"></i></a>
                                    <button class="btn btn-outline-primary" onclick="openCompetenciaModal(${competencia.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-danger" onclick="deleteCompetencia(${competencia.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>`;
                });
            } catch (error) {
                showAlert('#alertContainer', 'danger', 'Error cargando competencias: ' + error.message);
            }
        }

        function openCompetenciaModal(id = null) {
            document.getElementById('competenciaForm').reset();
            document.getElementById('competenciaId').value = id || '';
            document.getElementById('competenciaModalTitle').textContent = id ? 'Editar Competencia' : 'Nueva Competencia';
            if (id) {
                const competencia = competencias.find(item => item.id === id);
                document.getElementById('nombre').value = competencia?.nombre || '';
                document.getElementById('asignatura_id').value = competencia?.asignatura_id || '';
                document.getElementById('fecha_inicio').value = toDateInput(competencia?.fecha_inicio);
                document.getElementById('fecha_fin').value = toDateInput(competencia?.fecha_fin);
            }
            competenciaModal.show();
        }

        document.getElementById('competenciaForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const id = document.getElementById('competenciaId').value;
            const data = {
                nombre: document.getElementById('nombre').value.trim(),
                asignatura_id: document.getElementById('asignatura_id').value || null,
                fecha_inicio: document.getElementById('fecha_inicio').value || null,
                fecha_fin: document.getElementById('fecha_fin').value || null
            };

            try {
                if (id) await api.put(`/competencias/${id}`, data);
                else await api.post('/competencias', data);
                competenciaModal.hide();
                showAlert('#alertContainer', 'success', 'Competencia guardada correctamente');
                loadCompetencias();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando competencia');
            }
        });

        async function deleteCompetencia(id) {
            if (!await confirmAction({ title: 'Eliminar competencia', text: '¿Eliminar esta competencia?', confirmButtonText: 'Si, eliminar' })) return;
            try {
                await api.delete(`/competencias/${id}`);
                showAlert('#alertContainer', 'success', 'Competencia eliminada');
                loadCompetencias();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error eliminando competencia');
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            competenciaModal = new bootstrap.Modal(document.getElementById('competenciaModal'));
            await loadCatalogs();
            loadCompetencias();
        });
    </script>
</body>
</html>
