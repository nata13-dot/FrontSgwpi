<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || (!is_admin() && !is_teacher())) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluaciones - <?= APP_NAME ?></title>
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
                    <div>
                        <h1 class="mb-1">Evaluaciones</h1>
                        <p class="text-muted mb-0">Rubricas unicas por semestre, con comentarios opcionales</p>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if (is_admin()): ?>
                        <button class="btn btn-outline-primary" onclick="openEvaluationManagersModal()">
                            <i class="bi bi-person-gear"></i> Responsable de evaluaciones
                        </button>
                        <?php endif; ?>
                        <?php if (is_evaluation_manager()): ?>
                        <button class="btn btn-outline-primary" onclick="openRubricModal()">
                            <i class="bi bi-list-check"></i> Gestionar Rubrica
                        </button>
                        <button class="btn btn-outline-primary" onclick="openRoomsModal()">
                            <i class="bi bi-door-open"></i> Salas
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label" for="projectFilter">Proyecto</label>
                        <select class="form-select" id="projectFilter" onchange="loadEvaluations()">
                            <option value="">Todos los proyectos</option>
                        </select>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Proyecto</th>
                                        <th>Semestre</th>
                                        <th>Etapa</th>
                                        <th>Sala</th>
                                        <th>Fecha</th>
                                        <th>Resultado</th>
                                        <th>Promedio global</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="evaluationsTable">
                                    <tr><td colspan="8" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="evaluationModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="evaluationForm">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Evaluacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="evaluation_room_id">Sala configurada</label>
                        <select class="form-select" id="evaluation_room_id" onchange="applyRoomToEvaluationForm()">
                            <option value="">Sin sala configurada</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="project_id">Proyecto</label>
                        <select class="form-select" id="project_id" required></select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="semestre">Semestre</label>
                            <select class="form-select" id="semestre" required>
                                <option value="5">5 - Propuesta</option>
                                <option value="6">6 - Avance</option>
                                <option value="7">7 - Avance</option>
                                <option value="8">8 - Titulacion</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="sala">Sala</label>
                            <input type="text" class="form-control" id="sala" placeholder="Sala 1">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="fecha_exposicion">Fecha de exposicion</label>
                        <input type="datetime-local" class="form-control" id="fecha_exposicion">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="rubricModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestionar Rubrica por Semestre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-3">
                            <label class="form-label" for="rubricSemester">Semestre</label>
                            <select class="form-select" id="rubricSemester" onchange="loadRubricCriteria()">
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label" for="newCriterionText">Nueva pregunta</label>
                            <input type="text" class="form-control" id="newCriterionText" maxlength="255">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" onclick="addCriterion()">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                    <div id="rubricCriteriaList" style="max-height: 60vh; overflow:auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="scoreModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="scoreForm">
                <div class="modal-header">
                    <h5 class="modal-title">Rubrica de Evaluacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="scoreEvaluationId">
                    <div id="scoreFields"></div>
                    <div class="border rounded p-3 mt-3">
                        <label class="form-label fw-semibold" for="generalEvaluationComment">Comentarios o puntos de mejora</label>
                        <textarea class="form-control" id="generalEvaluationComment" rows="3" placeholder="Comentario final visible en el desglose"></textarea>
                    </div>
                    <div class="border rounded p-3 mt-3 d-none" id="titulationAptBox">
                        <label class="form-label fw-semibold">¿El proyecto es apto para titulacion?</label>
                        <select class="form-select" id="apto_titulacion">
                            <option value="">Sin respuesta</option>
                            <option value="1">Si</option>
                            <option value="0">No</option>
                        </select>
                        <div class="form-text">Esta respuesta no afecta el puntaje general.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Rubrica</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="roomsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-door-open"></i> Salas de Evaluacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <div class="border rounded p-3">
                                <input type="hidden" id="roomId">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label">Sala</label><input class="form-control" id="roomName" placeholder="Sala 1" required><div class="invalid-feedback">Nombre obligatorio y no repetido.</div></div>
                                    <div class="col-md-6"><label class="form-label">Salon</label><input class="form-control" id="roomClassroom" placeholder="Salon/Laboratorio" required></div>
                                    <div class="col-md-4"><label class="form-label">Semestre</label><select class="form-select" id="roomSemester" onchange="loadRoomProjects()"><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option></select></div>
                                    <div class="col-md-8"><label class="form-label">Fecha</label><input type="datetime-local" class="form-control" id="roomDate" required onchange="updateRoomAvailability()"><div class="form-text">Debe ser posterior a la fecha y hora actual.</div></div>
                                    <div class="col-md-4"><label class="form-label">Min. docentes</label><input type="number" class="form-control" id="teacherMinutes" min="1" max="240" value="15"></div>
                                    <div class="col-md-4"><label class="form-label">Min. proyecto</label><input type="number" class="form-control" id="presentationMinutes" min="1" max="240" value="20"></div>
                                    <div class="col-md-4"><label class="form-label">Oportunidades</label><input type="number" class="form-control" id="maxAttempts" min="1" max="10" value="1"></div>
                                    <div class="col-12"><label class="form-label">Docentes</label><div class="border rounded p-2" id="roomTeachers" style="max-height: 160px; overflow:auto;"></div></div>
                                    <div class="col-12"><label class="form-label">Responsable de sala</label><select class="form-select" id="responsibleTeacher"><option value="">Selecciona primero docentes</option></select></div>
                                    <div class="col-12"><label class="form-label">Proyectos</label><div class="border rounded p-2" id="roomProjects" style="max-height: 220px; overflow:auto;"></div></div>
                                    <div class="col-12"><div class="small text-muted" id="roomAvailabilityHint"></div></div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button class="btn btn-outline-secondary" onclick="resetRoomForm()">Limpiar</button>
                                    <button class="btn btn-primary" onclick="saveRoom()"><i class="bi bi-save"></i> Guardar sala</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7" id="roomsList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (is_admin()): ?>
    <div class="modal fade" id="evaluationManagersModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-gear"></i> Responsable de evaluaciones</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="evaluationManagersAlert"></div>
                    <p class="text-muted small">Selecciona uno o mas docentes para que tengan disponible la gestion completa de evaluaciones en su perfil.</p>
                    <div class="border rounded p-2" id="evaluationManagersList" style="max-height: 360px; overflow:auto;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveEvaluationManagersBtn" onclick="saveEvaluationManagers()"><i class="bi bi-save"></i> Guardar responsables</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="breakdownModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Desglose por Docente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="breakdownContent"></div>
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
        const IS_ADMIN = <?= is_admin() ? 'true' : 'false' ?>;
        const CAN_MANAGE_EVALUATIONS = <?= is_evaluation_manager() ? 'true' : 'false' ?>;
    </script>
    <script>
        let projects = [];
        let roomProjects = [];
        let teachers = [];
        let rooms = [];
        let evaluations = [];
        let criteria = [];
        let criteriaBySemester = {};
        let levels = [];
        let evaluationModal;
        let rubricModal;
        let scoreModal;
        let breakdownModal;
        let roomsModal;
        let evaluationManagersModal;
        let evaluationManagerIds = [];
        let evaluationsRealtimeTimer = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || '';
        }

        function projectActiveAuthors(project) {
            const students = Array.isArray(project?.students) ? project.students : [];
            return students.map(student => fullName(student)).filter(Boolean).join(', ');
        }

        function stageLabel(stage) {
            return { propuesta: 'Propuesta', avance: 'Avance', titulacion: 'Titulacion' }[stage] || stage;
        }

        function groupCriteria() {
            criteriaBySemester = criteria.reduce((groups, criterion) => {
                if (!groups[criterion.semestre]) groups[criterion.semestre] = [];
                groups[criterion.semestre].push(criterion);
                return groups;
            }, {});
        }

        async function refreshCriteria() {
            const criteriaResponse = await api.get('/evaluations/criteria', { _cache_ttl: 60000 });
            criteria = criteriaResponse.criteria || [];
            levels = criteriaResponse.levels || levels;
            groupCriteria();
        }

        async function loadInitialData() {
            const [projectsResponse] = await Promise.all([
                api.get('/evaluations/projects', { _cache_ttl: 30000 }),
                refreshCriteria()
            ]);
            projects = projectsResponse || [];
            if (CAN_MANAGE_EVALUATIONS) {
                const [adminsResponse, teachersResponse] = await Promise.all([
                    api.get('/users', { perfil_id: 1, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 }),
                    api.get('/users', { perfil_id: 2, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 })
                ]);
                teachers = [...(adminsResponse.data || []), ...(teachersResponse.data || [])]
                    .sort((a, b) => fullName(a).localeCompare(fullName(b)));
            }
            await loadRooms();

            const projectFilter = document.getElementById('projectFilter');
            const projectSelect = document.getElementById('project_id');
            projectFilter.innerHTML = '<option value="">Todos los proyectos</option>';
            projectSelect.innerHTML = '<option value="">Selecciona un proyecto</option>';
            projects.forEach(project => {
                const option = `<option value="${project.id}">${escapeHtml(project.title)}</option>`;
                projectFilter.innerHTML += option;
                projectSelect.innerHTML += option;
            });
            renderRoomOptions();
        }

        async function openEvaluationManagersModal() {
            if (!IS_ADMIN) return;
            if (!evaluationManagersModal) evaluationManagersModal = new bootstrap.Modal(document.getElementById('evaluationManagersModal'));
            document.getElementById('evaluationManagersAlert').innerHTML = '';
            document.getElementById('evaluationManagersList').innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>';
            evaluationManagersModal.show();

            try {
                const response = await api.get('/evaluation-managers');
                evaluationManagerIds = (response.manager_ids || []).map(String);
                const availableTeachers = response.teachers || teachers;
                const orderedTeachers = [...availableTeachers].sort((a, b) => {
                    const aSelected = evaluationManagerIds.includes(String(a.id)) ? 0 : 1;
                    const bSelected = evaluationManagerIds.includes(String(b.id)) ? 0 : 1;
                    if (aSelected !== bSelected) return aSelected - bSelected;
                    return fullName(a).localeCompare(fullName(b), 'es', { sensitivity: 'base' });
                });
                document.getElementById('evaluationManagersList').innerHTML = orderedTeachers.map(teacher => `
                    <div class="form-check border-bottom py-2">
                        <input class="form-check-input evaluation-manager-check" type="checkbox" value="${escapeHtml(teacher.id)}" id="evaluationManager${escapeHtml(teacher.id)}" ${evaluationManagerIds.includes(String(teacher.id)) ? 'checked' : ''}>
                        <label class="form-check-label" for="evaluationManager${escapeHtml(teacher.id)}">
                            <strong>${escapeHtml(fullName(teacher))}</strong>
                            <span class="text-muted small d-block">${escapeHtml(teacher.id)}${teacher.email ? ' · ' + escapeHtml(teacher.email) : ''}</span>
                        </label>
                    </div>
                `).join('') || '<p class="text-muted mb-0">No hay docentes activos.</p>';
            } catch (error) {
                document.getElementById('evaluationManagersAlert').innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message || 'Error cargando responsables')}</div>`;
                document.getElementById('evaluationManagersList').innerHTML = '';
            }
        }

        async function saveEvaluationManagers() {
            const teacherIds = [...document.querySelectorAll('.evaluation-manager-check:checked')].map(input => input.value);
            const button = document.getElementById('saveEvaluationManagersBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

            try {
                const response = await api.put('/evaluation-managers', { teacher_ids: teacherIds });
                evaluationManagerIds = (response.manager_ids || []).map(String);
                document.getElementById('evaluationManagersAlert').innerHTML = '<div class="alert alert-success">Responsables actualizados. Los docentes veran la opcion al volver a iniciar sesion.</div>';
            } catch (error) {
                document.getElementById('evaluationManagersAlert').innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message || 'Error guardando responsables')}</div>`;
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        function renderRoomOptions() {
            document.getElementById('evaluation_room_id').innerHTML = '<option value="">Sin sala configurada</option>' + rooms.map(room => `<option value="${room.id}">${escapeHtml(room.nombre)} · ${escapeHtml(room.salon || 'Sin salon')} · ${room.semestre}</option>`).join('');
        }

        function normalizeRoomName(value) {
            const clean = String(value || '').trim();
            if (/^sala\s+\d+/i.test(clean)) return clean;
            const number = clean.match(/\d+/)?.[0] || (rooms.length + 1);
            return `Sala ${number}`;
        }

        function normalizeClassroom(value) {
            const clean = String(value || '').trim().toUpperCase();
            if (/^EB\d+/.test(clean)) return clean;
            const number = clean.match(/\d+/)?.[0] || '';
            return number ? `EB${number}` : clean;
        }

        function hasOpenEvaluationModal() {
            return Boolean(document.querySelector('.modal.show'));
        }

        function startEvaluationsRealtime() {
            clearInterval(evaluationsRealtimeTimer);
            evaluationsRealtimeTimer = setInterval(async () => {
                if (document.hidden || hasOpenEvaluationModal()) return;
                await refreshEvaluationLiveData();
            }, 5000);
        }

        async function refreshEvaluationLiveData() {
            await Promise.all([loadRooms(false, true), loadEvaluations(false, true)]);
            renderRoomOptions();
        }

        async function loadEvaluations(showLoading = true, fresh = false) {
            const projectId = document.getElementById('projectFilter').value;
            const params = projectId ? { project_id: projectId } : {};
            if (fresh) params._fresh = 1;
            const tbody = document.getElementById('evaluationsTable');
            if (showLoading) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>';
            }
            const response = await api.get('/evaluations', params);
            evaluations = response.data || [];
            tbody.innerHTML = '';

            if (evaluations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay evaluaciones</td></tr>';
                return;
            }

            let lastRoomKey = null;
            evaluations.forEach(evaluation => {
                const roomKey = evaluation.room?.id || 'sin-sala';
                if (roomKey !== lastRoomKey) {
                    lastRoomKey = roomKey;
                    const room = evaluation.room;
                    tbody.innerHTML += `
                        <tr class="table-light">
                            <td colspan="8">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <strong>${escapeHtml(room?.nombre || 'Sin sala')}</strong>
                                    <span class="text-muted small">${escapeHtml(room?.salon || '-')} · Responsable: ${escapeHtml(fullName(room?.responsible_teacher) || '-')}</span>
                                </div>
                            </td>
                        </tr>`;
                }
                const averageColor = evaluation.global_average_color || 'secondary';
                const disabled = evaluation.can_score_now ? '' : 'disabled';
                const statusClass = evaluation.evaluation_badge_color
                    ? `bg-${evaluation.evaluation_badge_color}`
                    : ({ activo: 'bg-primary', evaluado: 'bg-success', pendiente: 'bg-secondary' }[evaluation.sequence_status] || 'bg-secondary');
                const evaluatedClass = evaluation.evaluated_by_all ? 'table-success' : '';
                const evaluatedBadge = evaluation.evaluated_by_all ? '<span class="badge bg-success ms-2"><i class="bi bi-check2-circle"></i> Evaluado por todos</span>' : '';
                tbody.innerHTML += `
                    <tr class="${evaluatedClass}">
                        <td><span class="badge ${statusClass} me-1">#${evaluation.presentation_order || '-'}</span>${escapeHtml(evaluation.project?.title || 'N/A')}${evaluatedBadge}</td>
                        <td>${evaluation.semestre}</td>
                        <td>${stageLabel(evaluation.etapa)}</td>
                        <td>${escapeHtml(evaluation.sala || '-')}</td>
                        <td>${evaluation.fecha_exposicion ? new Date(evaluation.fecha_exposicion).toLocaleString('es-MX') : '-'}</td>
                        <td><span class="badge bg-secondary">${escapeHtml(evaluation.resultado)}</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-${averageColor}" onclick="showBreakdown(${evaluation.id})">
                                ${evaluation.global_average}% · ${evaluation.evaluators_count}/${evaluation.expected_evaluators_count || 0} evaluadores
                            </button>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-success" onclick="openScoreModal(${evaluation.id})" title="Evaluar" ${disabled}><i class="bi bi-clipboard-check"></i></button>
                                ${(evaluation.can_manage_evaluations || evaluation.is_room_responsible) ? `<button class="btn btn-outline-primary" onclick="askAdvanceRoom(${evaluation.evaluation_room_id})" title="Finalizar y continuar"><i class="bi bi-skip-forward"></i></button>` : ''}
                                ${evaluation.can_manage_evaluations ? `<button class="btn btn-outline-danger" onclick="deleteEvaluation(${evaluation.id})" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                            </div>
                        </td>
                    </tr>`;
            });
        }

        function openEvaluationModal() {
            document.getElementById('evaluationForm').reset();
            evaluationModal.show();
        }

        function applyRoomToEvaluationForm() {
            const room = rooms.find(item => String(item.id) === document.getElementById('evaluation_room_id').value);
            if (!room) return;
            document.getElementById('semestre').value = room.semestre;
            document.getElementById('sala').value = room.nombre;
            document.getElementById('fecha_exposicion').value = room.fecha_evaluacion ? room.fecha_evaluacion.slice(0, 16) : '';
            document.getElementById('project_id').innerHTML = '<option value="">Selecciona un proyecto</option>' + (room.projects || []).map(project => `<option value="${project.id}">${escapeHtml(project.title)}</option>`).join('');
        }

        document.getElementById('evaluationForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const data = {
                project_id: document.getElementById('project_id').value,
                evaluation_room_id: document.getElementById('evaluation_room_id').value || null,
                semestre: document.getElementById('semestre').value,
                sala: document.getElementById('sala').value.trim() || null,
                fecha_exposicion: document.getElementById('fecha_exposicion').value || null,
                estado: 'programada',
                resultado: 'pendiente'
            };
            try {
                await api.post('/evaluations', data);
                evaluationModal.hide();
                showAlert('#alertContainer', 'success', 'Evaluacion creada correctamente');
                loadEvaluations();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error creando evaluacion');
            }
        });

        function openRubricModal() {
            if (!CAN_MANAGE_EVALUATIONS) return;
            document.getElementById('rubricSemester').value = '5';
            loadRubricCriteria();
            rubricModal.show();
        }

        async function loadRooms(renderAfterLoad = true, fresh = false) {
            rooms = await api.get('/evaluations/rooms', fresh ? { _fresh: 1 } : {});
            if (renderAfterLoad && roomsModal?._isShown) renderRooms();
        }

        async function openRoomsModal() {
            if (!CAN_MANAGE_EVALUATIONS) return;
            await loadRooms();
            renderRooms();
            renderRoomTeachers();
            await loadRoomProjects();
            roomsModal.show();
        }

        function normalizedRoomDate(value) {
            return value ? String(value).replace('T', ' ').slice(0, 13) : '';
        }

        function conflictingRoomsForCurrentForm() {
            const currentRoomId = document.getElementById('roomId').value;
            const selectedDate = normalizedRoomDate(document.getElementById('roomDate').value);
            if (!selectedDate) return [];

            return rooms.filter(room => {
                const isSameRecord = currentRoomId && String(room.id) === String(currentRoomId);
                return !isSameRecord && normalizedRoomDate(room.fecha_evaluacion) === selectedDate;
            });
        }

        function busyRoomIds() {
            const conflicts = conflictingRoomsForCurrentForm();
            return {
                teachers: new Set(conflicts.flatMap(room => (room.teachers || []).map(teacher => String(teacher.id)))),
                projects: new Set(conflicts.flatMap(room => (room.projects || []).map(project => Number(project.id)))),
                rooms: conflicts
            };
        }

        async function updateRoomAvailability() {
            const selectedTeachers = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            const selectedProjects = [...document.querySelectorAll('.room-project:checked')].map(input => Number(input.value));
            renderRoomTeachers(selectedTeachers);
            await loadRoomProjects(selectedProjects);
        }

        function renderRoomTeachers(selected = []) {
            const selectedIds = selected.map(String);
            const busy = busyRoomIds();
            const availableTeachers = teachers.filter(teacher => !busy.teachers.has(String(teacher.id)));
            document.getElementById('roomTeachers').innerHTML = availableTeachers.map(teacher => `
                <div class="form-check">
                    <input class="form-check-input room-teacher" type="checkbox" value="${escapeHtml(teacher.id)}" id="roomTeacher${escapeHtml(teacher.id)}" ${selectedIds.includes(String(teacher.id)) ? 'checked' : ''} onchange="refreshResponsibleTeacherOptions()">
                    <label class="form-check-label" for="roomTeacher${escapeHtml(teacher.id)}">${escapeHtml(fullName(teacher))} <span class="text-muted small">${Number(teacher.perfil_id) === 1 ? 'Administrativo' : 'Docente'}</span></label>
                </div>`).join('') || '<p class="text-muted mb-0">No hay evaluadores disponibles para esta fecha y hora.</p>';
            refreshResponsibleTeacherOptions();
            renderAvailabilityHint(busy);
        }

        function refreshResponsibleTeacherOptions(selected = null) {
            const current = selected ?? document.getElementById('responsibleTeacher')?.value ?? '';
            const checked = [...document.querySelectorAll('.room-teacher:checked')].map(input => String(input.value));
            const available = teachers.filter(teacher => checked.includes(String(teacher.id)));
            document.getElementById('responsibleTeacher').innerHTML = '<option value="">Sin responsable</option>' + available.map(teacher => `
                <option value="${escapeHtml(teacher.id)}">${escapeHtml(fullName(teacher))}</option>
            `).join('');
            if (current) document.getElementById('responsibleTeacher').value = current;
        }

        async function loadRoomProjects(selected = [], orderMap = {}) {
            const semester = document.getElementById('roomSemester').value;
            roomProjects = await api.get('/evaluations/projects', { semestre: semester, _cache_ttl: 30000 });
            const selectedIds = selected.map(Number);
            const busy = busyRoomIds();
            const availableProjects = roomProjects.filter(project => !busy.projects.has(Number(project.id)));
            document.getElementById('roomProjects').innerHTML = availableProjects.map(project => `
                <div class="d-flex align-items-start gap-2 mb-2">
                    <input class="form-check-input room-project mt-2" type="checkbox" value="${project.id}" id="roomProject${project.id}" ${selectedIds.includes(Number(project.id)) ? 'checked' : ''}>
                    <input class="form-control form-control-sm room-project-order" data-project-id="${project.id}" type="number" min="1" value="${orderMap[project.id] || selectedIds.indexOf(Number(project.id)) + 1 || ''}" style="width:76px" title="Orden">
                    <label class="form-check-label flex-grow-1" for="roomProject${project.id}">${escapeHtml(project.title)} <span class="text-muted small">${escapeHtml(projectActiveAuthors(project))}</span></label>
                </div>`).join('') || '<p class="text-muted mb-0">No hay proyectos disponibles para esta fecha, hora y semestre.</p>';
            renderAvailabilityHint(busy);
        }

        function renderAvailabilityHint(busy = busyRoomIds()) {
            const hint = document.getElementById('roomAvailabilityHint');
            if (!hint) return;
            if (!document.getElementById('roomDate').value) {
                hint.textContent = 'Selecciona fecha y hora para filtrar docentes y proyectos ya ocupados en ese horario.';
                return;
            }
            if (!busy.rooms.length) {
                hint.textContent = 'No hay conflictos para el horario seleccionado.';
                return;
            }
            const names = busy.rooms.map(room => room.nombre).join(', ');
            hint.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> Se ocultaron ${busy.teachers.size} docente(s) y ${busy.projects.size} proyecto(s) ya asignados en: ${escapeHtml(names)}.</span>`;
        }

        function renderRooms() {
            const roomActions = room => CAN_MANAGE_EVALUATIONS ? `
                            <button class="btn btn-outline-primary" onclick="editRoom(${room.id})"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-success" onclick="lockRoomSequence(${room.id})" title="Bloquear orden"><i class="bi bi-lock"></i></button>
                            ${room.completed_at ? `<button class="btn btn-outline-secondary" onclick="exportRoom(${room.id})" title="Exportar CSV"><i class="bi bi-file-earmark-spreadsheet"></i></button>` : ''}
                            <button class="btn btn-outline-danger" onclick="deleteRoom(${room.id})"><i class="bi bi-trash"></i></button>
            ` : '';
            document.getElementById('roomsList').innerHTML = rooms.map(room => `
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between gap-2">
                        <div>
                            <h6 class="mb-1">${escapeHtml(room.nombre)} <span class="badge bg-secondary">${room.semestre}</span></h6>
                            <div class="small text-muted">${escapeHtml(room.salon || 'Sin salon')} · ${room.fecha_evaluacion ? new Date(room.fecha_evaluacion).toLocaleString('es-MX') : 'Sin fecha'}</div>
                            <div class="small mt-1">Docentes: ${(room.teachers || []).map(t => escapeHtml(t.nombres)).join(', ') || '-'}</div>
                            <div class="small">Responsable: ${escapeHtml(fullName(room.responsible_teacher) || '-')}</div>
                            <div class="small">Proyectos: ${(room.projects || []).length} ${room.sequence_locked ? '<span class="badge bg-primary">Orden bloqueado</span>' : ''} ${room.completed_at ? '<span class="badge bg-success">Sala finalizada</span>' : ''}</div>
                            <ol class="small mb-1 mt-1">${(room.projects || []).map(project => `<li>${escapeHtml(project.title)} <span class="badge bg-light text-dark">${escapeHtml(project.sequence_status || 'pendiente')}</span></li>`).join('')}</ol>
                            <div class="small">Exposicion: ${room.project_presentation_minutes} min · Evaluacion docente: ${room.teacher_evaluation_minutes} min · Oportunidades: ${room.max_attempts}</div>
                        </div>
                        <div class="btn-group btn-group-sm align-self-start">
                            ${roomActions(room)}
                        </div>
                    </div>
                </div>`).join('') || '<p class="text-muted">No hay salas creadas.</p>';
        }

        async function editRoom(id) {
            const room = rooms.find(item => Number(item.id) === Number(id));
            if (!room) return;
            document.getElementById('roomId').value = room.id;
            document.getElementById('roomName').value = room.nombre || '';
            document.getElementById('roomClassroom').value = room.salon || '';
            document.getElementById('roomSemester').value = room.semestre;
            document.getElementById('roomDate').value = room.fecha_evaluacion ? room.fecha_evaluacion.slice(0, 16) : '';
            document.getElementById('teacherMinutes').value = room.teacher_evaluation_minutes || 15;
            document.getElementById('presentationMinutes').value = room.project_presentation_minutes || 20;
            document.getElementById('maxAttempts').value = room.max_attempts || 1;
            renderRoomTeachers((room.teachers || []).map(t => t.id));
            refreshResponsibleTeacherOptions(room.responsible_teacher_id || '');
            const orderMap = Object.fromEntries((room.projects || []).map(p => [p.id, p.presentation_order || 0]));
            await loadRoomProjects((room.projects || []).map(p => p.id), orderMap);
        }

        function resetRoomForm() {
            ['roomId', 'roomName', 'roomClassroom', 'roomDate'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('responsibleTeacher').innerHTML = '<option value="">Selecciona primero docentes</option>';
            document.getElementById('teacherMinutes').value = 15;
            document.getElementById('presentationMinutes').value = 20;
            document.getElementById('maxAttempts').value = 1;
            renderRoomTeachers();
            loadRoomProjects();
        }

        async function saveRoom() {
            const id = document.getElementById('roomId').value;
            const teacherIds = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            const projectIds = [...document.querySelectorAll('.room-project:checked')].map(input => Number(input.value));
            const projectOrder = {};
            document.querySelectorAll('.room-project-order').forEach(input => {
                if (projectIds.includes(Number(input.dataset.projectId))) {
                    projectOrder[input.dataset.projectId] = Number(input.value || 0);
                }
            });
            const payload = {
                nombre: normalizeRoomName(document.getElementById('roomName').value),
                salon: normalizeClassroom(document.getElementById('roomClassroom').value) || null,
                semestre: document.getElementById('roomSemester').value,
                responsible_teacher_id: document.getElementById('responsibleTeacher').value || null,
                fecha_evaluacion: document.getElementById('roomDate').value,
                teacher_evaluation_minutes: Number(document.getElementById('teacherMinutes').value),
                project_presentation_minutes: Number(document.getElementById('presentationMinutes').value),
                max_attempts: Number(document.getElementById('maxAttempts').value),
                teacher_ids: teacherIds,
                project_ids: projectIds,
                project_order: projectOrder
            };
            if (!payload.nombre || !payload.fecha_evaluacion) {
                showAlert('#alertContainer', 'danger', 'Indica el nombre de la sala y la fecha de evaluacion.');
                return;
            }
            if (new Date(payload.fecha_evaluacion) <= new Date()) {
                showAlert('#alertContainer', 'danger', 'La fecha de la sala debe ser posterior al momento actual.');
                return;
            }
            const repeated = rooms.find(room => String(room.id) !== String(id) && String(room.nombre || '').toLowerCase() === payload.nombre.toLowerCase());
            if (repeated) {
                showAlert('#alertContainer', 'danger', 'Ya existe una sala activa con ese nombre.');
                return;
            }
            if (!teacherIds.length || !projectIds.length) {
                showAlert('#alertContainer', 'danger', 'Selecciona al menos un docente y un proyecto para la sala.');
                return;
            }
            try {
                if (id) await api.put(`/evaluations/rooms/${id}`, payload);
                else await api.post('/evaluations/rooms', payload);
                await loadRooms();
                renderRooms();
                renderRoomOptions();
                resetRoomForm();
                loadEvaluations();
                swalToast('success', 'Sala guardada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando sala');
            }
        }

        async function deleteRoom(id) {
            if (!await confirmAction({ title: 'Desactivar sala', text: 'La sala dejara de aparecer para nuevas evaluaciones.', confirmButtonText: 'Si, desactivar' })) return;
            await api.delete(`/evaluations/rooms/${id}`);
            await loadRooms();
            renderRooms();
            renderRoomOptions();
        }

        async function lockRoomSequence(id) {
            if (!await confirmAction({ title: 'Bloquear orden de paso', text: 'Despues de bloquear, solo el proyecto en turno podra evaluarse.', confirmButtonText: 'Si, bloquear' })) return;
            await api.post(`/evaluations/rooms/${id}/lock-sequence`, {});
            await loadRooms();
            renderRooms();
            loadEvaluations();
        }

        async function askAdvanceRoom(id) {
            const result = await Swal.fire({
                title: 'La evaluacion del proyecto actual ha finalizado?',
                text: 'Continuamos con el siguiente proyecto?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Si',
                cancelButtonText: 'No'
            });
            if (!result.isConfirmed) return;
            await api.post(`/evaluations/rooms/${id}/advance`, { continue_next: true });
            await loadRooms();
            renderRooms();
            loadEvaluations();
        }

        async function exportRoom(id) {
            const response = await fetch(`${API_BASE_URL}/evaluations/rooms/${id}/export`, {
                headers: { Authorization: `Bearer ${auth.getToken()}` }
            });
            if (!response.ok) {
                showAlert('#alertContainer', 'danger', 'No se pudo generar el Excel/CSV de la sala.');
                return;
            }
            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `evaluaciones_sala_${id}.csv`;
            link.click();
            URL.revokeObjectURL(url);
        }

        async function loadRubricCriteria() {
            const criteriaResponse = await api.get('/evaluations/criteria', { _fresh: 1 });
            criteria = criteriaResponse.criteria || [];
            levels = criteriaResponse.levels || levels;
            groupCriteria();
            const semester = document.getElementById('rubricSemester').value;
            const list = document.getElementById('rubricCriteriaList');
            const semesterCriteria = criteriaBySemester[semester] || [];

            if (semesterCriteria.length === 0) {
                list.innerHTML = '<p class="text-muted mb-0">No hay preguntas para este semestre.</p>';
                return;
            }

            list.innerHTML = semesterCriteria.map(criterion => `
                <div class="border rounded p-3 mb-2" data-rubric-id="${criterion.id}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-2">
                            <input type="number" class="form-control criterion-order" value="${criterion.orden || 0}" min="0">
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control criterion-question" value="${escapeHtml(criterion.label)}" maxlength="255">
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateCriterion(${criterion.id})" title="Guardar"><i class="bi bi-save"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCriterion(${criterion.id})" title="Desactivar"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>`).join('');
        }

        async function addCriterion() {
            if (!CAN_MANAGE_EVALUATIONS) return;
            const semester = document.getElementById('rubricSemester').value;
            const text = document.getElementById('newCriterionText').value.trim();
            if (!text) return;
            try {
                await api.post('/evaluations/rubric-criteria', {
                    semestre: semester,
                    pregunta: text
                });
                document.getElementById('newCriterionText').value = '';
                await loadRubricCriteria();
                showAlert('#alertContainer', 'success', 'Pregunta agregada a la rubrica.');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo agregar la pregunta.');
            }
        }

        async function updateCriterion(id) {
            const row = document.querySelector(`[data-rubric-id="${id}"]`);
            await api.put(`/evaluations/rubric-criteria/${id}`, {
                pregunta: row.querySelector('.criterion-question').value.trim(),
                orden: row.querySelector('.criterion-order').value || 0
            });
            loadRubricCriteria();
        }

        async function deleteCriterion(id) {
            if (!await confirmAction({ title: 'Desactivar pregunta', text: '¿Desactivar esta pregunta de la rubrica?', confirmButtonText: 'Si, desactivar' })) return;
            await api.delete(`/evaluations/rubric-criteria/${id}`);
            loadRubricCriteria();
        }

        function openScoreModal(evaluationId) {
            document.getElementById('scoreEvaluationId').value = evaluationId;
            const evaluation = evaluations.find(item => item.id === evaluationId);
            if (evaluation && !evaluation.can_score_now) {
                showAlert('#alertContainer', 'warning', 'Este proyecto aun no esta liberado para evaluacion dentro de la sala.');
                return;
            }
            document.getElementById('generalEvaluationComment').value = '';
            document.getElementById('titulationAptBox').classList.toggle('d-none', Number(evaluation?.semestre) !== 8);
            document.getElementById('apto_titulacion').value = evaluation?.apto_titulacion === true ? '1' : (evaluation?.apto_titulacion === false ? '0' : '');
            if (evaluation?.current_teacher_has_scores && Number(evaluation.current_teacher_attempts) >= Number(evaluation.max_attempts)) {
                showAlert('#alertContainer', 'danger', `Ya alcanzaste el limite de ${evaluation.max_attempts} oportunidad(es) para esta evaluacion.`);
                return;
            }
            const semesterCriteria = criteriaBySemester[evaluation?.semestre] || [];
            const container = document.getElementById('scoreFields');
            container.innerHTML = '';

            if (semesterCriteria.length === 0) {
                container.innerHTML = '<p class="text-muted mb-0">Este semestre no tiene preguntas de rubrica configuradas.</p>';
                scoreModal.show();
                return;
            }

            semesterCriteria.forEach(criterion => {
                container.innerHTML += `
                    <div class="border rounded p-3 mb-3" data-criterion="${criterion.key}">
                        <label class="form-label fw-semibold">${escapeHtml(criterion.label)}</label>
                        <select class="form-select mb-2 criterion-level" required>
                            ${levels.map(level => `<option value="${level}">${level}</option>`).join('')}
                        </select>
                        <textarea class="form-control criterion-comment" rows="2" placeholder="Comentario opcional, no afecta el puntaje"></textarea>
                    </div>`;
            });
            scoreModal.show();
        }

        document.getElementById('scoreForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const evaluationId = document.getElementById('scoreEvaluationId').value;
            const scores = [...document.querySelectorAll('[data-criterion]')].map(row => ({
                criterio: row.dataset.criterion,
                nivel: row.querySelector('.criterion-level').value,
                comentario: row.querySelector('.criterion-comment').value.trim() || null
            }));
            try {
                const evaluation = evaluations.find(item => String(item.id) === String(evaluationId));
                let confirm_update = false;
                if (evaluation?.current_teacher_has_scores) {
                    confirm_update = await confirmAction({
                        title: 'Modificar evaluacion existente',
                        text: `Ya evaluaste este proyecto. Si continuas, se modificara tu evaluacion actual. Oportunidades usadas: ${evaluation.current_teacher_attempts}/${evaluation.max_attempts}.`,
                        confirmButtonText: 'Si, modificar'
                    });
                    if (!confirm_update) return;
                }
                const payload = { scores, confirm_update, general_comment: document.getElementById('generalEvaluationComment').value.trim() || null };
                const aptValue = document.getElementById('apto_titulacion').value;
                if (aptValue !== '') payload.apto_titulacion = aptValue === '1';
                await api.post(`/evaluations/${evaluationId}/score`, payload);
                scoreModal.hide();
                showAlert('#alertContainer', 'success', 'Rubrica guardada correctamente');
                loadEvaluations();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando rubrica');
            }
        });

        function showBreakdown(evaluationId) {
            const evaluation = evaluations.find(item => item.id === evaluationId);
            const container = document.getElementById('breakdownContent');
            if (!evaluation) return;
            const teacherBlocks = evaluation.teacher_breakdown.length === 0
                ? '<p class="text-muted mb-0">Aun no hay evaluaciones registradas por docentes.</p>'
                : evaluation.teacher_breakdown.map(teacher => `
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">${escapeHtml(teacher.teacher_name)}</h6>
                        <span class="badge bg-primary">${teacher.average}%</span>
                    </div>
                    ${teacher.general_comment ? `<div class="alert alert-light border py-2">${escapeHtml(teacher.general_comment)}</div>` : ''}
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tbody>
                                ${teacher.scores.map(score => `
                                    <tr>
                                        <td>${escapeHtml(score.criterio_label)}</td>
                                        <td><span class="badge bg-secondary">${escapeHtml(score.nivel)}</span></td>
                                        <td>${escapeHtml(score.comentario || '-')}</td>
                                    </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>`).join('');
            const feedbackBox = `
                <div class="border rounded p-3">
                    <h6>Retroalimentacion de evaluacion</h6>
                    <p class="text-muted small">${escapeHtml(evaluation.room_feedback || 'Sin retroalimentacion registrada.')}</p>
                    ${(evaluation.can_manage_evaluations || evaluation.is_room_responsible) ? `
                        <textarea class="form-control mb-2" id="roomFeedbackText" rows="3">${escapeHtml(evaluation.room_feedback || '')}</textarea>
                        <button class="btn btn-sm btn-primary" onclick="saveRoomFeedback(${evaluation.id})"><i class="bi bi-save"></i> Guardar retroalimentacion</button>
                    ` : ''}
                </div>`;
            container.innerHTML = teacherBlocks + feedbackBox;
            breakdownModal.show();
        }

        async function saveRoomFeedback(evaluationId) {
            await api.post(`/evaluations/${evaluationId}/feedback`, {
                room_feedback: document.getElementById('roomFeedbackText').value.trim()
            });
            breakdownModal.hide();
            showAlert('#alertContainer', 'success', 'Retroalimentacion guardada');
            loadEvaluations();
        }

        async function deleteEvaluation(id) {
            if (!await confirmAction({ title: 'Eliminar evaluacion', text: '¿Eliminar esta evaluacion?', confirmButtonText: 'Si, eliminar' })) return;
            await api.delete(`/evaluations/${id}`);
            showAlert('#alertContainer', 'success', 'Evaluacion eliminada');
            loadEvaluations();
        }

        document.addEventListener('DOMContentLoaded', async () => {
            evaluationModal = new bootstrap.Modal(document.getElementById('evaluationModal'));
            rubricModal = new bootstrap.Modal(document.getElementById('rubricModal'));
            scoreModal = new bootstrap.Modal(document.getElementById('scoreModal'));
            breakdownModal = new bootstrap.Modal(document.getElementById('breakdownModal'));
            roomsModal = new bootstrap.Modal(document.getElementById('roomsModal'));
            if (IS_ADMIN) evaluationManagersModal = new bootstrap.Modal(document.getElementById('evaluationManagersModal'));
            document.getElementById('roomDate')?.addEventListener('input', updateRoomAvailability);
            await loadInitialData();
            await loadEvaluations();
            startEvaluationsRealtime();
        });
    </script>
</body>
</html>
