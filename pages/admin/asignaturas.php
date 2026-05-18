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
    <title>Gestion de Asignaturas - <?= APP_NAME ?></title>
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
                    <h1 class="mb-0">Gestion de Asignaturas</h1>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" id="cargas" onclick="openGroupsModal()">
                            <i class="bi bi-collection"></i> Gestionar Cargas
                        </button>
                        <button class="btn btn-primary" onclick="openAsignaturaModal()">
                            <i class="bi bi-plus-circle"></i> Nueva Asignatura
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
                                        <th>Clave</th>
                                        <th>Nombre</th>
                                        <th>Competencias</th>
                                        <th>Descripcion</th>
                                        <th>Gestion</th>
                                    </tr>
                                </thead>
                                <tbody id="asignaturasTable">
                                    <tr><td colspan="5" class="text-center py-4"><div class="spinner-custom"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="structureModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="structureTitle">Estructura de asignatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="structureBody">
                    <div class="text-center py-4"><div class="spinner-border" role="status"></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="competenciasModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="competenciasTitle">Competencias de asignatura</h5>
                        <small class="text-muted">Administra las competencias individuales de esta asignatura.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="competenciasAlert"></div>
                    <form id="competenciaForm" class="border rounded p-3 mb-3">
                        <input type="hidden" id="competenciaId">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label for="competenciaNombre" class="form-label">Competencia</label>
                                <input type="text" class="form-control" id="competenciaNombre" required>
                            </div>
                            <div class="col-md-3">
                                <label for="competenciaInicio" class="form-label">Fecha inicio</label>
                                <input type="date" class="form-control" id="competenciaInicio">
                            </div>
                            <div class="col-md-3">
                                <label for="competenciaFin" class="form-label">Fecha fin</label>
                                <input type="date" class="form-control" id="competenciaFin">
                            </div>
                            <div class="col-md-1 d-grid">
                                <button type="submit" class="btn btn-primary" title="Guardar competencia"><i class="bi bi-save"></i></button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Competencia</th>
                                    <th>Periodo</th>
                                    <th>Entregables</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="competenciasTable">
                                <tr><td colspan="5" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="asignaturaModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="asignaturaForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="asignaturaModalTitle">Nueva Asignatura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="asignaturaId">
                    <div class="mb-3">
                        <label for="clave" class="form-label">Clave</label>
                        <input type="text" class="form-control" id="clave" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripcion</label>
                        <textarea class="form-control" id="descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal fade" id="groupsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cargas de asignaturas por semestre y grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="groupSemestre" class="form-label">Semestre</label>
                            <select class="form-select" id="groupSemestre" onchange="loadSubjectGroups()">
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="groupCode" class="form-label">Grupo</label>
                            <input type="text" class="form-control text-uppercase" id="groupCode" maxlength="20" placeholder="A">
                        </div>
                        <div class="col-md-4">
                            <label for="groupName" class="form-label">Nombre visible</label>
                            <input type="text" class="form-control" id="groupName" placeholder="Ej. 5to A">
                        </div>
                        <div class="col-md-3">
                            <label for="groupPeriod" class="form-label">Periodo</label>
                            <input type="text" class="form-control" id="groupPeriod" placeholder="Ej. Ene-Jun 2026">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="saveSubjectGroup()"><i class="bi bi-save"></i> Guardar</button>
                        </div>
                    </div>
                    <input type="hidden" id="groupId">
                    <div class="row">
                        <div class="col-md-5">
                            <h6>Asignaturas del grupo</h6>
                            <div id="groupSubjects" class="border rounded p-3" style="max-height: 320px; overflow:auto;"></div>
                        </div>
                        <div class="col-md-7">
                            <h6>Cargas registradas</h6>
                            <div id="groupsList"></div>
                        </div>
                    </div>
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
        let asignaturas = [];
        let competencias = [];
        let subjectGroups = [];
        let asignaturaModal;
        let structureModal;
        let competenciasModal;
        let groupsModal;
        let currentAsignatura = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function toDateInput(value) {
            return value ? String(value).substring(0, 10) : '';
        }

        function getEstado(fechaInicio, fechaFin) {
            if (!fechaInicio || !fechaFin) return '<span class="text-muted small">Sin rango</span>';
            const now = new Date();
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            if (now >= inicio && now <= fin) return '<span class="badge bg-success">En rango</span>';
            if (now > fin) return '<span class="badge bg-danger">Vencida</span>';
            return '<span class="badge bg-warning text-dark">Proxima</span>';
        }

        async function loadAsignaturas(page = 1) {
            try {
                const response = await api.get('/asignaturas', { page });
                asignaturas = response.data || [];
                const tbody = document.getElementById('asignaturasTable');
                tbody.innerHTML = '';

                if (asignaturas.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No hay asignaturas</td></tr>';
                    return;
                }

                asignaturas.forEach(asignatura => {
                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${escapeHtml(asignatura.clave || '-')}</strong></td>
                            <td>${escapeHtml(asignatura.nombre)}</td>
                            <td><span class="badge bg-primary">${Number(asignatura.competencias_count || 0)}</span></td>
                            <td><small>${escapeHtml(asignatura.descripcion || '-')}</small></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-primary" onclick="openCompetenciasModal(${asignatura.id})" title="Gestionar competencias"><i class="bi bi-star"></i></button>
                                    <button class="btn btn-outline-info" onclick="viewAsignaturaStructure(${asignatura.id})" title="Ver estructura"><i class="bi bi-diagram-3"></i></button>
                                    <button class="btn btn-outline-secondary" onclick="openAsignaturaModal(${asignatura.id})" title="Editar asignatura"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-danger" onclick="deleteAsignatura(${asignatura.id})" title="Eliminar asignatura"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>`;
                });
            } catch (error) {
                showAlert('#alertContainer', 'danger', 'Error cargando asignaturas: ' + error.message);
            }
        }

        function openAsignaturaModal(id = null) {
            document.getElementById('asignaturaForm').reset();
            document.getElementById('asignaturaId').value = id || '';
            document.getElementById('asignaturaModalTitle').textContent = id ? 'Editar Asignatura' : 'Nueva Asignatura';

            if (id) {
                const asignatura = asignaturas.find(item => item.id === id);
                document.getElementById('clave').value = asignatura?.clave || '';
                document.getElementById('nombre').value = asignatura?.nombre || '';
                document.getElementById('descripcion').value = asignatura?.descripcion || '';
            }

            asignaturaModal.show();
        }

        document.getElementById('asignaturaForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const id = document.getElementById('asignaturaId').value;
            const data = {
                clave: document.getElementById('clave').value.trim() || null,
                nombre: document.getElementById('nombre').value.trim(),
                descripcion: document.getElementById('descripcion').value.trim() || null
            };

            try {
                if (id) await api.put(`/asignaturas/${id}`, data);
                else await api.post('/asignaturas', data);
                asignaturaModal.hide();
                showAlert('#alertContainer', 'success', 'Asignatura guardada correctamente');
                loadAsignaturas();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando asignatura');
            }
        });

        async function openCompetenciasModal(asignaturaId) {
            currentAsignatura = asignaturas.find(item => Number(item.id) === Number(asignaturaId)) || await api.get(`/asignaturas/${asignaturaId}`);
            document.getElementById('competenciasTitle').textContent = `Competencias - ${currentAsignatura.nombre}`;
            resetCompetenciaForm();
            competenciasModal.show();
            await loadCompetenciasByAsignatura();
        }

        async function loadCompetenciasByAsignatura() {
            const tbody = document.getElementById('competenciasTable');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>';

            try {
                const response = await api.get('/competencias', { asignatura_id: currentAsignatura.id, per_page: 100 });
                competencias = response.data || [];
                if (!competencias.length) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Esta asignatura no tiene competencias registradas.</td></tr>';
                    return;
                }

                tbody.innerHTML = competencias.map(competencia => `
                    <tr>
                        <td><strong>${escapeHtml(competencia.nombre)}</strong></td>
                        <td><small>${escapeHtml(toDateInput(competencia.fecha_inicio) || 'Sin inicio')} - ${escapeHtml(toDateInput(competencia.fecha_fin) || 'Sin fin')}</small></td>
                        <td><span class="badge bg-primary">${Number(competencia.deliverables_count || 0)}</span></td>
                        <td>${getEstado(competencia.fecha_inicio, competencia.fecha_fin)}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-outline-info" href="/pages/admin/deliverables.php?competencia_id=${competencia.id}" title="Gestionar entregables"><i class="bi bi-file-earmark"></i></a>
                                <button class="btn btn-outline-secondary" onclick="editCompetencia(${competencia.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-outline-danger" onclick="deleteCompetencia(${competencia.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } catch (error) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-danger text-center py-4">Error cargando competencias.</td></tr>';
            }
        }

        function resetCompetenciaForm() {
            document.getElementById('competenciaForm').reset();
            document.getElementById('competenciaId').value = '';
            document.getElementById('competenciasAlert').innerHTML = '';
        }

        function editCompetencia(id) {
            const competencia = competencias.find(item => Number(item.id) === Number(id));
            if (!competencia) return;
            document.getElementById('competenciaId').value = competencia.id;
            document.getElementById('competenciaNombre').value = competencia.nombre || '';
            document.getElementById('competenciaInicio').value = toDateInput(competencia.fecha_inicio);
            document.getElementById('competenciaFin').value = toDateInput(competencia.fecha_fin);
        }

        document.getElementById('competenciaForm').addEventListener('submit', async event => {
            event.preventDefault();
            const id = document.getElementById('competenciaId').value;
            const data = {
                nombre: document.getElementById('competenciaNombre').value.trim(),
                asignatura_id: currentAsignatura.id,
                fecha_inicio: document.getElementById('competenciaInicio').value || null,
                fecha_fin: document.getElementById('competenciaFin').value || null
            };

            try {
                if (id) await api.put(`/competencias/${id}`, data);
                else await api.post('/competencias', data);
                swalToast('success', 'Competencia guardada');
                resetCompetenciaForm();
                await loadCompetenciasByAsignatura();
                await loadAsignaturas();
            } catch (error) {
                showAlert('#competenciasAlert', 'danger', error.message || 'Error guardando competencia');
            }
        });

        async function deleteCompetencia(id) {
            if (!await confirmAction({ title: 'Eliminar competencia', text: '¿Eliminar esta competencia?', confirmButtonText: 'Si, eliminar' })) return;
            try {
                await api.delete(`/competencias/${id}`);
                swalToast('success', 'Competencia eliminada');
                await loadCompetenciasByAsignatura();
                await loadAsignaturas();
            } catch (error) {
                showAlert('#competenciasAlert', 'danger', error.message || 'Error eliminando competencia');
            }
        }


        function renderSubjectCheckboxes(selectedIds = []) {
            const selected = selectedIds.map(Number);
            const container = document.getElementById('groupSubjects');
            container.innerHTML = asignaturas.map(asignatura => `
                <div class="form-check mb-2">
                    <input class="form-check-input group-subject" type="checkbox" value="${asignatura.id}" id="groupSubject${asignatura.id}" ${selected.includes(Number(asignatura.id)) ? 'checked' : ''}>
                    <label class="form-check-label" for="groupSubject${asignatura.id}">${escapeHtml(asignatura.nombre)}</label>
                </div>`).join('') || '<p class="text-muted mb-0">Primero registra asignaturas.</p>';
        }

        async function openGroupsModal() {
            document.getElementById('groupId').value = '';
            document.getElementById('groupCode').value = '';
            document.getElementById('groupName').value = '';
            document.getElementById('groupPeriod').value = '';
            renderSubjectCheckboxes();
            await loadSubjectGroups();
            groupsModal.show();
        }

        async function loadSubjectGroups() {
            const semester = document.getElementById('groupSemestre').value;
            const list = document.getElementById('groupsList');
            list.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>';

            try {
                subjectGroups = await api.get('/subject-groups', { semestre: semester });
                if (!subjectGroups.length) {
                    list.innerHTML = '<p class="text-muted">No hay cargas registradas para este semestre.</p>';
                    return;
                }

                list.innerHTML = subjectGroups.map(group => `
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between gap-2">
                            <div>
                                <strong>${escapeHtml(group.nombre)}</strong>
                                <span class="badge bg-secondary ms-1">${group.semestre} semestre</span>
                                <span class="badge bg-primary ms-1">Grupo ${escapeHtml(group.grupo || '-')}</span>
                                ${group.periodo ? `<span class="text-muted small ms-1">${escapeHtml(group.periodo)}</span>` : ''}
                                <div class="small text-muted mt-1">${(group.asignaturas || []).map(item => escapeHtml(item.nombre)).join(', ') || 'Sin asignaturas'}</div>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editSubjectGroup(${group.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-outline-danger" onclick="deleteSubjectGroup(${group.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>`).join('');
            } catch (error) {
                list.innerHTML = '<p class="text-danger">Error cargando cargas de asignaturas.</p>';
            }
        }

        function editSubjectGroup(id) {
            const group = subjectGroups.find(item => Number(item.id) === Number(id));
            if (!group) return;
            document.getElementById('groupId').value = group.id;
            document.getElementById('groupSemestre').value = group.semestre;
            document.getElementById('groupCode').value = group.grupo || '';
            document.getElementById('groupName').value = group.nombre || '';
            document.getElementById('groupPeriod').value = group.periodo || '';
            renderSubjectCheckboxes((group.asignaturas || []).map(item => item.id));
        }

        async function saveSubjectGroup() {
            const id = document.getElementById('groupId').value;
            const semester = document.getElementById('groupSemestre').value;
            const groupCode = document.getElementById('groupCode').value.trim().toUpperCase();
            const data = {
                nombre: document.getElementById('groupName').value.trim(),
                semestre: semester,
                grupo: groupCode,
                periodo: document.getElementById('groupPeriod').value.trim() || null,
                asignatura_ids: [...document.querySelectorAll('.group-subject:checked')].map(input => Number(input.value))
            };

            if (!data.grupo) {
                showAlert('#alertContainer', 'danger', 'Ingresa la clave del grupo');
                return;
            }
            if (!data.nombre) data.nombre = `${semester}to ${groupCode}`;

            try {
                if (id) await api.put(`/subject-groups/${id}`, data);
                else await api.post('/subject-groups', data);
                swalToast('success', 'Carga guardada correctamente');
                document.getElementById('groupId').value = '';
                document.getElementById('groupCode').value = '';
                document.getElementById('groupName').value = '';
                document.getElementById('groupPeriod').value = '';
                renderSubjectCheckboxes();
                loadSubjectGroups();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando carga');
            }
        }

        async function deleteSubjectGroup(id) {
            const confirmed = await confirmAction({
                title: 'Eliminar carga',
                text: '¿Eliminar esta carga de asignaturas?',
                confirmButtonText: 'Si, eliminar'
            });
            if (!confirmed) return;

            try {
                await api.delete(`/subject-groups/${id}`);
                swalToast('success', 'Carga eliminada');
                loadSubjectGroups();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error eliminando carga');
            }
        }
        async function deleteAsignatura(id) {
            if (!await confirmAction({ title: 'Eliminar asignatura', text: '¿Eliminar esta asignatura?', confirmButtonText: 'Si, eliminar' })) return;
            try {
                await api.delete(`/asignaturas/${id}`);
                showAlert('#alertContainer', 'success', 'Asignatura eliminada');
                loadAsignaturas();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error eliminando asignatura');
            }
        }

        async function viewAsignaturaStructure(id) {
            document.getElementById('structureTitle').textContent = 'Estructura de asignatura';
            document.getElementById('structureBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';
            structureModal.show();

            try {
                const asignatura = await api.get(`/asignaturas/${id}`);
                document.getElementById('structureTitle').textContent = asignatura.nombre || 'Estructura de asignatura';
                const competencias = asignatura.competencias || [];
                if (!competencias.length) {
                    document.getElementById('structureBody').innerHTML = '<p class="text-muted mb-0">Esta asignatura aun no tiene competencias asignadas.</p>';
                    return;
                }

                document.getElementById('structureBody').innerHTML = competencias.map(competencia => `
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <div>
                                <strong>${escapeHtml(competencia.nombre)}</strong>
                                <div class="small text-muted">
                                    ${escapeHtml(competencia.fecha_inicio || 'Sin inicio')} - ${escapeHtml(competencia.fecha_fin || 'Sin fin')}
                                </div>
                            </div>
                            <a class="btn btn-sm btn-outline-primary" href="/pages/admin/deliverables.php?competencia_id=${competencia.id}">
                                <i class="bi bi-file-earmark"></i> Entregables
                            </a>
                        </div>
                        <div class="mt-3">
                            ${(competencia.deliverables || []).length
                                ? `<ul class="mb-0">${competencia.deliverables.map(item => `<li>${escapeHtml(item.nombre)} <span class="badge bg-secondary">${escapeHtml(item.estado || 'pendiente')}</span></li>`).join('')}</ul>`
                                : '<span class="text-muted small">Sin entregables registrados.</span>'}
                        </div>
                    </div>`).join('');
            } catch (error) {
                document.getElementById('structureBody').innerHTML = '<p class="text-danger mb-0">Error cargando la estructura.</p>';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            asignaturaModal = new bootstrap.Modal(document.getElementById('asignaturaModal'));
            structureModal = new bootstrap.Modal(document.getElementById('structureModal'));
            competenciasModal = new bootstrap.Modal(document.getElementById('competenciasModal'));
            groupsModal = new bootstrap.Modal(document.getElementById('groupsModal'));
            loadAsignaturas();
        });
    </script>
</body>
</html>
