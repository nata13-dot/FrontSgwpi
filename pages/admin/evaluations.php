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
                                        <th class="evaluation-col-project">Proyecto</th>
                                        <th class="evaluation-col-members">Integrantes</th>
                                        <th class="evaluation-col-data">Datos clave</th>
                                        <th class="evaluation-col-room">Sala y turno</th>
                                        <th class="evaluation-col-status">Estado</th>
                                        <th class="evaluation-col-average">Promedio</th>
                                        <th class="evaluation-col-actions">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="evaluationsTable">
                                    <tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
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
                        <div class="col-md-2">
                            <label class="form-label" for="rubricSemester">Semestre</label>
                            <select class="form-select" id="rubricSemester" onchange="onRubricScopeChange()">
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="rubricScoreMode">Metodo</label>
                            <select class="form-select" id="rubricScoreMode" onchange="saveRubricScoreMode()">
                                <option value="levels">Acuerdos</option>
                                <option value="numeric">Puntaje 1 a 5</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-none" id="rubricProjectBox">
                            <label class="form-label" for="rubricProjectId">Proyecto 8vo</label>
                            <select class="form-select" id="rubricProjectId" onchange="loadRubricCriteria()">
                                <option value="">Rubrica general de 8vo</option>
                            </select>
                            <div class="form-text">Sin proyecto seleccionado editas la general; puede quedar vacia.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="newCriterionText">Nueva pregunta</label>
                            <input type="text" class="form-control" id="newCriterionText" maxlength="255">
                        </div>
                        <div class="col-md-1">
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
                    <div id="scoreProjectContext" class="border rounded p-3 mb-3"></div>
                    <div id="scoreDraftStatus" class="small text-muted mb-2"></div>
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

    <div class="modal fade" id="projectDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="projectDetailsContent"></div>
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
        let expandedEvaluationRooms = new Set();
        let criteria = [];
        let criteriaBySemester = {};
        let levels = [];
        let rubricScoreModes = {};
        let evaluationModal;
        let rubricModal;
        let scoreModal;
        let breakdownModal;
        let projectDetailsModal;
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

        function projectCompany(project) {
            return project?.company_name || project?.empresa || project?.company?.name || project?.business_name || '';
        }

        function projectAdvisor(project) {
            return fullName(project?.advisor || project?.asesor || project?.teacher || project?.docente);
        }

        function projectControlNumbers(project) {
            const students = Array.isArray(project?.students) ? project.students : [];
            return students.map(student => student.numero_control || student.control_number || student.no_control || student.id).filter(Boolean).join(', ');
        }

        function projectDetailsRows(project) {
            return [
                ['Integrantes', projectActiveAuthors(project)],
                ['No. control', projectControlNumbers(project)],
                ['Empresa', projectCompany(project)],
                ['Giro', project?.company_giro || project?.giro],
                ['Contacto', project?.company_contact_name || project?.contact_name],
                ['Asesor', projectAdvisor(project)],
                ['Descripcion', project?.description || project?.descripcion]
            ].filter(([, value]) => String(value ?? '').trim() !== '');
        }

        function compactProjectMeta(project) {
            const rows = projectDetailsRows(project).slice(0, 4);
            if (rows.length === 0) return '<span class="text-muted small d-block">Sin datos adicionales registrados</span>';
            return rows.map(([label, value]) => `<span class="text-muted small d-block"><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</span>`).join('');
        }

        function projectKeyData(project) {
            const rows = [
                ['Empresa', projectCompany(project)],
                ['Giro', project?.company_giro || project?.giro],
                ['Contacto', project?.company_contact_name || project?.contact_name],
                ['Periodo', project?.year],
                ['Estatus', project?.proposal_status]
            ].filter(([, value]) => String(value ?? '').trim() !== '');
            return rows.length
                ? rows.map(([label, value]) => `<span class="small d-block"><strong>${escapeHtml(label)}:</strong> ${escapeHtml(value)}</span>`).join('')
                : '<span class="text-muted small">Sin datos clave registrados</span>';
        }

        function orderedRoomProjects(room) {
            return [...(room?.projects || [])].sort((a, b) => {
                const orderA = Number(a.presentation_order ?? a.pivot?.presentation_order ?? 0);
                const orderB = Number(b.presentation_order ?? b.pivot?.presentation_order ?? 0);
                if (orderA && orderB && orderA !== orderB) return orderA - orderB;
                if (orderA && !orderB) return -1;
                if (!orderA && orderB) return 1;
                return String(a.title || '').localeCompare(String(b.title || ''), 'es', { sensitivity: 'base' });
            });
        }

        function evaluationProject(evaluation) {
            return evaluation?.project || projects.find(project => String(project.id) === String(evaluation?.project_id)) || {};
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

        function criteriaForEvaluation(evaluation) {
            const projectId = String(evaluation?.project_id || evaluation?.project?.id || '');
            const semesterCriteria = criteriaBySemester[evaluation?.semestre] || [];
            return semesterCriteria.filter(criterion => {
                const criterionProjectId = criterion.project_id === null || criterion.project_id === undefined ? '' : String(criterion.project_id);
                if (Number(evaluation?.semestre) !== 8) return criterionProjectId === '';
                return criterionProjectId === '' || criterionProjectId === projectId;
            });
        }

        function levelLabel(level) {
            if (typeof level === 'object' && level !== null) return level.label || level.key || '';
            const found = levels.find(item => typeof item === 'object' ? item.key === level : item === level);
            if (found && typeof found === 'object') return found.label || found.key;
            return String(level || '').replaceAll('_', ' ');
        }

        function levelValue(level) {
            return typeof level === 'object' && level !== null ? level.key : level;
        }

        function levelPoints(level) {
            const value = levelValue(level);
            const found = levels.find(item => typeof item === 'object' ? item.key === value : item === value);
            return found && typeof found === 'object' && found.puntaje !== undefined ? Number(found.puntaje) : null;
        }

        function levelText(level) {
            const points = levelPoints(level);
            return points === null ? levelLabel(level) : `${levelLabel(level)} (${points} pts)`;
        }

        function rubricModeKey(semester) {
            return `sgpi-rubric-score-mode:${semester}`;
        }

        function currentRubricMode(semester) {
            return rubricScoreModes[String(semester)] || localStorage.getItem(rubricModeKey(semester)) || 'levels';
        }

        async function saveRubricScoreMode() {
            const semester = document.getElementById('rubricSemester').value;
            const mode = document.getElementById('rubricScoreMode').value;
            localStorage.setItem(rubricModeKey(semester), mode);
            rubricScoreModes[String(semester)] = mode;
            try {
                const response = await api.put('/evaluations/rubric-score-modes', { semester: Number(semester), mode });
                rubricScoreModes = response.score_modes || rubricScoreModes;
                swalToast('success', 'Metodo de rubrica guardado');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo guardar el metodo de rubrica.');
            }
        }

        function numericLevelOptions() {
            const orderedKeys = ['totalmente_en_desacuerdo', 'en_desacuerdo', 'neutral', 'de_acuerdo', 'totalmente_de_acuerdo'];
            return orderedKeys.map((key, index) => ({ score: index + 1, level: levels.find(item => levelValue(item) === key) || key }));
        }

        function scoreDraftKey(evaluationId) {
            return `sgpi-score-draft:${evaluationId}`;
        }

        function readScoreDraft(evaluationId) {
            try {
                return JSON.parse(localStorage.getItem(scoreDraftKey(evaluationId)) || '{}');
            } catch (error) {
                return {};
            }
        }

        function collectScoreDraft() {
            return {
                general_comment: document.getElementById('generalEvaluationComment').value,
                apto_titulacion: document.getElementById('apto_titulacion').value,
                scores: [...document.querySelectorAll('[data-criterion]')].map(row => ({
                    criterio: row.dataset.criterion,
                    nivel: row.querySelector('.criterion-level')?.value || '',
                    comentario: row.querySelector('.criterion-comment')?.value || ''
                }))
            };
        }

        function saveScoreDraft() {
            const evaluationId = document.getElementById('scoreEvaluationId').value;
            if (!evaluationId) return;
            localStorage.setItem(scoreDraftKey(evaluationId), JSON.stringify(collectScoreDraft()));
            const status = document.getElementById('scoreDraftStatus');
            if (status) status.innerHTML = '<i class="bi bi-save"></i> Progreso guardado en este navegador.';
        }

        function clearScoreDraft(evaluationId) {
            localStorage.removeItem(scoreDraftKey(evaluationId));
        }

        async function refreshCriteria() {
            const criteriaResponse = await api.get('/evaluations/criteria', { _cache_ttl: 60000 });
            criteria = criteriaResponse.criteria || [];
            levels = criteriaResponse.levels || levels;
            rubricScoreModes = criteriaResponse.score_modes || rubricScoreModes;
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
            const rubricProjectSelect = document.getElementById('rubricProjectId');
            projectFilter.innerHTML = '<option value="">Todos los proyectos</option>';
            projectSelect.innerHTML = '<option value="">Selecciona un proyecto</option>';
            if (rubricProjectSelect) rubricProjectSelect.innerHTML = '<option value="">Rubrica general de 8vo</option>';
            projects.forEach(project => {
                const option = `<option value="${project.id}">${escapeHtml(project.title)}</option>`;
                projectFilter.innerHTML += option;
                projectSelect.innerHTML += option;
                if (rubricProjectSelect && Number(project.semestre) === 8) rubricProjectSelect.innerHTML += option;
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

        function evaluationRoomKey(evaluation) {
            return String(evaluation.room?.id || evaluation.evaluation_room_id || 'sin-sala');
        }

        function evaluationRoomGroups(items) {
            return items.reduce((groups, evaluation) => {
                const key = evaluationRoomKey(evaluation);
                if (!groups[key]) {
                    groups[key] = {
                        total: 0,
                        evaluated: 0,
                        active: 0,
                        pending: 0
                    };
                }
                groups[key].total++;
                if (evaluation.evaluated_by_all) groups[key].evaluated++;
                if (evaluation.sequence_status === 'activo') groups[key].active++;
                if (!evaluation.evaluated_by_all) groups[key].pending++;
                return groups;
            }, {});
        }

        function cssEscape(value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(String(value));
            }
            return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
        }

        function toggleEvaluationRoom(roomKey) {
            const key = String(roomKey);
            const expanded = expandedEvaluationRooms.has(key);
            if (expanded) {
                expandedEvaluationRooms.delete(key);
            } else {
                expandedEvaluationRooms.add(key);
            }

            document.querySelectorAll(`[data-evaluation-room-row="${cssEscape(key)}"]`).forEach(row => {
                row.classList.toggle('d-none', expanded);
            });
            const icon = document.querySelector(`[data-evaluation-room-icon="${cssEscape(key)}"]`);
            if (icon) icon.className = expanded ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
            const label = document.querySelector(`[data-evaluation-room-label="${cssEscape(key)}"]`);
            if (label) label.textContent = expanded ? 'Desplegar' : 'Ocultar';
        }

        async function loadEvaluations(showLoading = true, fresh = false) {
            const projectId = document.getElementById('projectFilter').value;
            const params = projectId ? { project_id: projectId } : {};
            if (fresh) params._fresh = 1;
            const tbody = document.getElementById('evaluationsTable');
            if (showLoading) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>';
            }
            const response = await api.get('/evaluations', params);
            evaluations = orderEvaluationsByRoomSequence(removeDuplicateEvaluations(response.data || []));
            tbody.innerHTML = '';

            if (evaluations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay evaluaciones</td></tr>';
                return;
            }

            let lastRoomKey = null;
            const roomGroups = evaluationRoomGroups(evaluations);
            evaluations.forEach(evaluation => {
                const roomKey = evaluationRoomKey(evaluation);
                const isExpanded = expandedEvaluationRooms.has(roomKey);
                if (roomKey !== lastRoomKey) {
                    lastRoomKey = roomKey;
                    const room = evaluation.room;
                    const stats = roomGroups[roomKey] || { total: 0, evaluated: 0, active: 0, pending: 0 };
                    tbody.innerHTML += `
                        <tr class="table-light evaluation-room-header">
                            <td colspan="7">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2" onclick="toggleEvaluationRoom('${escapeHtml(roomKey)}')">
                                        <i class="bi ${isExpanded ? 'bi-chevron-down' : 'bi-chevron-right'}" data-evaluation-room-icon="${escapeHtml(roomKey)}"></i>
                                        <span data-evaluation-room-label="${escapeHtml(roomKey)}">${isExpanded ? 'Ocultar' : 'Desplegar'}</span>
                                    </button>
                                    <div class="flex-grow-1">
                                        <strong>${escapeHtml(room?.nombre || 'Sin sala')}</strong>
                                        <span class="text-muted small d-block">${escapeHtml(room?.salon || '-')} · Responsable: ${escapeHtml(fullName(room?.responsible_teacher) || '-')}</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-secondary">${stats.total} proyecto${stats.total === 1 ? '' : 's'}</span>
                                        <span class="badge bg-success">${stats.evaluated} evaluado${stats.evaluated === 1 ? '' : 's'}</span>
                                        ${stats.active ? `<span class="badge bg-primary">${stats.active} en turno</span>` : ''}
                                        ${stats.pending ? `<span class="badge bg-warning text-dark">${stats.pending} pendiente${stats.pending === 1 ? '' : 's'}</span>` : ''}
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                }
                const averageColor = evaluation.global_average_color || 'secondary';
                const project = evaluationProject(evaluation);
                const disabled = evaluation.can_score_now ? '' : 'disabled';
                const statusClass = evaluation.evaluation_badge_color
                    ? `bg-${evaluation.evaluation_badge_color}`
                    : ({ activo: 'bg-primary', evaluado: 'bg-success', pendiente: 'bg-secondary' }[evaluation.sequence_status] || 'bg-secondary');
                const evaluatedClass = evaluation.evaluated_by_all ? 'table-success' : '';
                const evaluatedBadge = evaluation.evaluated_by_all ? '<span class="badge bg-success ms-2"><i class="bi bi-check2-circle"></i> Evaluado por todos</span>' : '';
                tbody.innerHTML += `
                    <tr class="${evaluatedClass} ${isExpanded ? '' : 'd-none'}" data-evaluation-room-row="${escapeHtml(roomKey)}">
                        <td class="evaluation-cell-project">
                            <div class="d-flex align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold">${escapeHtml(project?.title || 'N/A')}${evaluatedBadge}</div>
                                    <span class="text-muted small d-block">Semestre ${escapeHtml(evaluation.semestre || '-')} · ${escapeHtml(stageLabel(evaluation.etapa))}</span>
                                </div>
                            </div>
                        </td>
                        <td class="evaluation-cell-members">
                            <div class="small">${escapeHtml(projectActiveAuthors(project) || '-')}</div>
                            ${projectControlNumbers(project) ? `<div class="text-muted small"><strong>No. control:</strong> ${escapeHtml(projectControlNumbers(project))}</div>` : ''}
                        </td>
                        <td class="evaluation-cell-data">${projectKeyData(project)}</td>
                        <td class="evaluation-cell-room">
                            <span class="badge ${statusClass}">Orden #${evaluation.presentation_order || '-'}</span>
                            <div class="small mt-1">${escapeHtml(evaluation.sala || evaluation.room?.nombre || '-')}</div>
                            <div class="text-muted small">${evaluation.fecha_exposicion ? new Date(evaluation.fecha_exposicion).toLocaleString('es-MX') : '-'}</div>
                        </td>
                        <td class="evaluation-cell-status">
                            <span class="badge bg-secondary">${escapeHtml(evaluation.resultado)}</span>
                            <div class="small text-muted mt-1">${escapeHtml(evaluation.sequence_status || evaluation.estado || '-')}</div>
                        </td>
                        <td class="evaluation-cell-average">
                            <button class="btn btn-sm btn-outline-${averageColor}" onclick="showBreakdown(${evaluation.id})">
                                ${evaluation.global_average}% · ${evaluation.evaluators_count}/${evaluation.expected_evaluators_count || 0} evaluadores
                            </button>
                        </td>
                        <td class="evaluation-cell-actions">
                            <div class="evaluation-actions">
                                <button class="btn btn-sm btn-success evaluation-start-btn" onclick="openScoreModal(${evaluation.id})" title="Evaluar" ${disabled}>
                                    <i class="bi bi-clipboard-check"></i><span>Evaluar</span>
                                </button>
                                <div class="evaluation-secondary-actions">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="showProjectDetails(${evaluation.id})" title="Detalles del proyecto"><i class="bi bi-info-circle"></i></button>
                                    <button class="btn btn-sm btn-outline-dark" onclick="downloadEvaluationReport(${evaluation.id})" title="Reporte PDF"><i class="bi bi-file-earmark-pdf"></i></button>
                                    ${(evaluation.can_manage_evaluations && Number(evaluation.semestre) === 8) ? `<button class="btn btn-sm btn-outline-primary" onclick="openRubricModal(${evaluation.project_id})" title="Rubrica personalizada"><i class="bi bi-ui-checks-grid"></i></button>` : ''}
                                    ${(evaluation.can_manage_evaluations || evaluation.is_room_responsible) ? `<button class="btn btn-sm btn-outline-primary" onclick="askAdvanceRoom(${evaluation.evaluation_room_id})" title="Finalizar y continuar"><i class="bi bi-skip-forward"></i></button>` : ''}
                                    ${evaluation.can_manage_evaluations ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteEvaluation(${evaluation.id})" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                                </div>
                            </div>
                        </td>
                    </tr>`;
            });
        }

        function removeDuplicateEvaluations(items) {
            const seen = new Set();
            return items.filter(item => {
                const key = [
                    item.evaluation_room_id || item.room?.id || 'sin-sala',
                    item.project_id || item.project?.id || item.id
                ].join(':');
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            });
        }

        function orderEvaluationsByRoomSequence(items) {
            return [...items].sort((a, b) => {
                const roomA = String(a.room?.id || a.evaluation_room_id || '');
                const roomB = String(b.room?.id || b.evaluation_room_id || '');
                if (roomA !== roomB) return roomA.localeCompare(roomB, 'es', { numeric: true });

                const orderA = Number(a.presentation_order || 0);
                const orderB = Number(b.presentation_order || 0);
                if (orderA && orderB && orderA !== orderB) return orderA - orderB;
                if (orderA && !orderB) return -1;
                if (!orderA && orderB) return 1;

                const dateA = new Date(a.fecha_exposicion || 0).getTime();
                const dateB = new Date(b.fecha_exposicion || 0).getTime();
                return dateA - dateB;
            });
        }

        function showProjectDetails(evaluationId) {
            const evaluation = evaluations.find(item => Number(item.id) === Number(evaluationId));
            if (!evaluation) return;
            const project = evaluationProject(evaluation);
            const rows = projectDetailsRows(project);
            document.getElementById('projectDetailsContent').innerHTML = `
                <h5 class="mb-2">${escapeHtml(project?.title || 'N/A')}</h5>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-secondary">Orden #${escapeHtml(evaluation.presentation_order || '-')}</span>
                    <span class="badge bg-light text-dark">Semestre ${escapeHtml(evaluation.semestre || '-')}</span>
                    <span class="badge bg-light text-dark">${escapeHtml(stageLabel(evaluation.etapa))}</span>
                </div>
                ${rows.length ? `
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <tbody>
                                ${rows.map(([label, value]) => `
                                    <tr>
                                        <th style="width: 160px">${escapeHtml(label)}</th>
                                        <td>${escapeHtml(value)}</td>
                                    </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>
                ` : '<p class="text-muted mb-0">No hay datos adicionales registrados para este proyecto.</p>'}
            `;
            projectDetailsModal.show();
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

        function onRubricScopeChange() {
            const semester = document.getElementById('rubricSemester').value;
            const projectBox = document.getElementById('rubricProjectBox');
            projectBox.classList.toggle('d-none', Number(semester) !== 8);
            if (Number(semester) !== 8) document.getElementById('rubricProjectId').value = '';
            loadRubricCriteria();
        }

        function openRubricModal(projectId = '') {
            if (!CAN_MANAGE_EVALUATIONS) return;
            document.getElementById('rubricSemester').value = projectId ? '8' : '5';
            document.getElementById('rubricProjectId').value = projectId ? String(projectId) : '';
            document.getElementById('rubricProjectBox').classList.toggle('d-none', !projectId);
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
            const currentRoomId = document.getElementById('roomId').value;
            const projectRooms = rooms.filter(room => !(currentRoomId && String(room.id) === String(currentRoomId)));
            return {
                teachers: new Set(conflicts.flatMap(room => (room.teachers || []).map(teacher => String(teacher.id)))),
                projects: new Set(projectRooms.flatMap(room => (room.projects || []).map(project => Number(project.id)))),
                rooms: conflicts,
                projectRooms
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
            const selectedSet = new Set(selectedIds);
            const availableProjects = roomProjects
                .filter(project => selectedSet.has(Number(project.id)) || !busy.projects.has(Number(project.id)))
                .sort((a, b) => {
                    const selectedA = selectedSet.has(Number(a.id));
                    const selectedB = selectedSet.has(Number(b.id));
                    if (selectedA !== selectedB) return selectedA ? -1 : 1;
                    const orderA = Number(orderMap[a.id] || 0);
                    const orderB = Number(orderMap[b.id] || 0);
                    if (orderA && orderB && orderA !== orderB) return orderA - orderB;
                    if (orderA && !orderB) return -1;
                    if (!orderA && orderB) return 1;
                    return String(a.title || '').localeCompare(String(b.title || ''), 'es', { sensitivity: 'base' });
                });
            document.getElementById('roomProjects').innerHTML = availableProjects.map(project => `
                <div class="d-flex align-items-start gap-2 mb-2">
                    <input class="form-check-input room-project mt-2" type="checkbox" value="${project.id}" id="roomProject${project.id}" ${selectedIds.includes(Number(project.id)) ? 'checked' : ''}>
                    <input class="form-control form-control-sm room-project-order" data-project-id="${project.id}" type="number" min="1" value="${orderMap[project.id] || selectedIds.indexOf(Number(project.id)) + 1 || ''}" style="width:76px" title="Orden">
                    <label class="form-check-label flex-grow-1" for="roomProject${project.id}">
                        <span class="fw-semibold">${escapeHtml(project.title)}</span>
                        <span class="text-muted small d-block">${escapeHtml(projectActiveAuthors(project) || 'Sin integrantes registrados')}</span>
                        ${projectCompany(project) ? `<span class="text-muted small d-block">Empresa: ${escapeHtml(projectCompany(project))}</span>` : ''}
                    </label>
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
            if (!busy.rooms.length && !busy.projects.size) {
                hint.textContent = 'No hay conflictos para el horario seleccionado.';
                return;
            }
            const names = busy.rooms.map(room => room.nombre).join(', ');
            const projectCount = busy.projects.size;
            const teacherText = busy.rooms.length ? ` Se ocultaron ${busy.teachers.size} docente(s) ocupados en: ${escapeHtml(names)}.` : '';
            const projectText = projectCount ? ` ${projectCount} proyecto(s) ya asignados a otra sala no estan disponibles.` : '';
            hint.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i>${teacherText}${projectText}</span>`;
        }

        function renderRooms() {
            const roomActions = room => CAN_MANAGE_EVALUATIONS ? `
                            <button class="btn btn-outline-primary" onclick="editRoom(${room.id})"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-outline-success" onclick="lockRoomSequence(${room.id})" title="Bloquear orden"><i class="bi bi-lock"></i></button>
                            <button class="btn btn-outline-dark" onclick="downloadRoomReport(${room.id})" title="Reporte PDF de sala"><i class="bi bi-file-earmark-pdf"></i></button>
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
                            <ol class="small mb-1 mt-1">${orderedRoomProjects(room).map(project => `<li>${escapeHtml(project.title)} <span class="badge bg-light text-dark">${escapeHtml(project.sequence_status || 'pendiente')}</span></li>`).join('')}</ol>
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
            const selectedProjects = orderedRoomProjects(room);
            const orderMap = Object.fromEntries(selectedProjects.map(p => [p.id, p.presentation_order || 0]));
            await loadRoomProjects(selectedProjects.map(p => p.id), orderMap);
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
            const duplicateProjectIds = projectIds.filter((projectId, index) => projectIds.indexOf(projectId) !== index);
            if (duplicateProjectIds.length) {
                showAlert('#alertContainer', 'danger', 'Hay proyectos duplicados en la sala. Revisa la seleccion antes de guardar.');
                return;
            }
            const selectedOrders = Object.values(projectOrder).filter(order => order > 0);
            const duplicateOrders = selectedOrders.filter((order, index) => selectedOrders.indexOf(order) !== index);
            if (duplicateOrders.length) {
                showAlert('#alertContainer', 'danger', 'No repitas el orden de presentacion entre proyectos.');
                return;
            }
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

        async function downloadRoomReport(id) {
            const response = await fetch(`${API_BASE_URL}/evaluations/rooms/${id}/report.pdf`, {
                credentials: 'include',
                headers: { Authorization: `Bearer ${auth.getToken()}` }
            });
            if (!response.ok) {
                showAlert('#alertContainer', 'danger', 'No se pudo generar el reporte PDF de la sala.');
                return;
            }
            const content = await response.blob();
            const blob = new Blob([content], { type: 'application/pdf' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `reporte_sala_${id}.pdf`;
            link.click();
            URL.revokeObjectURL(url);
        }

        async function downloadEvaluationReport(id) {
            const response = await fetch(`${API_BASE_URL}/evaluations/${id}/report.pdf`, {
                credentials: 'include',
                headers: { Authorization: `Bearer ${auth.getToken()}` }
            });
            if (!response.ok) {
                showAlert('#alertContainer', 'danger', 'No se pudo generar el reporte PDF.');
                return;
            }
            const content = await response.blob();
            const blob = new Blob([content], { type: 'application/pdf' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `reporte_evaluacion_${id}.pdf`;
            link.click();
            URL.revokeObjectURL(url);
        }

        async function loadRubricCriteria() {
            const criteriaResponse = await api.get('/evaluations/criteria', { _fresh: 1 });
            criteria = criteriaResponse.criteria || [];
            levels = criteriaResponse.levels || levels;
            rubricScoreModes = criteriaResponse.score_modes || rubricScoreModes;
            groupCriteria();
            const semester = document.getElementById('rubricSemester').value;
            const selectedProjectId = Number(semester) === 8 ? document.getElementById('rubricProjectId').value : '';
            document.getElementById('rubricScoreMode').value = currentRubricMode(semester);
            const list = document.getElementById('rubricCriteriaList');
            const semesterCriteria = (criteriaBySemester[semester] || []).filter(criterion => {
                const criterionProjectId = criterion.project_id === null || criterion.project_id === undefined ? '' : String(criterion.project_id);
                return criterionProjectId === String(selectedProjectId || '');
            });

            if (semesterCriteria.length === 0) {
                list.innerHTML = `<p class="text-muted mb-0">${selectedProjectId ? 'Este proyecto aun no tiene preguntas personalizadas.' : 'No hay preguntas para esta rubrica general.'}</p>`;
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
                            <div class="form-text">${criterion.project_id ? 'Personalizada del proyecto' : 'General del semestre'}</div>
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
            const projectId = Number(semester) === 8 ? document.getElementById('rubricProjectId').value : '';
            const text = document.getElementById('newCriterionText').value.trim();
            if (!text) return;
            try {
                const payload = {
                    semestre: semester,
                    pregunta: text
                };
                if (projectId) payload.project_id = Number(projectId);
                await api.post('/evaluations/rubric-criteria', payload);
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
            const draft = readScoreDraft(evaluationId);
            document.getElementById('scoreDraftStatus').innerHTML = Object.keys(draft).length
                ? '<i class="bi bi-save"></i> Se restauro un progreso guardado en este navegador.'
                : '';
            const project = evaluationProject(evaluation);
            document.getElementById('scoreProjectContext').innerHTML = `
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <h6 class="mb-1">${escapeHtml(project?.title || 'N/A')}</h6>
                        <div class="small text-muted"><strong>Integrantes:</strong> ${escapeHtml(projectActiveAuthors(project) || '-')}</div>
                        ${projectControlNumbers(project) ? `<div class="small text-muted"><strong>No. control:</strong> ${escapeHtml(projectControlNumbers(project))}</div>` : ''}
                    </div>
                    <span class="badge bg-secondary align-self-start">Orden #${escapeHtml(evaluation?.presentation_order || '-')}</span>
                </div>
            `;
            document.getElementById('generalEvaluationComment').value = draft.general_comment || '';
            document.getElementById('titulationAptBox').classList.toggle('d-none', Number(evaluation?.semestre) !== 8);
            document.getElementById('apto_titulacion').value = draft.apto_titulacion ?? (evaluation?.apto_titulacion === true ? '1' : (evaluation?.apto_titulacion === false ? '0' : ''));
            if (evaluation?.current_teacher_has_scores && Number(evaluation.current_teacher_attempts) >= Number(evaluation.max_attempts)) {
                showAlert('#alertContainer', 'danger', `Ya alcanzaste el limite de ${evaluation.max_attempts} oportunidad(es) para esta evaluacion.`);
                return;
            }
            const semesterCriteria = criteriaForEvaluation(evaluation);
            const rubricMode = currentRubricMode(evaluation?.semestre);
            const numericOptions = numericLevelOptions();
            const container = document.getElementById('scoreFields');
            container.innerHTML = '';

            if (semesterCriteria.length === 0) {
                container.innerHTML = '<p class="text-muted mb-0">Este semestre no tiene preguntas de rubrica configuradas.</p>';
                scoreModal.show();
                return;
            }

            semesterCriteria.forEach(criterion => {
                const draftScore = (draft.scores || []).find(score => score.criterio === criterion.key) || {};
                container.innerHTML += `
                    <div class="border rounded p-3 mb-3" data-criterion="${criterion.key}">
                        <label class="form-label fw-semibold">${escapeHtml(criterion.label)} ${criterion.project_id ? '<span class="badge bg-info text-dark ms-1">Proyecto</span>' : '<span class="badge bg-secondary ms-1">General</span>'}</label>
                        <select class="form-select mb-2 criterion-level" required>
                            ${(rubricMode === 'numeric' ? numericOptions : levels).map(option => {
                                const value = rubricMode === 'numeric' ? levelValue(option.level) : levelValue(option);
                                const label = rubricMode === 'numeric' ? `${option.score} punto${option.score === 1 ? '' : 's'}` : levelText(option);
                                return `<option value="${escapeHtml(value)}" ${String(draftScore.nivel || '') === String(value) ? 'selected' : ''}>${escapeHtml(label)}</option>`;
                            }).join('')}
                        </select>
                        <textarea class="form-control criterion-comment" rows="2" placeholder="Comentario opcional, no afecta el puntaje">${escapeHtml(draftScore.comentario || '')}</textarea>
                    </div>`;
            });
            container.querySelectorAll('select, textarea').forEach(input => {
                input.oninput = saveScoreDraft;
                input.onchange = saveScoreDraft;
            });
            document.getElementById('generalEvaluationComment').oninput = saveScoreDraft;
            document.getElementById('apto_titulacion').onchange = saveScoreDraft;
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
                const confirmSave = await confirmAction({
                    title: 'Guardar evaluacion',
                    text: `Se guardara la rubrica para ${evaluationProject(evaluation)?.title || 'este proyecto'}.`,
                    confirmButtonText: 'Si, guardar'
                });
                if (!confirmSave) return;
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
                clearScoreDraft(evaluationId);
                document.getElementById('scoreEvaluationId').value = '';
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
                    ${teacher.general_comment ? `<div class="alert alert-light border py-2 mb-3"><strong>Comentario general:</strong><div class="mt-1" style="white-space:pre-wrap">${escapeHtml(teacher.general_comment)}</div></div>` : ''}
                    <div class="d-grid gap-2">
                        ${teacher.scores.map(score => `
                            <div class="border rounded p-2">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <strong>${escapeHtml(score.criterio_label)}</strong>
                                    <span>
                                        <span class="badge bg-secondary">${score.score_mode === 'numeric' ? `${Number(score.puntaje ?? 0)} de ${Number(score.puntaje_max || teacher.max_score || 5)}` : escapeHtml(score.nivel_label || levelLabel(score.nivel))}</span>
                                        <span class="small text-muted ms-1">${Number(score.puntaje ?? 0)} pts</span>
                                    </span>
                                </div>
                                <div class="small text-muted mt-2">Comentario de la pregunta</div>
                                <div class="bg-light border rounded p-2 mt-1 ${score.comentario ? '' : 'text-muted'}" style="white-space:pre-wrap">${escapeHtml(score.comentario || 'Sin comentario para esta pregunta.')}</div>
                            </div>`).join('')}
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
            projectDetailsModal = new bootstrap.Modal(document.getElementById('projectDetailsModal'));
            roomsModal = new bootstrap.Modal(document.getElementById('roomsModal'));
            if (IS_ADMIN) evaluationManagersModal = new bootstrap.Modal(document.getElementById('evaluationManagersModal'));
            document.getElementById('scoreModal').addEventListener('hide.bs.modal', saveScoreDraft);
            document.getElementById('roomDate')?.addEventListener('input', updateRoomAvailability);
            await loadInitialData();
            await loadEvaluations();
            startEvaluationsRealtime();
        });
    </script>
</body>
</html>
