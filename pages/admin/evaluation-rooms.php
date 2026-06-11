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
    <title>Gestion de Salas - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .room-work-panel {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        .room-work-panel-header {
            background: var(--soft-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.15rem;
        }
        .room-work-panel-body {
            padding: 1.15rem;
        }
        .room-section-title {
            color: var(--primary-blue);
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: 0;
            margin: .25rem 0 .75rem;
            text-transform: uppercase;
        }
        .room-scroll-list {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            max-height: 320px;
            overflow: auto;
            padding: .75rem;
        }
        .room-option-card,
        .room-project-item,
        .room-list-card,
        .room-teacher-card,
        .room-project-card {
            background: var(--surface-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }
        .room-option-card {
            align-items: flex-start;
            cursor: pointer;
            display: flex;
            gap: .65rem;
            padding: .7rem .8rem;
        }
        .room-option-card + .room-option-card,
        .room-project-item + .room-project-item,
        .room-list-card + .room-list-card {
            margin-top: .65rem;
        }
        .room-option-card:has(input:checked),
        .room-project-item:has(input:checked) {
            background: color-mix(in srgb, var(--primary-blue) 9%, var(--surface-bg));
            border-color: color-mix(in srgb, var(--primary-blue) 45%, var(--border-color));
        }
        .room-project-item,
        .room-list-card,
        .room-teacher-card,
        .room-project-card {
            padding: .9rem;
        }
        .room-meta-grid {
            display: grid;
            gap: .45rem .8rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin: .75rem 0;
        }
        .room-meta-item {
            background: var(--soft-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: .55rem .65rem;
        }
        .room-action-group {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            justify-content: flex-end;
        }
        .room-metric {
            background: var(--soft-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: .75rem;
            text-align: center;
        }
        .room-metric strong {
            color: var(--primary-blue);
            display: block;
            font-size: 1.25rem;
        }
        .room-metric span {
            color: var(--text-muted);
            font-size: .82rem;
        }
        .room-step-list {
            display: grid;
            gap: .5rem;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin-bottom: 1rem;
        }
        .room-step {
            align-items: center;
            background: var(--soft-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-muted);
            display: flex;
            gap: .55rem;
            min-height: 48px;
            padding: .6rem .7rem;
        }
        .room-step i {
            color: var(--primary-blue);
            font-size: 1rem;
        }
        .room-step strong {
            color: var(--text-color);
            display: block;
            font-size: .82rem;
            line-height: 1.1;
        }
        .room-step span {
            display: block;
            font-size: .74rem;
            line-height: 1.15;
        }
        .room-form-help {
            background: color-mix(in srgb, var(--primary-blue) 7%, var(--surface-bg));
            border: 1px solid color-mix(in srgb, var(--primary-blue) 26%, var(--border-color));
            border-radius: 8px;
            color: var(--text-color);
            font-size: .88rem;
            margin-bottom: 1rem;
            padding: .75rem .85rem;
        }
        .room-inline-hint {
            color: var(--text-muted);
            font-size: .8rem;
            margin-top: .3rem;
        }
        @media (max-width: 991.98px) {
            .room-meta-grid {
                grid-template-columns: 1fr;
            }
            .room-action-group {
                justify-content: flex-start;
            }
            .room-step-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 575.98px) {
            .room-step-list {
                grid-template-columns: 1fr;
            }
        }
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
                        <h1 class="mb-1">Salas de evaluacion</h1>
                        <p class="text-muted mb-0">Funcion de evaluaciones para programar salas, responsables, docentes y orden de proyectos.</p>
                    </div>
                    <a class="btn btn-outline-secondary" href="/pages/admin/evaluations.php">
                        <i class="bi bi-arrow-left"></i> Volver a evaluaciones
                    </a>
                </div>

                <div id="alertContainer"></div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label" for="semesterFilter">Semestre</label>
                        <select class="form-select" id="semesterFilter" onchange="renderCurrentView()">
                            <option value="">Todos</option>
                            <option value="5">5 - Propuesta</option>
                            <option value="6">6 - Avance</option>
                            <option value="7">7 - Avance</option>
                            <option value="8">8 - Titulacion</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="roomStatusFilter">Estado</label>
                        <select class="form-select" id="roomStatusFilter" onchange="reloadRooms()">
                            <option value="active">Activas</option>
                            <option value="archived">Archivadas</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="roomSearch">Buscar</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" id="roomSearch" placeholder="Sala, docente, proyecto o salon" oninput="renderCurrentView()">
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Vista de gestion de salas">
                    <button type="button" class="btn btn-primary" id="viewRoomsBtn" onclick="setRoomView('rooms')">
                        <i class="bi bi-door-open"></i> Por sala
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="viewProjectsBtn" onclick="setRoomView('projects')">
                        <i class="bi bi-kanban"></i> Por proyecto
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="viewTeachersBtn" onclick="setRoomView('teachers')">
                        <i class="bi bi-person-workspace"></i> Por evaluador
                    </button>
                    <button type="button" class="btn btn-success ms-md-auto" onclick="startNewRoom()">
                        <i class="bi bi-plus-circle"></i> Crear sala
                    </button>
                </div>

                <div class="row g-3 mb-4" id="roomSummary">
                    <div class="col-sm-3"><div class="room-metric"><strong id="summaryRooms">0</strong><span>Salas</span></div></div>
                    <div class="col-sm-3"><div class="room-metric"><strong id="summaryProjects">0</strong><span>Proyectos asignados</span></div></div>
                    <div class="col-sm-3"><div class="room-metric"><strong id="summaryTeachers">0</strong><span>Evaluadores</span></div></div>
                    <div class="col-sm-3"><div class="room-metric"><strong id="summaryPending">0</strong><span>Sin sala</span></div></div>
                </div>

                <div id="roomByRoomView">
                    <div class="row g-3">
                        <div class="col-xl-5">
                            <div class="room-work-panel" id="roomFormPanel">
                                <div class="room-work-panel-header">
                                    <h5 class="mb-0"><i class="bi bi-sliders"></i> Configuracion de sala</h5>
                                </div>
                                <div class="room-work-panel-body">
                                    <div class="room-form-help">
                                        <i class="bi bi-info-circle"></i>
                                        Completa la sala de izquierda a derecha: primero horario, despues evaluadores, responsable y proyectos. La disponibilidad se actualiza con base en el horario elegido.
                                    </div>
                                    <div class="room-step-list" aria-label="Pasos para crear sala">
                                        <div class="room-step"><i class="bi bi-calendar-event"></i><div><strong>1. Horario</strong><span>Fecha, hora y salon</span></div></div>
                                        <div class="room-step"><i class="bi bi-people"></i><div><strong>2. Evaluadores</strong><span>Docentes disponibles</span></div></div>
                                        <div class="room-step"><i class="bi bi-person-badge"></i><div><strong>3. Responsable</strong><span>Dentro de la sala</span></div></div>
                                        <div class="room-step"><i class="bi bi-list-ol"></i><div><strong>4. Proyectos</strong><span>Orden de paso</span></div></div>
                                    </div>
                                    <input type="hidden" id="roomId">
                                    <div class="row g-3">
                                        <div class="col-12"><div class="room-section-title">Datos generales</div></div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="roomName">Sala</label>
                                            <input class="form-control" id="roomName" placeholder="Sala 1">
                                            <div class="room-inline-hint">Se normaliza automaticamente, por ejemplo: SALA 1.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="roomClassroom">Salon</label>
                                            <input class="form-control" id="roomClassroom" placeholder="Salon/Laboratorio">
                                            <div class="room-inline-hint">Puedes escribir EB1, laboratorio o aula.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="roomSemester">Semestre</label>
                                            <select class="form-select" id="roomSemester" onchange="loadRoomProjects()">
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="roomDate">Inicio</label>
                                            <input type="datetime-local" class="form-control" id="roomDate" onchange="updateRoomAvailability()">
                                            <div class="room-inline-hint">Debe ser posterior a la fecha actual.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="roomEndDate">Fin</label>
                                            <input type="datetime-local" class="form-control" id="roomEndDate" onchange="updateRoomAvailability()">
                                            <div class="room-inline-hint">Si lo dejas vacio, se propone 1 hora.</div>
                                        </div>

                                        <div class="col-12"><div class="room-section-title">Tiempos</div></div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="teacherMinutes">Eval. docente</label>
                                            <input type="number" class="form-control" id="teacherMinutes" min="1" max="240" value="15">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="presentationMinutes">Presentacion</label>
                                            <input type="number" class="form-control" id="presentationMinutes" min="1" max="240" value="20">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="maxAttempts">Oportunidades</label>
                                            <input type="number" class="form-control" id="maxAttempts" min="1" max="10" value="1">
                                        </div>

                                        <div class="col-12">
                                            <div class="room-section-title">Evaluadores</div>
                                            <div class="room-inline-hint mb-2">Solo aparecen docentes sin empalme en el horario seleccionado.</div>
                                            <div class="room-scroll-list" id="roomTeachers"></div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="responsibleTeacher">Responsable de sala</label>
                                            <select class="form-select" id="responsibleTeacher"><option value="">Selecciona primero docentes</option></select>
                                            <div class="room-inline-hint">El responsable se elige entre los evaluadores seleccionados.</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="room-section-title">Proyectos y orden</div>
                                            <div class="room-inline-hint mb-2">Marca los proyectos y ajusta su orden de presentacion.</div>
                                            <div class="room-scroll-list" id="roomProjects"></div>
                                        </div>
                                        <div class="col-12">
                                            <div class="small text-muted" id="roomAvailabilityHint"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button class="btn btn-outline-secondary" onclick="resetRoomForm()">Limpiar</button>
                                        <button class="btn btn-primary" onclick="saveRoom()"><i class="bi bi-save"></i> Guardar sala</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-7">
                            <div class="room-work-panel">
                                <div class="room-work-panel-header d-flex justify-content-between align-items-center gap-2">
                                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Salas registradas</h5>
                                    <span class="badge bg-light text-primary" id="roomsCountBadge">0 salas</span>
                                </div>
                                <div class="room-work-panel-body" id="roomsList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-none" id="roomByProjectView">
                    <div class="room-work-panel">
                        <div class="room-work-panel-header">
                            <h5 class="mb-0"><i class="bi bi-kanban"></i> Proyectos por sala</h5>
                        </div>
                        <div class="room-work-panel-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Proyecto</th>
                                            <th>Semestre</th>
                                            <th>Sala actual</th>
                                            <th>Asignar a sala</th>
                                            <th>Orden</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="projectRoomTable"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-none" id="roomByTeacherView">
                    <div class="row g-3" id="teacherRoomContainer"></div>
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
        let teachers = [];
        let projects = [];
        let rooms = [];
        let roomProjects = [];
        let roomView = 'rooms';
        let roomProjectsReorderTimer = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama, user?.apellido_paterno, user?.apellido_materno].filter(Boolean).join(' ') || user?.id || '';
        }

        function projectActiveAuthors(project) {
            const students = Array.isArray(project?.students) ? project.students : [];
            return students.map(student => fullName(student)).filter(Boolean).join(', ');
        }

        function projectCompany(project) {
            return project?.company_name || project?.empresa?.nombre || '';
        }

        function normalizeRoomName(value) {
            const clean = String(value || '').trim().replace(/\s+/g, ' ').toUpperCase();
            if (/^SALA\s+\d+/.test(clean)) return clean;
            const number = clean.match(/\d+/)?.[0] || '';
            return number ? `SALA ${number}` : clean;
        }

        function normalizeClassroom(value) {
            const clean = String(value || '').trim().replace(/\s+/g, ' ').toUpperCase();
            if (/^EB\d+/.test(clean)) return clean;
            const number = clean.match(/\d+/)?.[0] || '';
            return number ? `EB${number}` : clean;
        }

        function parseRoomDate(value) {
            if (!value) return null;
            const normalized = String(value).includes('T') ? String(value) : String(value).replace(' ', 'T');
            const date = new Date(normalized);
            return Number.isNaN(date.getTime()) ? null : date;
        }

        function formatLocalDateTimeInput(date) {
            const pad = value => String(value).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        function roomRange(room) {
            const start = parseRoomDate(room.fecha_evaluacion);
            const end = parseRoomDate(room.fecha_fin_evaluacion);
            return { start, end: end || (start ? new Date(start.getTime() + 60 * 60 * 1000) : null) };
        }

        function rangesOverlap(startA, endA, startB, endB) {
            return startA && endA && startB && endB && startA < endB && endA > startB;
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

        function roomForProject(projectId) {
            return rooms.find(room => (room.projects || []).some(project => Number(project.id) === Number(projectId))) || null;
        }

        function currentSemesterFilter() {
            return document.getElementById('semesterFilter').value;
        }

        function searchText() {
            return document.getElementById('roomSearch').value.trim().toLowerCase();
        }

        function filteredRooms() {
            const semester = currentSemesterFilter();
            const search = searchText();
            return rooms.filter(room => {
                if (semester && String(room.semestre) !== String(semester)) return false;
                if (!search) return true;
                const haystack = [
                    room.nombre,
                    room.salon,
                    fullName(room.responsible_teacher),
                    ...(room.teachers || []).map(fullName),
                    ...(room.projects || []).map(project => project.title)
                ].join(' ').toLowerCase();
                return haystack.includes(search);
            });
        }

        function filteredProjects() {
            const semester = currentSemesterFilter();
            const search = searchText();
            return projects.filter(project => {
                if (semester && String(project.semestre) !== String(semester)) return false;
                if (!search) return true;
                const currentRoom = roomForProject(project.id);
                const haystack = `${project.title} ${projectActiveAuthors(project)} ${projectCompany(project)} ${currentRoom?.nombre || ''}`.toLowerCase();
                return haystack.includes(search);
            });
        }

        async function loadTeachers() {
            const [adminsResponse, teachersResponse] = await Promise.all([
                api.get('/users', { perfil_id: 1, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000, _timeout: 45000 }),
                api.get('/users', { perfil_id: 2, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000, _timeout: 45000 })
            ]);
            teachers = [...(adminsResponse.data || []), ...(teachersResponse.data || [])]
                .sort((a, b) => Number(a.perfil_id) - Number(b.perfil_id) || fullName(a).localeCompare(fullName(b)));
        }

        async function loadProjects() {
            projects = await api.get('/evaluations/projects', { _cache_ttl: 30000, _timeout: 45000 });
        }

        async function loadRooms() {
            const archived = document.getElementById('roomStatusFilter').value === 'archived' ? 1 : 0;
            rooms = await api.get('/evaluations/rooms', { archived, _fresh: 1, _timeout: 30000 });
        }

        async function reloadRooms() {
            await loadRooms();
            renderCurrentView();
        }

        async function loadInitialData() {
            await Promise.all([loadTeachers(), loadProjects(), loadRooms()]);
            renderRoomTeachers();
            loadRoomProjects();
            renderCurrentView();
        }

        function setRoomView(view) {
            if (roomView === view) return;
            SGPIViewTransition.run(() => {
                roomView = view;
                document.getElementById('roomByRoomView').classList.toggle('d-none', view !== 'rooms');
                document.getElementById('roomByProjectView').classList.toggle('d-none', view !== 'projects');
                document.getElementById('roomByTeacherView').classList.toggle('d-none', view !== 'teachers');
                document.getElementById('viewRoomsBtn').className = view === 'rooms' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                document.getElementById('viewProjectsBtn').className = view === 'projects' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                document.getElementById('viewTeachersBtn').className = view === 'teachers' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                renderCurrentView();
            });
        }

        function renderCurrentView() {
            renderSummary();
            if (roomView === 'rooms') renderRooms();
            if (roomView === 'projects') renderProjectRoomView();
            if (roomView === 'teachers') renderTeacherRoomView();
        }

        function renderSummary() {
            const visibleRooms = filteredRooms();
            const assignedProjectIds = new Set(rooms.flatMap(room => (room.projects || []).map(project => Number(project.id))));
            const visibleProjects = filteredProjects();
            const visibleTeacherIds = new Set(visibleRooms.flatMap(room => (room.teachers || []).map(teacher => String(teacher.id))));
            document.getElementById('summaryRooms').textContent = visibleRooms.length;
            document.getElementById('summaryProjects').textContent = visibleRooms.reduce((total, room) => total + (room.projects || []).length, 0);
            document.getElementById('summaryTeachers').textContent = visibleTeacherIds.size;
            document.getElementById('summaryPending').textContent = visibleProjects.filter(project => !assignedProjectIds.has(Number(project.id))).length;
        }

        function renderRooms() {
            const visibleRooms = filteredRooms();
            document.getElementById('roomsCountBadge').textContent = `${visibleRooms.length} sala${visibleRooms.length === 1 ? '' : 's'}`;
            document.getElementById('roomsList').innerHTML = visibleRooms.map(room => {
                const archived = document.getElementById('roomStatusFilter').value === 'archived';
                return `
                    <div class="room-list-card">
                        <div class="d-flex justify-content-between gap-3 flex-wrap">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <span class="fw-bold">${escapeHtml(room.nombre)}</span>
                                    <span class="badge bg-secondary">Semestre ${escapeHtml(room.semestre)}</span>
                                    ${room.sequence_locked ? '<span class="badge bg-primary">Orden bloqueado</span>' : ''}
                                    ${room.completed_at ? '<span class="badge bg-success">Finalizada</span>' : ''}
                                </div>
                                <div class="room-meta-grid small">
                                    <div class="room-meta-item"><span class="text-muted d-block">Lugar</span>${escapeHtml(room.salon || 'Sin salon')}</div>
                                    <div class="room-meta-item"><span class="text-muted d-block">Horario</span>${room.fecha_evaluacion ? new Date(room.fecha_evaluacion).toLocaleString('es-MX') : 'Sin inicio'}${room.fecha_fin_evaluacion ? ` - ${new Date(room.fecha_fin_evaluacion).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })}` : ''}</div>
                                    <div class="room-meta-item"><span class="text-muted d-block">Responsable</span>${escapeHtml(fullName(room.responsible_teacher) || '-')}</div>
                                    <div class="room-meta-item"><span class="text-muted d-block">Tiempos</span>${room.project_presentation_minutes} min exposicion / ${room.teacher_evaluation_minutes} min evaluacion</div>
                                </div>
                                <div class="small mb-2"><span class="fw-semibold">Evaluadores:</span> ${(room.teachers || []).map(teacher => escapeHtml(fullName(teacher))).filter(Boolean).join(', ') || '-'}</div>
                                <div class="small fw-semibold mb-1">Proyectos (${(room.projects || []).length})</div>
                                <ol class="small mb-0 ps-3">${orderedRoomProjects(room).map(project => `<li class="mb-1">${escapeHtml(project.title)} <span class="badge bg-light text-dark">Orden ${escapeHtml(project.presentation_order || project.pivot?.presentation_order || '-')}</span></li>`).join('') || '<li>Sin proyectos asignados</li>'}</ol>
                            </div>
                            <div class="room-action-group align-self-start">
                                <button class="btn btn-sm btn-outline-dark" onclick="downloadRoomReport(${room.id})" title="Reporte sala"><i class="bi bi-file-earmark-pdf"></i></button>
                                <button class="btn btn-sm btn-outline-info" onclick="downloadRoomTeacherReport(${room.id})" title="Reporte docentes"><i class="bi bi-people"></i></button>
                                ${archived ? `<button class="btn btn-sm btn-outline-secondary" onclick="unarchiveRoom(${room.id})" title="Restaurar"><i class="bi bi-arrow-counterclockwise"></i></button>` : `
                                    <button class="btn btn-sm btn-outline-primary" onclick="editRoom(${room.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-success" onclick="lockRoomSequence(${room.id})" title="Bloquear orden"><i class="bi bi-lock"></i></button>
                                    ${room.sequence_locked && !room.completed_at ? `<button class="btn btn-sm btn-outline-warning" onclick="advanceRoom(${room.id})" title="Siguiente proyecto"><i class="bi bi-skip-forward"></i></button>` : ''}
                                    <button class="btn btn-sm btn-outline-secondary" onclick="archiveRoom(${room.id})" title="Archivar"><i class="bi bi-archive"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(${room.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                                `}
                            </div>
                        </div>
                    </div>`;
            }).join('') || '<p class="text-muted mb-0">No hay salas con los filtros seleccionados.</p>';
        }

        function renderProjectRoomView() {
            const rows = filteredProjects();
            document.getElementById('projectRoomTable').innerHTML = rows.map(project => {
                const currentRoom = roomForProject(project.id);
                const availableRooms = rooms.filter(room => String(room.semestre) === String(project.semestre));
                const currentOrder = currentRoom ? (orderedRoomProjects(currentRoom).find(item => Number(item.id) === Number(project.id))?.presentation_order || '') : '';
                return `
                    <tr>
                        <td>
                            <strong>${escapeHtml(project.title)}</strong>
                            <div class="small text-muted">${escapeHtml(projectActiveAuthors(project) || 'Sin integrantes registrados')}</div>
                        </td>
                        <td>${escapeHtml(project.semestre || '-')}</td>
                        <td>${currentRoom ? `<span class="badge bg-primary">${escapeHtml(currentRoom.nombre)}</span><div class="small text-muted">${escapeHtml(currentRoom.salon || '')}</div>` : '<span class="text-muted">Sin sala</span>'}</td>
                        <td>
                            <select class="form-select form-select-sm" id="projectRoom${project.id}">
                                <option value="">Sin sala</option>
                                ${availableRooms.map(room => `<option value="${room.id}" ${currentRoom && Number(currentRoom.id) === Number(room.id) ? 'selected' : ''}>${escapeHtml(room.nombre)} - ${escapeHtml(room.salon || 'Sin salon')}</option>`).join('')}
                            </select>
                        </td>
                        <td><input class="form-control form-control-sm" id="projectOrder${project.id}" type="number" min="1" value="${escapeHtml(currentOrder)}" style="width:90px"></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="saveProjectRoom(${project.id})"><i class="bi bi-save"></i></button>
                            ${currentRoom ? `<button class="btn btn-sm btn-outline-secondary" onclick="editRoom(${currentRoom.id}); setRoomView('rooms')"><i class="bi bi-door-open"></i></button>` : ''}
                        </td>
                    </tr>`;
            }).join('') || '<tr><td colspan="6" class="text-center text-muted py-4">No hay proyectos con los filtros seleccionados.</td></tr>';
        }

        function renderTeacherRoomView() {
            const search = searchText();
            const visibleRooms = filteredRooms();
            const cards = teachers.map(teacher => {
                const evaluatorRooms = visibleRooms.filter(room => (room.teachers || []).some(item => String(item.id) === String(teacher.id)));
                const responsibleRooms = visibleRooms.filter(room => String(room.responsible_teacher_id || room.responsible_teacher?.id || '') === String(teacher.id));
                return { teacher, evaluatorRooms, responsibleRooms };
            }).filter(item => {
                if (!item.evaluatorRooms.length && !item.responsibleRooms.length) return false;
                if (!search) return true;
                return `${item.teacher.id} ${fullName(item.teacher)} ${(item.teacher.email || '')}`.toLowerCase().includes(search)
                    || item.evaluatorRooms.some(room => String(room.nombre || '').toLowerCase().includes(search));
            }).sort((a, b) => (b.evaluatorRooms.length + b.responsibleRooms.length) - (a.evaluatorRooms.length + a.responsibleRooms.length) || fullName(a.teacher).localeCompare(fullName(b.teacher)));

            document.getElementById('teacherRoomContainer').innerHTML = cards.map(item => `
                <div class="col-xl-6">
                    <div class="room-teacher-card h-100">
                        <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
                            <div>
                                <strong>${escapeHtml(fullName(item.teacher))}</strong>
                                <div class="small text-muted">${escapeHtml(item.teacher.id)}${item.teacher.email ? ' · ' + escapeHtml(item.teacher.email) : ''}</div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary">${item.evaluatorRooms.length} como evaluador</span>
                                <span class="badge bg-success">${item.responsibleRooms.length} responsable</span>
                            </div>
                        </div>
                        <div class="small fw-semibold mb-1">Salas</div>
                        <div class="d-grid gap-2">
                            ${[...new Map([...item.evaluatorRooms, ...item.responsibleRooms].map(room => [room.id, room])).values()].map(room => `
                                <div class="room-meta-item">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                                        <strong>${escapeHtml(room.nombre)}</strong>
                                        <span class="badge bg-secondary">Semestre ${escapeHtml(room.semestre)}</span>
                                    </div>
                                    <div class="small text-muted">${escapeHtml(room.salon || '-')} · ${room.fecha_evaluacion ? new Date(room.fecha_evaluacion).toLocaleString('es-MX') : '-'}</div>
                                    <div class="small">${(room.projects || []).length} proyecto(s)</div>
                                </div>`).join('')}
                        </div>
                    </div>
                </div>`).join('') || '<div class="col-12"><div class="alert alert-info mb-0">No hay evaluadores con salas en los filtros actuales.</div></div>';
        }

        function conflictingRoomsForCurrentForm() {
            const currentRoomId = document.getElementById('roomId').value;
            const selectedStart = parseRoomDate(document.getElementById('roomDate').value);
            const selectedEnd = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!selectedStart || !selectedEnd || selectedEnd <= selectedStart) return [];
            return rooms.filter(room => {
                const isSameRecord = currentRoomId && String(room.id) === String(currentRoomId);
                const range = roomRange(room);
                return !isSameRecord && rangesOverlap(selectedStart, selectedEnd, range.start, range.end);
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

        function currentRoomProjectState() {
            const selected = [...document.querySelectorAll('.room-project:checked')].map(input => Number(input.value));
            const orderMap = {};
            document.querySelectorAll('.room-project-order').forEach(input => {
                const projectId = Number(input.dataset.projectId);
                const order = Number(input.value || 0);
                if (projectId && order > 0) orderMap[projectId] = order;
            });
            return { selected, orderMap };
        }

        function normalizeProjectOrderMap(selectedIds, orderMap = {}) {
            const normalized = {};
            let nextOrder = Math.max(0, ...Object.values(orderMap).map(Number).filter(order => order > 0)) + 1;
            selectedIds.forEach(projectId => {
                const order = Number(orderMap[projectId] || 0);
                normalized[projectId] = order > 0 ? order : nextOrder++;
            });
            return normalized;
        }

        function scheduleRoomProjectsReorder() {
            clearTimeout(roomProjectsReorderTimer);
            roomProjectsReorderTimer = setTimeout(() => {
                const { selected, orderMap } = currentRoomProjectState();
                loadRoomProjects(selected, orderMap);
            }, 180);
        }

        async function updateRoomAvailability() {
            const startInput = document.getElementById('roomDate');
            const endInput = document.getElementById('roomEndDate');
            const startsAt = parseRoomDate(startInput.value);
            if (startsAt && !endInput.value) {
                endInput.value = formatLocalDateTimeInput(new Date(startsAt.getTime() + 60 * 60 * 1000));
            }
            const selectedTeachers = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            const { selected: selectedProjects, orderMap } = currentRoomProjectState();
            renderRoomTeachers(selectedTeachers);
            loadRoomProjects(selectedProjects, orderMap);
        }

        function renderRoomTeachers(selected = []) {
            const selectedIds = selected.map(String);
            const busy = busyRoomIds();
            const availableTeachers = teachers.filter(teacher => !busy.teachers.has(String(teacher.id)) || selectedIds.includes(String(teacher.id)));
            document.getElementById('roomTeachers').innerHTML = availableTeachers.map(teacher => `
                <label class="room-option-card" for="roomTeacher${escapeHtml(teacher.id)}">
                    <input class="form-check-input room-teacher mt-1" type="checkbox" value="${escapeHtml(teacher.id)}" id="roomTeacher${escapeHtml(teacher.id)}" ${selectedIds.includes(String(teacher.id)) ? 'checked' : ''} onchange="refreshResponsibleTeacherOptions()">
                    <span class="flex-grow-1">
                        <span class="fw-semibold d-block">${escapeHtml(fullName(teacher))}</span>
                        <span class="text-muted small">${Number(teacher.perfil_id) === 1 ? 'Administrativo' : 'Docente'}</span>
                    </span>
                </label>`).join('') || '<p class="text-muted mb-0">No hay evaluadores disponibles para esta fecha y hora.</p>';
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

        function loadRoomProjects(selected = [], orderMap = {}) {
            const semester = document.getElementById('roomSemester').value;
            const currentRoomId = Number(document.getElementById('roomId').value || 0);
            roomProjects = projects.filter(project => {
                const assignedRoomId = Number(project.assigned_room_id || 0);
                return String(project.semestre || '') === String(semester)
                    && (!assignedRoomId || assignedRoomId === currentRoomId);
            });
            const selectedIds = selected.map(Number);
            const normalizedOrderMap = normalizeProjectOrderMap(selectedIds, orderMap);
            const busy = busyRoomIds();
            const selectedSet = new Set(selectedIds);
            const availableProjects = roomProjects
                .filter(project => selectedSet.has(Number(project.id)) || !busy.projects.has(Number(project.id)))
                .sort((a, b) => {
                    const selectedA = selectedSet.has(Number(a.id));
                    const selectedB = selectedSet.has(Number(b.id));
                    if (selectedA !== selectedB) return selectedA ? -1 : 1;
                    const orderA = Number(normalizedOrderMap[a.id] || 0);
                    const orderB = Number(normalizedOrderMap[b.id] || 0);
                    if (orderA && orderB && orderA !== orderB) return orderA - orderB;
                    if (orderA && !orderB) return -1;
                    if (!orderA && orderB) return 1;
                    return String(a.title || '').localeCompare(String(b.title || ''), 'es', { sensitivity: 'base' });
                });
            document.getElementById('roomProjects').innerHTML = availableProjects.map(project => `
                <div class="room-project-item d-flex align-items-start gap-2">
                    <input class="form-check-input room-project mt-2" type="checkbox" value="${project.id}" id="roomProject${project.id}" ${selectedIds.includes(Number(project.id)) ? 'checked' : ''} onchange="scheduleRoomProjectsReorder()">
                    <input class="form-control form-control-sm room-project-order" data-project-id="${project.id}" type="number" min="1" value="${normalizedOrderMap[project.id] || ''}" style="width:76px" title="Orden" onchange="scheduleRoomProjectsReorder()">
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
            if (!document.getElementById('roomDate').value || !document.getElementById('roomEndDate').value) {
                hint.textContent = 'Selecciona hora de inicio y fin para filtrar docentes y proyectos ocupados en ese rango.';
                return;
            }
            const selectedStart = parseRoomDate(document.getElementById('roomDate').value);
            const selectedEnd = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!selectedStart || !selectedEnd || selectedEnd <= selectedStart) {
                hint.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> La hora de fin debe ser posterior a la hora de inicio.</span>';
                return;
            }
            if (!busy.rooms.length && !busy.projects.size) {
                hint.textContent = 'No hay conflictos para el rango seleccionado.';
                return;
            }
            const names = busy.rooms.map(room => room.nombre).join(', ');
            hint.innerHTML = `<span class="text-warning"><i class="bi bi-exclamation-triangle"></i> Hay conflictos con: ${escapeHtml(names)}.</span>`;
        }

        function focusRoomForm() {
            setRoomView('rooms');
            document.getElementById('roomFormPanel').scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('roomName').focus();
        }

        async function startNewRoom() {
            if (document.getElementById('roomStatusFilter').value === 'archived') {
                document.getElementById('roomStatusFilter').value = 'active';
                await reloadRooms();
            }
            resetRoomForm();
            focusRoomForm();
        }

        async function editRoom(id) {
            const room = rooms.find(item => Number(item.id) === Number(id));
            if (!room) return;
            document.getElementById('roomId').value = room.id;
            document.getElementById('roomName').value = room.nombre || '';
            document.getElementById('roomClassroom').value = room.salon || '';
            document.getElementById('roomSemester').value = room.semestre;
            document.getElementById('roomDate').value = room.fecha_evaluacion ? room.fecha_evaluacion.slice(0, 16) : '';
            document.getElementById('roomEndDate').value = room.fecha_fin_evaluacion ? room.fecha_fin_evaluacion.slice(0, 16) : '';
            document.getElementById('teacherMinutes').value = room.teacher_evaluation_minutes || 15;
            document.getElementById('presentationMinutes').value = room.project_presentation_minutes || 20;
            document.getElementById('maxAttempts').value = room.max_attempts || 1;
            renderRoomTeachers((room.teachers || []).map(teacher => teacher.id));
            refreshResponsibleTeacherOptions(room.responsible_teacher_id || room.responsible_teacher?.id || '');
            const selectedProjects = orderedRoomProjects(room);
            const orderMap = Object.fromEntries(selectedProjects.map(project => [project.id, project.presentation_order || project.pivot?.presentation_order || 0]));
            loadRoomProjects(selectedProjects.map(project => project.id), orderMap);
            focusRoomForm();
        }

        function resetRoomForm() {
            ['roomId', 'roomName', 'roomClassroom', 'roomDate', 'roomEndDate'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('responsibleTeacher').innerHTML = '<option value="">Selecciona primero docentes</option>';
            document.getElementById('teacherMinutes').value = 15;
            document.getElementById('presentationMinutes').value = 20;
            document.getElementById('maxAttempts').value = 1;
            renderRoomTeachers();
            loadRoomProjects();
        }

        function roomPayloadFromRoom(room, projectsOverride = null) {
            const orderedProjects = projectsOverride || orderedRoomProjects(room);
            return {
                nombre: room.nombre,
                salon: room.salon || null,
                semestre: room.semestre,
                responsible_teacher_id: room.responsible_teacher_id || room.responsible_teacher?.id || null,
                fecha_evaluacion: room.fecha_evaluacion,
                fecha_fin_evaluacion: room.fecha_fin_evaluacion,
                teacher_evaluation_minutes: Number(room.teacher_evaluation_minutes || 15),
                project_presentation_minutes: Number(room.project_presentation_minutes || 20),
                max_attempts: Number(room.max_attempts || 1),
                teacher_ids: (room.teachers || []).map(teacher => teacher.id),
                project_ids: orderedProjects.map(project => Number(project.id)),
                project_order: Object.fromEntries(orderedProjects.map((project, index) => [project.id, Number(project.presentation_order || project.pivot?.presentation_order || index + 1)]))
            };
        }

        async function saveRoom() {
            const id = document.getElementById('roomId').value;
            const teacherIds = [...document.querySelectorAll('.room-teacher:checked')].map(input => input.value);
            const projectIds = [...document.querySelectorAll('.room-project:checked')].map(input => Number(input.value));
            const startsAt = parseRoomDate(document.getElementById('roomDate').value);
            const endsAt = parseRoomDate(document.getElementById('roomEndDate').value);
            if (!startsAt || !endsAt || endsAt <= startsAt) {
                showAlert('#alertContainer', 'warning', 'La hora de fin de la sala debe ser posterior a la hora de inicio.');
                return;
            }
            const projectOrder = {};
            document.querySelectorAll('.room-project-order').forEach(input => {
                if (projectIds.includes(Number(input.dataset.projectId))) projectOrder[input.dataset.projectId] = Number(input.value || 0);
            });
            const selectedOrders = Object.values(projectOrder).filter(order => order > 0);
            if (selectedOrders.some((order, index) => selectedOrders.indexOf(order) !== index)) {
                showAlert('#alertContainer', 'danger', 'No repitas el orden de presentacion entre proyectos.');
                return;
            }
            const payload = {
                nombre: normalizeRoomName(document.getElementById('roomName').value),
                salon: normalizeClassroom(document.getElementById('roomClassroom').value) || null,
                semestre: document.getElementById('roomSemester').value,
                responsible_teacher_id: document.getElementById('responsibleTeacher').value || null,
                fecha_evaluacion: document.getElementById('roomDate').value,
                fecha_fin_evaluacion: document.getElementById('roomEndDate').value,
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
            if (!teacherIds.length || !projectIds.length) {
                showAlert('#alertContainer', 'danger', 'Selecciona al menos un docente y un proyecto para la sala.');
                return;
            }
            try {
                const response = id
                    ? await api.put(`/evaluations/rooms/${id}`, payload)
                    : await api.post('/evaluations/rooms', payload);
                replaceRoomLocal(response.room);
                renderCurrentView();
                resetRoomForm();
                swalToast('success', 'Sala guardada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error guardando sala');
            }
        }

        async function saveProjectRoom(projectId) {
            const targetRoomId = document.getElementById(`projectRoom${projectId}`).value;
            const order = Number(document.getElementById(`projectOrder${projectId}`).value || 0);
            const currentRoom = roomForProject(projectId);
            const targetRoom = targetRoomId ? rooms.find(room => Number(room.id) === Number(targetRoomId)) : null;
            const project = projects.find(item => Number(item.id) === Number(projectId));
            if (!project) return;
            try {
                if (currentRoom && (!targetRoom || Number(currentRoom.id) !== Number(targetRoom.id))) {
                    const nextProjects = orderedRoomProjects(currentRoom).filter(item => Number(item.id) !== Number(projectId));
                    const response = await api.put(`/evaluations/rooms/${currentRoom.id}`, roomPayloadFromRoom(currentRoom, nextProjects));
                    replaceRoomLocal(response.room);
                }
                if (targetRoom) {
                    const existing = orderedRoomProjects(targetRoom).filter(item => Number(item.id) !== Number(projectId));
                    existing.push({ ...project, presentation_order: order || existing.length + 1 });
                    const response = await api.put(`/evaluations/rooms/${targetRoom.id}`, roomPayloadFromRoom(targetRoom, existing));
                    replaceRoomLocal(response.room);
                }
                renderCurrentView();
                swalToast('success', 'Asignacion de proyecto actualizada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo actualizar la asignacion del proyecto.');
            }
        }

        async function deleteRoom(id) {
            if (!await confirmAction({ title: 'Eliminar sala', text: 'Se eliminaran tambien evaluaciones, puntajes e intentos vinculados.', confirmButtonText: 'Si, eliminar' })) return;
            try {
                const response = await api.delete(`/evaluations/rooms/${id}`);
                rooms = rooms.filter(room => Number(room.id) !== Number(id));
                syncProjectRoomAssignments();
                renderCurrentView();
                swalToast('success', response.message || 'Sala eliminada');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo eliminar la sala');
            }
        }

        async function archiveRoom(id) {
            if (!await confirmAction({ title: 'Archivar sala', text: 'Todas las evaluaciones de esta sala dejaran la vista principal.', confirmButtonText: 'Si, archivar sala' })) return;
            await api.post(`/evaluations/rooms/${id}/archive`, {});
            rooms = rooms.filter(room => Number(room.id) !== Number(id));
            syncProjectRoomAssignments();
            renderCurrentView();
            swalToast('success', 'Sala archivada');
        }

        async function unarchiveRoom(id) {
            if (!await confirmAction({ title: 'Restaurar sala', text: 'Todas las evaluaciones de esta sala volveran a la vista principal.', confirmButtonText: 'Si, restaurar sala' })) return;
            await api.post(`/evaluations/rooms/${id}/unarchive`, {});
            rooms = rooms.filter(room => Number(room.id) !== Number(id));
            syncProjectRoomAssignments();
            renderCurrentView();
            swalToast('success', 'Sala restaurada');
        }

        async function lockRoomSequence(id) {
            if (!await confirmAction({ title: 'Bloquear orden de paso', text: 'Despues de bloquear, solo el proyecto en turno podra evaluarse.', confirmButtonText: 'Si, bloquear' })) return;
            const response = await api.post(`/evaluations/rooms/${id}/lock-sequence`, {});
            replaceRoomLocal(response.room);
            renderCurrentView();
        }

        async function advanceRoom(id) {
            if (!await confirmAction({ title: 'Avanzar turno', text: 'Se finalizara el proyecto actual y se activara el siguiente.', confirmButtonText: 'Si, avanzar' })) return;
            try {
                const response = await api.post(`/evaluations/rooms/${id}/advance`, { continue_next: true });
                replaceRoomLocal(response.room);
                renderCurrentView();
                swalToast('success', response.message || 'Turno actualizado');
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo avanzar el turno');
            }
        }

        function replaceRoomLocal(updatedRoom) {
            if (!updatedRoom) return;
            const index = rooms.findIndex(room => Number(room.id) === Number(updatedRoom.id));
            if (index >= 0) rooms[index] = updatedRoom;
            else rooms.unshift(updatedRoom);
            syncProjectRoomAssignments();
        }

        function syncProjectRoomAssignments() {
            const assignments = new Map();
            rooms.forEach(room => {
                (room.projects || room.proyectos || []).forEach(project => assignments.set(String(project.id), room.id));
            });
            projects = projects.map(project => ({
                ...project,
                assigned_room_id: assignments.get(String(project.id)) || null
            }));
        }

        async function downloadRoomReport(id) {
            await downloadPdf(`/evaluations/rooms/${id}/report.pdf`, `reporte_sala_${id}.pdf`, 'No se pudo generar el reporte PDF de la sala.');
        }

        async function downloadRoomTeacherReport(id) {
            await downloadPdf(`/evaluations/rooms/${id}/report.pdf?audience=teachers`, `reporte_sala_${id}_docentes.pdf`, 'No se pudo generar el reporte PDF para docentes.');
        }

        async function downloadPdf(endpoint, filename, fallbackMessage) {
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                    headers: { Authorization: `Bearer ${auth.getToken()}` },
                    credentials: 'include'
                });
                if (!response.ok) throw new Error(fallbackMessage);
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                link.click();
                URL.revokeObjectURL(url);
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || fallbackMessage);
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            try {
                await loadInitialData();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'No se pudo cargar la gestion de salas.');
            }
        });
    </script>
</body>
</html>
