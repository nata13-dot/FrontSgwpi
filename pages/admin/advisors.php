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
    <title>Asignacion de Asesores - <?= APP_NAME ?></title>
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
                        <h1 class="mb-1">Asignacion de Asesores</h1>
                        <p class="text-muted mb-0">Asigna un asesor primario y uno secundario por proyecto.</p>
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
                            <option value="8">8 - Titulacion</option>
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
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'http://127.0.0.1:8000/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    <script>
        let projects = [];
        let teachers = [];

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || 'Sin nombre';
        }

        function advisorByRole(project, role) {
            return (project.advisors || []).find(advisor => advisor.pivot?.rol_asesor === role) || null;
        }

        function teacherOptions(selectedId, blockedId) {
            const options = ['<option value="">Sin asignar</option>'];
            teachers.forEach(teacher => {
                const disabled = blockedId && String(teacher.id) === String(blockedId) ? 'disabled' : '';
                const selected = selectedId && String(teacher.id) === String(selectedId) ? 'selected' : '';
                options.push(`<option value="${escapeHtml(teacher.id)}" ${selected} ${disabled}>${escapeHtml(fullName(teacher))} (${escapeHtml(teacher.id)})</option>`);
            });
            return options.join('');
        }

        async function loadTeachers() {
            const response = await api.get('/users', { perfil_id: 2, status: 'active', per_page: 100 });
            teachers = response.data || [];
        }

        async function loadProjects() {
            const tbody = document.getElementById('projectsTable');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>';

            try {
                const params = {};
                const semester = document.getElementById('semesterFilter').value;
                if (semester) params.semestre = semester;
                params.per_page = 100;

                const response = await api.get('/projects', params);
                projects = response.data || [];

                if (!projects.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay proyectos</td></tr>';
                    return;
                }

                tbody.innerHTML = projects.map(project => {
                    const primary = advisorByRole(project, 'primario');
                    const secondary = advisorByRole(project, 'secundario');
                    return `
                        <tr>
                            <td>
                                <strong>${escapeHtml(project.title)}</strong>
                                <div class="small text-muted">${escapeHtml(project.authors || '')}</div>
                            </td>
                            <td>${project.semestre || '-'}</td>
                            <td>${escapeHtml(project.subject_group?.nombre || project.subjectGroup?.nombre || '-')}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-primary"><i class="bi bi-award"></i> Primario</span>
                                    <small class="text-muted">Responsable principal</small>
                                </div>
                                <select class="form-select form-select-sm border-primary" id="primary-${project.id}" onchange="syncBlockedOptions(${project.id})">
                                    ${teacherOptions(primary?.id, secondary?.id)}
                                </select>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-info text-dark"><i class="bi bi-person-check"></i> Secundario</span>
                                    <small class="text-muted">Apoyo academico</small>
                                </div>
                                <select class="form-select form-select-sm border-info" id="secondary-${project.id}" onchange="syncBlockedOptions(${project.id})">
                                    ${teacherOptions(secondary?.id, primary?.id)}
                                </select>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="saveAdvisors(${project.id})" title="Guardar asesores"><i class="bi bi-save"></i></button>
                                    <button class="btn btn-outline-secondary" onclick="showProjectDetails(${project.id})" title="Ver proyecto"><i class="bi bi-folder2-open"></i></button>
                                </div>
                            </td>
                        </tr>`;
                }).join('');
            } catch (error) {
                showAlert('#alertContainer', 'danger', 'Error cargando proyectos: ' + error.message);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error al cargar proyectos</td></tr>';
            }
        }

        async function showProjectDetails(projectId) {
            try {
                const project = await api.get(`/projects/${projectId}`);
                const students = (project.students || []).map(student => `${escapeHtml(fullName(student))} (${escapeHtml(student.id)})`).join('<br>') || '<span class="text-muted">Sin autores</span>';
                const advisors = (project.advisors || []).map(advisor => `${escapeHtml(fullName(advisor))} <span class="badge bg-secondary">${escapeHtml(advisor.pivot?.rol_asesor || '')}</span>`).join('<br>') || '<span class="text-muted">Sin asesores</span>';
                const subjects = (project.asignaturas || []).map(subject => `<span class="badge bg-primary me-1 mb-1">${escapeHtml(subject.nombre || subject.clave || '')}</span>`).join('') || '<span class="text-muted">Sin asignaturas</span>';

                Swal.fire({
                    title: escapeHtml(project.title),
                    html: `
                        <div class="text-start">
                            <p class="text-muted">${escapeHtml(project.description || 'Sin descripcion')}</p>
                            <p><strong>Semestre:</strong> ${escapeHtml(project.semestre || '-')} | <strong>Año:</strong> ${escapeHtml(project.year || '-')}</p>
                            <p><strong>Grupo / carga:</strong><br>${escapeHtml(project.subject_group?.nombre || '-')}</p>
                            <p><strong>Autores:</strong><br>${students}</p>
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
            const primarySelect = document.getElementById(`primary-${projectId}`);
            const secondarySelect = document.getElementById(`secondary-${projectId}`);
            const primary = primarySelect.value;
            const secondary = secondarySelect.value;

            [...secondarySelect.options].forEach(option => {
                option.disabled = option.value !== '' && option.value === primary;
            });
            [...primarySelect.options].forEach(option => {
                option.disabled = option.value !== '' && option.value === secondary;
            });
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

            const currentPrimary = advisorByRole(project, 'primario')?.id || '';
            const currentSecondary = advisorByRole(project, 'secundario')?.id || '';
            const nextPrimary = document.getElementById(`primary-${projectId}`).value;
            const nextSecondary = document.getElementById(`secondary-${projectId}`).value;

            if (nextPrimary && nextSecondary && nextPrimary === nextSecondary) {
                showAlert('#alertContainer', 'danger', 'El asesor primario y secundario deben ser docentes diferentes.');
                return;
            }

            const changed = currentPrimary !== nextPrimary || currentSecondary !== nextSecondary;
            if (!changed) {
                swalToast('info', 'No hay cambios por guardar');
                return;
            }

            const adminPassword = await promptPassword({
                title: 'Confirmar modificacion de asesores',
                inputPlaceholder: 'Contraseña del administrador actual',
                confirmButtonText: 'Autorizar'
            });
            if (!adminPassword) return;

            try {
                if (currentPrimary && currentPrimary !== nextPrimary) await removeAdvisor(projectId, currentPrimary, adminPassword);
                if (currentSecondary && currentSecondary !== nextSecondary) await removeAdvisor(projectId, currentSecondary, adminPassword);
                await assignAdvisor(projectId, 'primario', nextPrimary, adminPassword);
                await assignAdvisor(projectId, 'secundario', nextSecondary, adminPassword);

                showAlert('#alertContainer', 'success', 'Asesores actualizados correctamente');
                await loadProjects();
            } catch (error) {
                showAlert('#alertContainer', 'danger', error.message || 'Error actualizando asesores');
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await loadTeachers();
            await loadProjects();
        });
    </script>
</body>
</html>
