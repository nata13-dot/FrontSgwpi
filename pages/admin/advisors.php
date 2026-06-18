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
    <title>Asignación de Asesores - <?= APP_NAME ?></title>
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
                        <h1 class="mb-1">Asignación de Asesores</h1>
                        <p class="text-muted mb-0">Gestiona asesores por proyecto y comités para tesis marcadas por administración.</p>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="semesterFilter" class="form-label">Filtrar por semestre</label>
                        <select id="semesterFilter" class="form-select" onchange="loadProjects()">
                            <option value="">Todos los semestres</option>
                            <option value="5">5 - Propuesta</option>
                            <option value="6">6 - Avance</option>
                            <option value="7">7 - Avance</option>
                            <option value="8">8 - Titulación</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Vista de gestión de asesores">
                    <button type="button" class="btn btn-primary" id="viewByProjectBtn" onclick="setAdvisorView('projects')">
                        <i class="bi bi-folder2-open"></i> Por proyecto
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="viewThesisCommitteeBtn" onclick="setAdvisorView('thesis')">
                        <i class="bi bi-mortarboard"></i> Comité de tesis
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="viewByTeacherBtn" onclick="setAdvisorView('teachers')">
                        <i class="bi bi-person-workspace"></i> Por asesor
                    </button>
                    <button type="button" class="btn btn-success ms-md-auto" id="saveAllAdvisorsBtn" onclick="saveAllAdvisorChanges()" disabled>
                        <i class="bi bi-save"></i> Guardar cambios
                    </button>
                </div>

                <div class="card border-0 shadow-sm" id="projectAssignmentView">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Proyecto</th>
                                        <th>Semestre</th>
                                        <th>Grupo</th>
                                        <th>Asesor primario</th>
                                        <th>Asesor secundario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="projectsTable">
                                    <tr><td colspan="6" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm d-none" id="thesisCommitteeView">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Tesis</th>
                                        <th>Semestre</th>
                                        <th>Grupo</th>
                                        <th>Asesor</th>
                                        <th>Revisor 1</th>
                                        <th>Revisor 2</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="thesisCommitteeTable">
                                    <tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="teacherAdvisorView" class="d-none">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-4">
                                    <label class="form-label" for="teacherSearch">Buscar asesor</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="search" class="form-control" id="teacherSearch" placeholder="Nombre, apellidos o nómina" oninput="renderTeacherAdvisorView()">
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label" for="teacherRoleFilter">Filtrar por rol</label>
                                    <select class="form-select" id="teacherRoleFilter" onchange="renderTeacherAdvisorView()">
                                        <option value="">Todos los roles</option>
                                        <option value="primario">Asesor primario</option>
                                        <option value="secundario">Asesor secundario</option>
                                        <option value="asesor">Asesor de tesis</option>
                                        <option value="revisor_1">Revisor 1</option>
                                        <option value="revisor_2">Revisor 2</option>
                                    </select>
                                </div>
                                <div class="col-lg-5">
                                    <div class="row g-2 text-center" id="advisorSummary">
                                        <div class="col-sm-4">
                                            <div class="advisor-metric">
                                                <strong id="summaryTeachers">0</strong>
                                                <span>Asesores</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="advisor-metric">
                                                <strong id="summaryAssigned">0</strong>
                                                <span>Con proyectos</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="advisor-metric">
                                                <strong id="summaryProjects">0</strong>
                                                <span>Roles asignados</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="teacherAdvisorContainer" class="row g-3"></div>
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
        let projects = [];
        let teachers = [];
        let advisorView = 'projects';
        const ADVISOR_ROLES = [
            { key: 'primario', label: 'Primario', badge: 'bg-primary', icon: 'bi-award', help: 'Responsable principal' },
            { key: 'secundario', label: 'Secundario', badge: 'bg-info text-dark', icon: 'bi-person-check', help: 'Apoyo académico' }
        ];
        const THESIS_ROLES = [
            { key: 'asesor', label: 'Asesor', badge: 'bg-primary', icon: 'bi-award', help: 'Responsable académico' },
            { key: 'revisor_1', label: 'Revisor 1', badge: 'bg-info text-dark', icon: 'bi-person-check', help: 'Primera revisión' },
            { key: 'revisor_2', label: 'Revisor 2', badge: 'bg-warning text-dark', icon: 'bi-person-lines-fill', help: 'Segunda revisión' }
        ];

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || 'Sin nombre';
        }

        function projectActiveAuthors(project, emptyText = 'Sin integrantes registrados') {
            const students = Array.isArray(project?.students) ? project.students : [];
            const names = students
                .filter(Boolean)
                .map(student => fullName(student))
                .filter(Boolean);
            return names.length ? names.join(', ') : emptyText;
        }

        function profileName(user) {
            return Number(user?.perfil_id) === 1 ? 'Administrador' : 'Docente';
        }

        function profileBadge(user) {
            return Number(user?.perfil_id) === 1
                ? '<span class="badge advisor-admin-badge"><i class="bi bi-shield-lock"></i> Administrador</span>'
                : '<span class="badge bg-secondary"><i class="bi bi-person-workspace"></i> Docente</span>';
        }

        function advisorRoleBadge(advisor) {
            const role = escapeHtml(roleLabel(advisor.pivot?.rol_asesor || ''));
            const profile = Number(advisor?.perfil_id) === 1
                ? '<span class="badge advisor-admin-badge ms-1">Administrador</span>'
                : '<span class="badge bg-secondary ms-1">Docente</span>';
            return `<span class="badge bg-dark">${role}</span>${profile}`;
        }

        function roleLabel(role) {
            return [...ADVISOR_ROLES, ...THESIS_ROLES].find(item => item.key === role)?.label || role || 'Sin rol';
        }

        function advisorByRole(project, role) {
            return (project.advisors || []).find(advisor => advisor.pivot?.rol_asesor === role) || null;
        }

        function activeAssignmentRoles() {
            return advisorView === 'thesis' ? THESIS_ROLES : ADVISOR_ROLES;
        }

        function activeAssignmentProjects() {
            return advisorView === 'thesis' ? projects.filter(project => project.is_thesis) : projects.filter(project => !project.is_thesis);
        }

        function advisorForTeacher(project, teacherId) {
            return (project.advisors || []).find(advisor => String(advisor.id) === String(teacherId)) || null;
        }

        function projectsForTeacher(teacherId) {
            return projects.filter(project => advisorForTeacher(project, teacherId));
        }

        function setAdvisorView(view) {
            if (advisorView === view) return;
            SGPIViewTransition.run(() => {
                advisorView = view;
                document.getElementById('projectAssignmentView').classList.toggle('d-none', view !== 'projects');
                document.getElementById('thesisCommitteeView').classList.toggle('d-none', view !== 'thesis');
                document.getElementById('teacherAdvisorView').classList.toggle('d-none', view !== 'teachers');
                document.getElementById('viewByProjectBtn').className = view === 'projects' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                document.getElementById('viewThesisCommitteeBtn').className = view === 'thesis' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                document.getElementById('viewByTeacherBtn').className = view === 'teachers' ? 'btn btn-primary' : 'btn btn-outline-secondary';
                document.getElementById('saveAllAdvisorsBtn').classList.toggle('d-none', view === 'teachers');
                if (view === 'teachers') renderTeacherAdvisorView();
                updateSaveAllButton();
            });
        }

        function teacherOptions(selectedId, blockedIds = []) {
            const blocked = Array.isArray(blockedIds) ? blockedIds.map(String) : [String(blockedIds || '')];
            const options = ['<option value="">Sin asignar</option>'];
            teachers.forEach(teacher => {
                const disabled = blocked.includes(String(teacher.id)) ? 'disabled' : '';
                const selected = selectedId && String(teacher.id) === String(selectedId) ? 'selected' : '';
                const optionStyle = Number(teacher.perfil_id) === 1 ? 'style="color:#6f42c1;font-weight:600;"' : '';
                options.push(`<option value="${escapeHtml(teacher.id)}" ${selected} ${disabled} ${optionStyle}>${escapeHtml(fullName(teacher))} (${escapeHtml(teacher.id)}) - ${escapeHtml(profileName(teacher))}</option>`);
            });
            return options.join('');
        }

        async function loadTeachers() {
            const [adminsResponse, teachersResponse] = await Promise.all([
                api.get('/users', { perfil_id: 1, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 }),
                api.get('/users', { perfil_id: 2, status: 'active', compact: 1, per_page: 500, _cache_ttl: 60000 })
            ]);
            teachers = [...(adminsResponse.data || []), ...(teachersResponse.data || [])]
                .sort((a, b) => Number(a.perfil_id) - Number(b.perfil_id) || fullName(a).localeCompare(fullName(b)));
        }

        async function loadProjects() {
            document.getElementById('projectsTable').innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>';
            document.getElementById('thesisCommitteeTable').innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>';

            try {
                const params = {};
                const semester = document.getElementById('semesterFilter').value;
                if (semester) params.semestre = semester;
                params.per_page = 100;

                const response = await api.get('/projects', params);
                projects = response.data || [];
                renderAssignmentTable('projectsTable', 'projects', ADVISOR_ROLES, projects.filter(project => !project.is_thesis), 'No hay proyectos integradores', 6);
                renderAssignmentTable('thesisCommitteeTable', 'thesis', THESIS_ROLES, projects.filter(project => project.is_thesis), 'No hay tesis marcadas', 7);
                renderTeacherAdvisorView();
                updateSaveAllButton();
            } catch (error) {
                showAlert('#alertContainer', 'danger', 'Error cargando proyectos: ' + error.message);
                document.getElementById('projectsTable').innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error al cargar proyectos</td></tr>';
                document.getElementById('thesisCommitteeTable').innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar tesis</td></tr>';
                renderTeacherAdvisorView();
                updateSaveAllButton();
            }
        }

        function renderAssignmentTable(tableId, view, roles, rows, emptyText, colspan) {
            const tbody = document.getElementById(tableId);
            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted py-4">${emptyText}</td></tr>`;
                return;
            }

            tbody.innerHTML = rows.map(project => {
                const selectedByRole = Object.fromEntries(roles.map(role => [role.key, advisorByRole(project, role.key)]));
                const blockedByRole = roleKey => roles
                    .filter(role => role.key !== roleKey)
                    .map(role => selectedByRole[role.key]?.id || '')
                    .filter(Boolean);
                return `
                    <tr id="${view}-row-${project.id}">
                        <td>
                            <strong>${escapeHtml(project.title)}</strong>
                            <div class="small text-muted">${escapeHtml(projectActiveAuthors(project, 'Sin integrantes activos'))}</div>
                            ${project.is_thesis ? '<span class="badge bg-success mt-1">Tesis</span>' : ''}
                        </td>
                        <td>${project.semestre || '-'}</td>
                        <td>${escapeHtml(project.subject_group?.nombre || project.subjectGroup?.nombre || '-')}</td>
                        ${roles.map(role => `
                            <td>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge ${role.badge}"><i class="bi ${role.icon}"></i> ${role.label}</span>
                                    <small class="text-muted">${role.help}</small>
                                </div>
                                <select class="form-select form-select-sm" id="${role.key}-${project.id}" onchange="syncBlockedOptions(${project.id}); markAdvisorRow(${project.id})">
                                    ${teacherOptions(selectedByRole[role.key]?.id, blockedByRole(role.key))}
                                </select>
                            </td>`).join('')}
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" onclick="revertAdvisorRow(${project.id})" title="Deshacer cambios"><i class="bi bi-arrow-counterclockwise"></i></button>
                                <button class="btn btn-outline-secondary" onclick="showProjectDetails(${project.id})" title="Ver proyecto"><i class="bi bi-folder2-open"></i></button>
                            </div>
                        </td>
                    </tr>`;
            }).join('');
        }

        function renderTeacherAdvisorView() {
            const container = document.getElementById('teacherAdvisorContainer');
            if (!container) return;

            const search = (document.getElementById('teacherSearch')?.value || '').trim().toLowerCase();
            const roleFilter = document.getElementById('teacherRoleFilter')?.value || '';
            const teachersWithProjects = teachers.map(teacher => {
                const assignedProjects = projectsForTeacher(teacher.id)
                    .filter(project => !roleFilter || advisorForTeacher(project, teacher.id)?.pivot?.rol_asesor === roleFilter);
                const primaryCount = assignedProjects.filter(project => advisorForTeacher(project, teacher.id)?.pivot?.rol_asesor === 'primario').length;
                const secondaryCount = assignedProjects.filter(project => advisorForTeacher(project, teacher.id)?.pivot?.rol_asesor === 'secundario').length;
                const thesisAdvisorCount = assignedProjects.filter(project => advisorForTeacher(project, teacher.id)?.pivot?.rol_asesor === 'asesor').length;
                const reviewerOneCount = assignedProjects.filter(project => advisorForTeacher(project, teacher.id)?.pivot?.rol_asesor === 'revisor_1').length;
                const reviewerTwoCount = assignedProjects.filter(project => advisorForTeacher(project, teacher.id)?.pivot?.rol_asesor === 'revisor_2').length;
                return { teacher, assignedProjects, primaryCount, secondaryCount, thesisAdvisorCount, reviewerOneCount, reviewerTwoCount };
            }).filter(item => {
                const haystack = `${item.teacher.id} ${fullName(item.teacher)} ${item.teacher.email || ''}`.toLowerCase();
                return !search || haystack.includes(search);
            }).sort((a, b) => b.assignedProjects.length - a.assignedProjects.length || fullName(a.teacher).localeCompare(fullName(b.teacher)));

            const assignedTeachers = teachers.filter(teacher => projectsForTeacher(teacher.id).length > 0).length;
            const totalAdvisories = teachers.reduce((total, teacher) => total + projectsForTeacher(teacher.id).length, 0);
            document.getElementById('summaryTeachers').textContent = teachers.length;
            document.getElementById('summaryAssigned').textContent = assignedTeachers;
            document.getElementById('summaryProjects').textContent = totalAdvisories;

            if (!teachersWithProjects.length) {
                container.innerHTML = '<div class="col-12"><div class="alert alert-info mb-0">No hay asesores que coincidan con la búsqueda o el filtro seleccionado.</div></div>';
                return;
            }

            container.innerHTML = teachersWithProjects.map(item => {
                const teacher = item.teacher;
                const projectCards = item.assignedProjects.length ? item.assignedProjects.map(project => {
                    const advisor = advisorForTeacher(project, teacher.id);
                    const role = advisor?.pivot?.rol_asesor || 'asesor';
                    const roleConfig = [...ADVISOR_ROLES, ...THESIS_ROLES].find(item => item.key === role) || ADVISOR_ROLES[0];
                    const roleClass = roleConfig.badge;
                    const groupName = project.subject_group?.nombre || project.subjectGroup?.nombre || '-';
                    return `
                        <div class="advisor-project-item">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div>
                                    <h6 class="mb-1">${escapeHtml(project.title)}</h6>
                                    <div class="small text-muted">${escapeHtml(projectActiveAuthors(project))}</div>
                                </div>
                                <span class="badge ${roleClass}">${escapeHtml(roleConfig.label)}</span>
                            </div>
                            <div class="row g-2 mt-2 small">
                                <div class="col-md-4"><strong>Semestre:</strong> ${escapeHtml(project.semestre || '-')}</div>
                                <div class="col-md-4"><strong>Grupo:</strong> ${escapeHtml(groupName)}</div>
                                <div class="col-md-4"><strong>Empresa:</strong> ${escapeHtml(project.company_name || '-')}</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="showProjectDetails(${project.id})">
                                    <i class="bi bi-eye"></i> Ver detalles
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="focusProjectAssignment(${project.id}, 'projects')">
                                    <i class="bi bi-pencil-square"></i> Ajustar asesores
                                </button>
                                ${project.is_thesis ? `<button type="button" class="btn btn-sm btn-outline-success" onclick="focusProjectAssignment(${project.id}, 'thesis')">
                                    <i class="bi bi-mortarboard"></i> Ajustar comité
                                </button>` : ''}
                            </div>
                        </div>`;
                }).join('') : '<p class="text-muted mb-0">Sin proyectos asesorados en este filtro.</p>';

                return `
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100 advisor-teacher-card">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <strong>${escapeHtml(fullName(teacher))}</strong>
                                    <div class="small opacity-75">${escapeHtml(teacher.id)}${teacher.email ? ' · ' + escapeHtml(teacher.email) : ''}</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    ${profileBadge(teacher)}
                                    <span class="badge bg-light text-primary">${item.assignedProjects.length} proyectos</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-primary">${item.primaryCount} primario</span>
                                    <span class="badge bg-info text-dark">${item.secondaryCount} secundario</span>
                                    <span class="badge bg-success">${item.thesisAdvisorCount} asesor tesis</span>
                                    <span class="badge bg-info text-dark">${item.reviewerOneCount} revisor 1</span>
                                    <span class="badge bg-warning text-dark">${item.reviewerTwoCount} revisor 2</span>
                                </div>
                                <div class="advisor-project-list">
                                    ${projectCards}
                                </div>
                            </div>
                        </div>
                    </div>`;
            }).join('');
        }

        function focusProjectAssignment(projectId, view = 'projects') {
            setAdvisorView(view);
            const firstRole = activeAssignmentRoles()[0]?.key || 'primario';
            const rowSelect = document.getElementById(`${firstRole}-${projectId}`);
            if (!rowSelect) return;
            rowSelect.closest('tr')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            rowSelect.closest('tr')?.classList.add('table-warning');
            setTimeout(() => rowSelect.closest('tr')?.classList.remove('table-warning'), 1600);
        }

        async function showProjectDetails(projectId) {
            try {
                const project = await api.get(`/projects/${projectId}`);
                const students = (project.students || []).map(student => `${escapeHtml(fullName(student))} (${escapeHtml(student.id)})`).join('<br>') || '<span class="text-muted">Sin integrantes</span>';
                const advisors = (project.advisors || []).map(advisor => `${escapeHtml(fullName(advisor))} ${advisorRoleBadge(advisor)}`).join('<br>') || '<span class="text-muted">Sin asesores</span>';
                const subjects = (project.asignaturas || []).map(subject => `<span class="badge bg-primary me-1 mb-1">${escapeHtml(subject.nombre || subject.clave || '')}</span>`).join('') || '<span class="text-muted">Sin asignaturas</span>';

                Swal.fire({
                    title: escapeHtml(project.title),
                    html: `
                        <div class="text-start">
                            <p class="text-muted">${escapeHtml(project.description || 'Sin descripción')}</p>
                            <p><strong>Semestre:</strong> ${escapeHtml(project.semestre || '-')} | <strong>Año:</strong> ${escapeHtml(project.year || '-')}</p>
                            <p><strong>Grupo / carga:</strong><br>${escapeHtml(project.subject_group?.nombre || '-')}</p>
                            <p><strong>Integrantes:</strong><br>${students}</p>
                            <p><strong>Asignaturas:</strong><br>${subjects}</p>
                            <p><strong>Empresa:</strong><br>${escapeHtml(project.company_name || '-')}</p>
                            <p><strong>Responsable empresa:</strong><br>${escapeHtml(project.company_contact_name || '-')} ${escapeHtml(project.company_contact_position || '')}</p>
                            <p><strong>Asesores:</strong><br>${advisors}</p>
                        </div>
                    `,
                    width: 760,
                    confirmButtonText: 'Cerrar'
                });
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }

        function syncBlockedOptions(projectId) {
            const roles = activeAssignmentRoles();
            const selectedValues = Object.fromEntries(roles.map(role => {
                const select = document.getElementById(`${role.key}-${projectId}`);
                return [role.key, select?.value || ''];
            }));

            roles.forEach(role => {
                const select = document.getElementById(`${role.key}-${projectId}`);
                if (!select) return;
                [...select.options].forEach(option => {
                    option.disabled = option.value !== '' && roles.some(other => other.key !== role.key && selectedValues[other.key] === option.value);
                });
            });
        }

        function markAdvisorRow(projectId) {
            const project = projects.find(item => Number(item.id) === Number(projectId));
            const row = document.getElementById(`${advisorView}-row-${projectId}`);
            if (!project || !row) return;

            const hasChanges = activeAssignmentRoles().some(role => {
                const currentValue = advisorByRole(project, role.key)?.id || '';
                const nextValue = document.getElementById(`${role.key}-${projectId}`)?.value || '';
                return currentValue !== nextValue;
            });

            row.classList.toggle('advisor-row-editing', hasChanges);
            row.classList.toggle('table-warning', hasChanges);
            updateSaveAllButton();
        }

        function changedAdvisorProjectIds() {
            return activeAssignmentProjects()
                .filter(project => {
                    return activeAssignmentRoles().some(role => {
                        const select = document.getElementById(`${role.key}-${project.id}`);
                        if (!select) return false;
                        return (advisorByRole(project, role.key)?.id || '') !== select.value;
                    });
                })
                .map(project => project.id);
        }

        function updateSaveAllButton() {
            const button = document.getElementById('saveAllAdvisorsBtn');
            if (!button) return;

            if (advisorView === 'teachers') {
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-save"></i> Guardar cambios';
                return;
            }

            const count = activeAssignmentProjects().reduce((total, project) => {
                return total + activeAssignmentRoles().reduce((roleTotal, role) => {
                    const select = document.getElementById(`${role.key}-${project.id}`);
                    if (!select) return roleTotal;
                    const currentValue = advisorByRole(project, role.key)?.id || '';
                    return roleTotal + (currentValue !== select.value ? 1 : 0);
                }, 0);
            }, 0);
            button.disabled = count === 0;
            button.innerHTML = count
                ? `<i class="bi bi-save"></i> Guardar cambios (${count})`
                : '<i class="bi bi-save"></i> Guardar cambios';
        }

        function revertAdvisorRow(projectId) {
            const project = projects.find(item => Number(item.id) === Number(projectId));
            if (!project) return;

            activeAssignmentRoles().forEach(role => {
                const select = document.getElementById(`${role.key}-${projectId}`);
                if (select) select.value = advisorByRole(project, role.key)?.id || '';
            });
            syncBlockedOptions(projectId);
            markAdvisorRow(projectId);
            swalToast('info', 'Cambios de la fila revertidos');
        }

        async function assignAdvisor(projectId, role, userId, adminPassword) {
            if (!userId) return;
            await api.post(`/projects/${projectId}/advisors`, {
                user_id: userId,
                rol_asesor: role,
                admin_password: adminPassword
            });
        }

        async function removeAdvisor(projectId, userId, adminPassword) {
            if (!userId) return;
            await api.delete(`/projects/${projectId}/advisors/${encodeURIComponent(userId)}`, { admin_password: adminPassword });
        }
        async function saveAdvisors(projectId) {
            const project = projects.find(item => Number(item.id) === Number(projectId));
            if (!project) return;

            const roles = activeAssignmentRoles();
            const nextValues = roles.map(role => document.getElementById(`${role.key}-${projectId}`)?.value || '').filter(Boolean);
            if (new Set(nextValues).size !== nextValues.length) {
                showAlert('#alertContainer', 'danger', 'Los roles de la fila deben ser personas diferentes.');
                return;
            }

            const changed = roles.some(role => {
                const currentValue = advisorByRole(project, role.key)?.id || '';
                const nextValue = document.getElementById(`${role.key}-${projectId}`)?.value || '';
                return currentValue !== nextValue;
            });
            if (!changed) {
                swalToast('info', 'No hay cambios por guardar');
                return;
            }

            const adminPassword = await promptPassword({
                title: 'Confirmar modificación de asesores',
                inputPlaceholder: 'Contraseña del administrador actual',
                confirmButtonText: 'Autorizar'
            });
            if (!adminPassword) return;

            try {
                await applyAdvisorChanges(projectId, adminPassword);

                showAlert('#alertContainer', 'success', 'Asesores actualizados correctamente');
                await loadProjects();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error actualizando asesores');
            }
        }

        async function saveAllAdvisorChanges() {
            const changedProjectIds = changedAdvisorProjectIds();
            if (!changedProjectIds.length) {
                swalToast('info', 'No hay cambios por guardar');
                return;
            }

            const invalidProjectId = changedProjectIds.find(projectId => {
                const nextValues = activeAssignmentRoles().map(role => document.getElementById(`${role.key}-${projectId}`)?.value || '').filter(Boolean);
                return new Set(nextValues).size !== nextValues.length;
            });
            if (invalidProjectId) {
                showAlert('#alertContainer', 'danger', 'Los roles de la fila deben ser personas diferentes.');
                document.getElementById(`${advisorView}-row-${invalidProjectId}`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const adminPassword = await promptPassword({
                title: 'Confirmar modificación de asesores',
                inputPlaceholder: 'Contraseña del administrador actual',
                confirmButtonText: 'Guardar cambios'
            });
            if (!adminPassword) return;

            const button = document.getElementById('saveAllAdvisorsBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

            try {
                for (const projectId of changedProjectIds) {
                    await applyAdvisorChanges(projectId, adminPassword);
                }

                showAlert('#alertContainer', 'success', 'Cambios de asesores guardados correctamente');
                await loadProjects();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error actualizando asesores');
                updateSaveAllButton();
            } finally {
                button.innerHTML = originalText;
                updateSaveAllButton();
            }
        }

        async function applyAdvisorChanges(projectId, adminPassword) {
            const project = projects.find(item => Number(item.id) === Number(projectId));
            if (!project) return;

            const roles = activeAssignmentRoles();
            const nextValues = roles.map(role => document.getElementById(`${role.key}-${projectId}`)?.value || '').filter(Boolean);
            if (new Set(nextValues).size !== nextValues.length) {
                throw new Error('Los roles de la fila deben ser personas diferentes.');
            }

            for (const role of roles) {
                const currentValue = advisorByRole(project, role.key)?.id || '';
                const nextValue = document.getElementById(`${role.key}-${projectId}`)?.value || '';
                if (currentValue && currentValue !== nextValue) await removeAdvisor(projectId, currentValue, adminPassword);
            }

            for (const role of roles) {
                const nextValue = document.getElementById(`${role.key}-${projectId}`)?.value || '';
                await assignAdvisor(projectId, role.key, nextValue, adminPassword);
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await loadTeachers();
            await loadProjects();
            const params = new URLSearchParams(window.location.search);
            if (params.get('view')) setAdvisorView(params.get('view'));
            if (params.get('project')) focusProjectAssignment(params.get('project'), params.get('view') || 'projects');
        });
    </script>
</body>
</html>
