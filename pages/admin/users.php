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
    <title>Gestion de Usuarios - <?= APP_NAME ?></title>
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
                        <h1 class="mb-1">Gestion de Usuarios</h1>
                        <p class="text-muted mb-0" id="statusDescription">Mostrando perfiles activos</p>
                    </div>
                    <a href="/pages/admin/user-create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Usuario
                    </a>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Filtro de estado de usuarios">
                    <button type="button" class="btn btn-primary" id="filterActive" onclick="setStatusFilter('active')"><i class="bi bi-check-circle"></i> Activos</button>
                    <button type="button" class="btn btn-outline-secondary" id="filterInactive" onclick="setStatusFilter('inactive')"><i class="bi bi-pause-circle"></i> Inactivos</button>
                    <button type="button" class="btn btn-outline-secondary" id="filterAll" onclick="setStatusFilter('all')"><i class="bi bi-list-ul"></i> Todos</button>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Filtro de perfil de usuarios">
                    <button type="button" class="btn btn-primary" id="profileAll" onclick="setProfileFilter('all')"><i class="bi bi-list-ul"></i> Todos los perfiles</button>
                    <button type="button" class="btn btn-outline-secondary" id="profileStudents" onclick="setProfileFilter('3')"><i class="bi bi-mortarboard"></i> Estudiantes</button>
                    <button type="button" class="btn btn-outline-secondary" id="profileTeachers" onclick="setProfileFilter('2')"><i class="bi bi-person-workspace"></i> Docentes</button>
                    <button type="button" class="btn btn-outline-secondary" id="profileAdmins" onclick="setProfileFilter('1')"><i class="bi bi-shield-lock"></i> Administrativos</button>
                </div>

                <div class="row g-3 mb-3 d-none" id="studentGroupFilters">
                    <div class="col-md-3">
                        <label class="form-label" for="semesterFilter">Semestre</label>
                        <select class="form-select" id="semesterFilter" onchange="loadGroupsForFilter(); loadUsers(1);">
                            <option value="">Todos</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="groupFilter">Grupo</label>
                        <select class="form-select" id="groupFilter" onchange="loadUsers(1)">
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end justify-content-md-end">
                        <button class="btn btn-outline-primary" onclick="openGroupControl()"><i class="bi bi-people"></i> Control de grupos</button>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Matricula/Nomina</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Perfil</th>
                                        <th>Grupo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTable">
                                    <tr><td colspan="7" class="text-center py-4"><div class="spinner-custom"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <nav id="paginationContainer" class="mt-4"></nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="groupControlModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-people"></i> Control de grupos de estudiantes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Semestre</label>
                            <select class="form-select" id="controlSemester" onchange="loadControlGroups()">
                                <option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex align-items-end justify-content-md-end">
                            <a href="/pages/admin/asignaturas.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Crear o editar grupos</a>
                        </div>
                    </div>
                    <div id="groupControlContent" class="row g-3"></div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/modal.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'http://127.0.0.1:8000/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script>
        let currentStatus = 'active';
        let currentProfile = 'all';
        let currentPage = 1;
        const usersPerPage = 12;
        let groups = [];
        let groupControlModal;

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function updateFilterButtons() {
            const statusButtons = { active: 'filterActive', inactive: 'filterInactive', all: 'filterAll' };
            Object.entries(statusButtons).forEach(([status, id]) => {
                const button = document.getElementById(id);
                button.className = status === currentStatus ? 'btn btn-primary' : 'btn btn-outline-secondary';
            });

            const profileButtons = { all: 'profileAll', '3': 'profileStudents', '2': 'profileTeachers', '1': 'profileAdmins' };
            Object.entries(profileButtons).forEach(([profile, id]) => {
                const button = document.getElementById(id);
                button.className = profile === currentProfile ? 'btn btn-primary' : 'btn btn-outline-secondary';
            });

            const labels = {
                active: 'Mostrando perfiles activos',
                inactive: 'Mostrando perfiles inactivos separados del resto',
                all: 'Mostrando todos los perfiles'
            };
            document.getElementById('statusDescription').textContent = labels[currentStatus];
            document.getElementById('studentGroupFilters').classList.toggle('d-none', currentProfile !== '3');
        }

        async function loadUsers(page = 1) {
            currentPage = page;
            updateFilterButtons();
            const tbody = document.getElementById('usersTable');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-custom"></div></td></tr>';

            try {
                const params = { page, per_page: usersPerPage };
                if (currentStatus !== 'all') params.status = currentStatus;
                if (currentProfile !== 'all') params.perfil_id = currentProfile;
                if (currentProfile === '3') {
                    const semester = document.getElementById('semesterFilter').value;
                    const group = document.getElementById('groupFilter').value;
                    if (semester) params.semestre = semester;
                    if (group) params.grupo = group;
                }

                const response = await api.get('/users', params);
                const users = response.data || [];
                tbody.innerHTML = '';

                if (users.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay usuarios para este filtro</td></tr>';
                    renderPagination(response);
                    return;
                }

                users.forEach(user => {
                    const profileNames = { 1: 'Administrativo', 2: 'Docente', 3: 'Estudiante' };
                    const isActive = Boolean(user.activo);
                    const isAdminUser = Number(user.perfil_id) === 1;
                    const statusBadge = isActive ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
                    const toggleClass = isActive ? 'btn-outline-warning' : 'btn-outline-success';
                    const toggleIcon = isActive ? 'bi-pause-circle' : 'bi-check-circle';
                    const toggleTitle = isActive ? 'Desactivar usuario' : 'Activar usuario';
                    const normalToggle = isAdminUser
                        ? `<button class="btn ${toggleClass}" disabled title="Administrador protegido"><i class="bi ${toggleIcon}"></i></button>`
                        : `<button class="btn ${toggleClass}" onclick="toggleUserStatus('${escapeHtml(user.id)}')" title="${toggleTitle}"><i class="bi ${toggleIcon}"></i></button>`;
                    const normalDelete = isAdminUser
                        ? `<button class="btn btn-outline-danger" disabled title="Administrador protegido"><i class="bi bi-trash"></i></button>`
                        : `<button class="btn btn-outline-danger" onclick="deleteUser('${escapeHtml(user.id)}')" title="Eliminar"><i class="bi bi-trash"></i></button>`;
                    const protectedButton = isAdminUser
                        ? `<button class="btn btn-outline-secondary" onclick="protectedAdminAction('${escapeHtml(user.id)}')" title="Accion protegida"><i class="bi bi-shield-lock"></i></button>`
                        : '';

                    tbody.innerHTML += `
                        <tr class="${isActive ? '' : 'table-light'}">
                            <td><strong>${escapeHtml(user.id)}</strong></td>
                            <td>${escapeHtml(user.nombres)} ${escapeHtml(user.apa)} ${escapeHtml(user.ama)}</td>
                            <td>${escapeHtml(user.email)}</td>
                            <td><span class="badge bg-secondary">${profileNames[user.perfil_id] || 'N/A'}</span></td>
                            <td>${Number(user.perfil_id) === 3 ? `<span class="badge bg-primary">${escapeHtml(user.semestre || '-')} ${escapeHtml(user.grupo || '')}</span>` : '<span class="text-muted small">-</span>'}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="/pages/admin/user-edit.php?id=${encodeURIComponent(user.id)}" class="btn btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    ${normalToggle}
                                    ${normalDelete}
                                    ${protectedButton}
                                </div>
                            </td>
                        </tr>`;
                });

                renderPagination(response);
            } catch (error) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar usuarios</td></tr>';
            }
        }


        function renderPagination(pagination) {
            const container = document.getElementById('paginationContainer');
            const current = Number(pagination.current_page || 1);
            const last = Number(pagination.last_page || 1);
            const total = Number(pagination.total || 0);
            const from = pagination.from || 0;
            const to = pagination.to || 0;

            if (last <= 1) {
                container.innerHTML = total > 0
                    ? `<p class="text-muted small mb-0">Mostrando ${from}-${to} de ${total} usuarios</p>`
                    : '';
                return;
            }

            const start = Math.max(1, current - 2);
            const end = Math.min(last, current + 2);
            const pages = [];

            if (start > 1) {
                pages.push(pageButton(1, current));
                if (start > 2) pages.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }

            for (let page = start; page <= end; page++) {
                pages.push(pageButton(page, current));
            }

            if (end < last) {
                if (end < last - 1) pages.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
                pages.push(pageButton(last, current));
            }

            container.innerHTML = `
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <p class="text-muted small mb-0">Mostrando ${from}-${to} de ${total} usuarios</p>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item ${current === 1 ? 'disabled' : ''}">
                            <button class="page-link" type="button" onclick="loadUsers(${current - 1})">Anterior</button>
                        </li>
                        ${pages.join('')}
                        <li class="page-item ${current === last ? 'disabled' : ''}">
                            <button class="page-link" type="button" onclick="loadUsers(${current + 1})">Siguiente</button>
                        </li>
                    </ul>
                </div>`;
        }

        function pageButton(page, current) {
            return `
                <li class="page-item ${page === current ? 'active' : ''}">
                    <button class="page-link" type="button" onclick="loadUsers(${page})">${page}</button>
                </li>`;
        }
        function setStatusFilter(status) {
            currentStatus = status;
            loadUsers(1);
        }

        function setProfileFilter(profile) {
            currentProfile = profile;
            if (profile === '3') loadGroupsForFilter();
            loadUsers(1);
        }

        async function loadGroupsForFilter() {
            const semester = document.getElementById('semesterFilter').value;
            const select = document.getElementById('groupFilter');
            select.innerHTML = '<option value="">Todos</option>';
            const params = {};
            if (semester) params.semestre = semester;
            groups = await api.get('/subject-groups', params);
            groups.forEach(group => {
                select.innerHTML += `<option value="${escapeHtml(group.grupo)}">${escapeHtml(group.semestre)} ${escapeHtml(group.grupo)} - ${escapeHtml(group.nombre)}</option>`;
            });
        }

        async function openGroupControl() {
            if (!groupControlModal) groupControlModal = new bootstrap.Modal(document.getElementById('groupControlModal'));
            document.getElementById('controlSemester').value = document.getElementById('semesterFilter').value || '5';
            await loadControlGroups();
            groupControlModal.show();
        }

        async function loadControlGroups() {
            const semester = document.getElementById('controlSemester').value;
            const box = document.getElementById('groupControlContent');
            box.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border"></div></div>';
            const [semesterGroups, studentsResponse] = await Promise.all([
                api.get('/subject-groups', { semestre: semester }),
                api.get('/users', { perfil_id: 3, status: 'active', per_page: 100 })
            ]);
                const students = (studentsResponse.data || []).filter(student => String(student.semestre || '') === String(semester));
                box.innerHTML = semesterGroups.map(group => {
                    const members = students.filter(student => String(student.grupo || '').toUpperCase() === String(group.grupo || '').toUpperCase());
                    const available = students.filter(student => String(student.grupo || '').toUpperCase() !== String(group.grupo || '').toUpperCase());
                    return `
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong>${escapeHtml(group.semestre)} ${escapeHtml(group.grupo)} - ${escapeHtml(group.nombre)}</strong>
                                <span class="badge bg-light text-primary">${members.length} alumnos</span>
                            </div>
                            <div class="card-body">
                                <div class="input-group input-group-sm mb-3">
                                    <select class="form-select" id="addStudent-${group.id}">
                                        <option value="">Agregar alumno al grupo</option>
                                        ${available.map(student => `<option value="${escapeHtml(student.id)}">${escapeHtml(student.id)} - ${escapeHtml(student.nombres)} ${escapeHtml(student.apa || '')} ${student.grupo ? `(actual: ${escapeHtml(student.grupo)})` : '(sin grupo)'}</option>`).join('')}
                                    </select>
                                    <button class="btn btn-outline-primary" onclick="assignStudentToGroup(${group.id})"><i class="bi bi-plus"></i></button>
                                </div>
                                ${members.length ? members.map(student => `<div class="d-flex justify-content-between align-items-center border-bottom py-2 gap-2"><span>${escapeHtml(student.id)} - ${escapeHtml(student.nombres)} ${escapeHtml(student.apa || '')}</span><div class="btn-group btn-group-sm"><a class="btn btn-outline-primary" href="/pages/admin/user-edit.php?id=${encodeURIComponent(student.id)}"><i class="bi bi-pencil"></i></a><button class="btn btn-outline-warning" onclick="removeStudentFromGroup('${escapeHtml(student.id)}')" title="Quitar del grupo"><i class="bi bi-person-dash"></i></button></div></div>`).join('') : '<p class="text-muted mb-0">Sin alumnos en este grupo.</p>'}
                            </div>
                        </div>
                    </div>`;
            }).join('') || '<div class="col-12"><p class="text-muted">No hay grupos creados para este semestre. Crea uno desde Asignaturas > Gestionar Cargas.</p></div>';
        }

        async function assignStudentToGroup(groupId) {
            const select = document.getElementById(`addStudent-${groupId}`);
            const studentId = select.value;
            if (!studentId) return;
            try {
                await api.post(`/subject-groups/${groupId}/students`, { student_ids: [studentId] });
                swalToast('success', 'Alumno agregado al grupo');
                await loadControlGroups();
                await loadUsers(currentPage);
            } catch (error) {
                showError(error.message || 'Error agregando alumno al grupo');
            }
        }

        async function removeStudentFromGroup(studentId) {
            const confirmed = await confirmAction({
                title: 'Quitar alumno del grupo',
                text: 'El alumno quedara sin semestre y grupo asignado hasta que lo reasignes.',
                confirmButtonText: 'Si, quitar'
            });
            if (!confirmed) return;
            try {
                await api.put(`/users/${studentId}`, { semestre: null, grupo: null });
                swalToast('success', 'Alumno removido del grupo');
                await loadControlGroups();
                await loadUsers(currentPage);
            } catch (error) {
                showError(error.message || 'Error quitando alumno del grupo');
            }
        }

        async function toggleUserStatus(userId, adminPassword = null) {
            try {
                const payload = adminPassword ? { admin_password: adminPassword } : {};
                await api.post(`/users/${userId}/toggle-active`, payload);
                await loadUsers(currentPage);
            } catch (error) {
                showError(error.message || 'Error al cambiar el estado del usuario');
            }
        }

        async function deleteUser(userId, adminPassword = null) {
            const confirmed = await confirmAction({
                title: 'Eliminar usuario',
                text: '¿Estas seguro de eliminar este usuario?',
                confirmButtonText: 'Si, eliminar'
            });
            if (!confirmed) return;

            const payload = adminPassword ? { admin_password: adminPassword } : null;
            api.delete(`/users/${userId}`, payload).then(() => {
                swalToast('success', 'Usuario eliminado');
                loadUsers(currentPage);
            }).catch(error => showError(error.message || 'Error al eliminar usuario'));
        }

        async function protectedAdminAction(userId) {
            const action = await promptAdminAction();
            if (!action) return;

            const password = await promptPassword({
                title: 'Autorizar accion protegida',
                inputPlaceholder: 'Contraseña del administrador actual'
            });
            if (!password) return;

            if (action === 'DESACTIVAR') await toggleUserStatus(userId, password);
            else await deleteUser(userId, password);
        }

        function showError(message) {
            if (window.swalToast && window.swalToast('danger', message)) return;

            document.getElementById('alertContainer').innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> ${escapeHtml(message)}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
        }

        document.addEventListener('DOMContentLoaded', () => loadUsers());
    </script>
</body>
</html>
