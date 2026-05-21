<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || !is_admin()) {
    redirect_to('/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Usuarios - <?= APP_NAME ?></title>
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
                        <h1 class="mb-1">Gestion de Usuarios</h1>
                        <p class="text-muted mb-0" id="statusDescription">Mostrando perfiles activos</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="downloadUsersExcelTemplate()">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Plantilla Excel
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="openUsersImportModal()">
                            <i class="bi bi-upload"></i> Cargar Excel
                        </button>
                        <button type="button" class="btn btn-primary" onclick="openUserModal()">
                            <i class="bi bi-plus-circle"></i> Nuevo Usuario
                        </button>
                    </div>
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

                <div class="row g-3 mb-3">
                    <div class="col-md-8 col-lg-6">
                        <label class="form-label" for="userSearchInput">Buscar usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" class="form-control" id="userSearchInput" placeholder="No. de Control, No. de empleado, nombre, correo o telefono" oninput="scheduleUsersSearch()">
                            <button type="button" class="btn btn-outline-secondary" onclick="clearUsersSearch()" title="Limpiar busqueda"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </div>
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
                                        <th>No. de Control, No. de empleado</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Perfil</th>
                                        <th>Grupo</th>
                                        <th>Proyecto</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTable">
                                    <tr><td colspan="8" class="text-center py-4"><div class="spinner-custom"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <nav id="paginationContainer" class="mt-4"></nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="usersImportModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="usersImportForm" onsubmit="importUsersExcel(event)">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-upload"></i> Cargar usuarios desde Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="usersImportAlert"></div>
                    <label class="form-label" for="usersImportFile">Archivo de plantilla</label>
                    <input type="file" class="form-control" id="usersImportFile" accept=".xls,.xlsx,.csv" required>
                    <div class="form-text">Puedes usar la plantilla descargada o guardarla como .xlsx antes de importarla.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="usersImportBtn"><i class="bi bi-upload"></i> Importar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="userFormModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="userModalForm" class="needs-validation" novalidate onsubmit="saveUserModal(event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalTitle"><i class="bi bi-person-plus"></i> Nuevo usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="userModalAlert"></div>
                        <input type="hidden" id="userModalEditingId">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="userId">No. de Control, No. de empleado</label>
                                <input type="text" class="form-control" id="userId" maxlength="10" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="userEmail">Email</label>
                                <input type="email" class="form-control" id="userEmail" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="userPhone">Telefono</label>
                                <input type="tel" class="form-control" id="userPhone" maxlength="200" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="userProfile">Perfil</label>
                                <select class="form-select" id="userProfile" required onchange="toggleUserStudentFields()">
                                    <option value="">Seleccionar...</option>
                                    <option value="1">Administrador</option>
                                    <option value="2">Docente</option>
                                    <option value="3">Estudiante</option>
                                </select>
                                <div class="form-text" id="userProfileHelp"></div>
                            </div>
                            <div class="col-md-4 user-student-field d-none">
                                <label class="form-label" for="userSemester">Semestre</label>
                                <select class="form-select" id="userSemester" onchange="loadUserModalGroups()">
                                    <option value="">Seleccionar...</option>
                                    <option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option>
                                </select>
                            </div>
                            <div class="col-md-4 user-student-field d-none">
                                <label class="form-label" for="userGroup">Grupo</label>
                                <select class="form-select" id="userGroup">
                                    <option value="">Selecciona un semestre</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="userActive">Estado</label>
                                <select class="form-select" id="userActive" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="userNames">Nombres</label>
                                <input type="text" class="form-control" id="userNames" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="userApa">Apellido paterno</label>
                                <input type="text" class="form-control" id="userApa">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="userAma">Apellido materno</label>
                                <input type="text" class="form-control" id="userAma">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="userAddress">Direccion</label>
                                <input type="text" class="form-control" id="userAddress" minlength="10" pattern="(?=.*\d)[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9\s#.,\-\/]+" placeholder="Calle, numero, colonia, municipio">
                                <div class="form-text">Separa los datos con comas (,).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="userPassword">Contraseña</label>
                                <input type="password" class="form-control" id="userPassword" minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="userPasswordConfirmation">Confirmar contraseña</label>
                                <input type="password" class="form-control" id="userPasswordConfirmation" minlength="6">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="userModalSaveBtn"><i class="bi bi-save"></i> Guardar</button>
                    </div>
                </form>
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
                            <button type="button" class="btn btn-primary" onclick="createControlGroup()">
                                <i class="bi bi-plus-circle"></i> Nuevo grupo
                            </button>
                        </div>
                    </div>
                    <div id="groupControlContent" class="row g-3"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="groupFormModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="groupForm" onsubmit="saveControlGroup(event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="groupFormTitle"><i class="bi bi-people"></i> Nuevo grupo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="groupFormAlert"></div>
                        <div class="mb-3">
                            <label class="form-label" for="groupFormName">Nombre</label>
                            <input type="text" class="form-control" id="groupFormName" maxlength="255" placeholder="Ej. Desarrollo de proyectos 5C" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="groupFormSemester">Semestre</label>
                                <select class="form-select" id="groupFormSemester" required>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="groupFormCode">Grupo</label>
                                <input type="text" class="form-control" id="groupFormCode" maxlength="20" placeholder="Ej. C" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="groupFormPeriod">Periodo</label>
                            <input type="text" class="form-control" id="groupFormPeriod" maxlength="100" placeholder="Ej. Enero-Junio 2026">
                        </div>
                        <p class="small text-muted mt-3 mb-0 d-none" id="groupFormMoveNotice">
                            Los alumnos asignados se moveran al nuevo semestre/grupo si cambias esos datos.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="groupFormSaveBtn">
                            <i class="bi bi-save"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/modal.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script>
        let currentStatus = 'active';
        let currentProfile = 'all';
        let currentPage = 1;
        const usersPerPage = 12;
        let groups = [];
        let controlGroups = [];
        let groupControlModal;
        let groupFormModal;
        let usersImportModal;
        let userFormModal;
        let editingGroupId = null;
        let loadedModalUser = null;
        let usersSearchTimer = null;

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
                const search = document.getElementById('userSearchInput')?.value.trim();
                if (search) params.q = search;
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
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay usuarios para este filtro</td></tr>';
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
                            <td>${Number(user.perfil_id) === 2 ? `<span class="badge bg-info text-dark">${Number(user.advising_projects_count || 0)} asesorias</span>` : (Number(user.perfil_id) === 3 ? (Number(user.student_projects_count || 0) > 0 ? '<span class="badge bg-success">Con proyecto</span>' : '<span class="badge bg-warning text-dark">Sin proyecto</span>') : '<span class="text-muted small">-</span>')}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" onclick="openUserModal('${escapeHtml(user.id)}')" title="Editar"><i class="bi bi-pencil"></i></button>
                                    ${normalToggle}
                                    ${protectedButton}
                                </div>
                            </td>
                        </tr>`;
                });

                renderPagination(response);
            } catch (error) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-4">Error al cargar usuarios</td></tr>';
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

        function scheduleUsersSearch() {
            clearTimeout(usersSearchTimer);
            usersSearchTimer = setTimeout(() => loadUsers(1), 350);
        }

        function clearUsersSearch() {
            document.getElementById('userSearchInput').value = '';
            loadUsers(1);
        }

        async function loadGroupsForFilter() {
            const semester = document.getElementById('semesterFilter').value;
            const select = document.getElementById('groupFilter');
            select.innerHTML = '<option value="">Todos</option>';
            const params = {};
            if (semester) params.semestre = semester;
            groups = await api.get('/subject-groups', { ...params, _cache_ttl: 60000 });
            groups.forEach(group => {
                select.innerHTML += `<option value="${escapeHtml(group.grupo)}">${escapeHtml(group.semestre)} ${escapeHtml(group.grupo)} - ${escapeHtml(group.nombre)}</option>`;
            });
        }

        function resetUserModal() {
            loadedModalUser = null;
            document.getElementById('userModalForm').reset();
            document.getElementById('userModalForm').classList.remove('was-validated');
            document.getElementById('userModalAlert').innerHTML = '';
            document.getElementById('userModalEditingId').value = '';
            document.getElementById('userId').readOnly = false;
            document.getElementById('userProfile').disabled = false;
            document.getElementById('userProfileHelp').textContent = '';
            document.getElementById('userPassword').required = true;
            document.getElementById('userPasswordConfirmation').required = true;
            document.getElementById('userActive').value = '1';
            toggleUserStudentFields();
        }

        async function openUserModal(userId = null) {
            if (!userFormModal) userFormModal = new bootstrap.Modal(document.getElementById('userFormModal'));
            resetUserModal();
            const isEdit = Boolean(userId);
            document.getElementById('userModalTitle').innerHTML = isEdit
                ? '<i class="bi bi-pencil"></i> Editar usuario'
                : '<i class="bi bi-person-plus"></i> Nuevo usuario';
            document.getElementById('userModalSaveBtn').innerHTML = isEdit
                ? '<i class="bi bi-save"></i> Guardar cambios'
                : '<i class="bi bi-save"></i> Guardar';

            if (isEdit) {
                loadedModalUser = await api.get(`/users/${userId}`);
                document.getElementById('userModalEditingId').value = loadedModalUser.id;
                document.getElementById('userId').value = loadedModalUser.id || '';
                document.getElementById('userId').readOnly = true;
                document.getElementById('userEmail').value = loadedModalUser.email || '';
                document.getElementById('userPhone').value = loadedModalUser.telefonos || '';
                document.getElementById('userProfile').value = loadedModalUser.perfil_id || '';
                document.getElementById('userProfile').disabled = true;
                document.getElementById('userProfileHelp').textContent = 'El rol ya definido no puede modificarse.';
                document.getElementById('userActive').value = loadedModalUser.activo ? '1' : '0';
                document.getElementById('userNames').value = loadedModalUser.nombres || '';
                document.getElementById('userApa').value = loadedModalUser.apa || '';
                document.getElementById('userAma').value = loadedModalUser.ama || '';
                document.getElementById('userAddress').value = loadedModalUser.direccion || '';
                document.getElementById('userSemester').value = loadedModalUser.semestre || '';
                await loadUserModalGroups(loadedModalUser.grupo || '');
                document.getElementById('userPassword').required = false;
                document.getElementById('userPasswordConfirmation').required = false;
                toggleUserStudentFields();
            }

            userFormModal.show();
        }

        function toggleUserStudentFields() {
            const isStudent = document.getElementById('userProfile').value === '3';
            document.querySelectorAll('.user-student-field').forEach(el => el.classList.toggle('d-none', !isStudent));
        }

        async function loadUserModalGroups(selected = '') {
            const semester = document.getElementById('userSemester').value;
            const select = document.getElementById('userGroup');
            select.innerHTML = semester ? '<option value="">Seleccionar grupo...</option>' : '<option value="">Selecciona un semestre</option>';
            if (!semester) return;
            const modalGroups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });
            modalGroups.forEach(group => {
                select.innerHTML += `<option value="${escapeHtml(group.grupo)}">${escapeHtml(group.semestre)} ${escapeHtml(group.grupo)} - ${escapeHtml(group.nombre)}</option>`;
            });
            select.value = selected || '';
        }

        async function saveUserModal(event) {
            event.preventDefault();
            const form = document.getElementById('userModalForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;

            const editingId = document.getElementById('userModalEditingId').value;
            const password = document.getElementById('userPassword').value;
            const passwordConfirmation = document.getElementById('userPasswordConfirmation').value;
            if ((password || passwordConfirmation) && password !== passwordConfirmation) {
                swalToast('danger', 'La nueva contraseña y su confirmacion no coinciden');
                return;
            }

            const payload = {
                email: document.getElementById('userEmail').value.trim(),
                activo: document.getElementById('userActive').value === '1',
                semestre: document.getElementById('userSemester').value || null,
                grupo: document.getElementById('userGroup').value || null,
                nombres: document.getElementById('userNames').value.trim(),
                apa: document.getElementById('userApa').value.trim() || null,
                ama: document.getElementById('userAma').value.trim() || null,
                direccion: document.getElementById('userAddress').value.trim() || null,
                telefonos: document.getElementById('userPhone').value.trim()
            };

            if (!editingId) {
                payload.id = document.getElementById('userId').value.trim();
                payload.perfil_id = document.getElementById('userProfile').value;
                payload.password = password;
                payload.password_confirmation = passwordConfirmation;
            } else if (password) {
                payload.password = password;
                payload.password_confirmation = passwordConfirmation;
            }

            if (loadedModalUser && Number(loadedModalUser.perfil_id) === 1 && !payload.activo) {
                const adminPassword = await promptPassword({
                    title: 'Administrador protegido',
                    inputPlaceholder: 'Contraseña del administrador actual',
                    confirmButtonText: 'Autorizar cambio'
                });
                if (!adminPassword) return;
                payload.admin_password = adminPassword;
            }

            const button = document.getElementById('userModalSaveBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
            try {
                if (editingId) {
                    await api.put(`/users/${editingId}`, payload);
                    swalToast('success', 'Usuario actualizado');
                } else {
                    await api.post('/users', payload);
                    swalToast('success', 'Usuario creado');
                }
                userFormModal.hide();
                await loadUsers(currentPage);
            } catch (error) {
                document.getElementById('userModalAlert').innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message || 'Error guardando usuario')}</div>`;
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
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
                api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 }),
                api.get('/users', { perfil_id: 3, status: 'active', compact: 1, per_page: 500, _cache_ttl: 30000 })
            ]);
                controlGroups = semesterGroups;
                const students = (studentsResponse.data || []).filter(student => String(student.semestre || '') === String(semester));
                box.innerHTML = semesterGroups.map(group => {
                    const members = students.filter(student => String(student.grupo || '').toUpperCase() === String(group.grupo || '').toUpperCase());
                    const available = students.filter(student => String(student.grupo || '').toUpperCase() !== String(group.grupo || '').toUpperCase());
                    return `
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <strong>${escapeHtml(group.semestre)} ${escapeHtml(group.grupo)} - ${escapeHtml(group.nombre)}</strong>
                                    ${group.periodo ? `<div class="small opacity-75">${escapeHtml(group.periodo)}</div>` : ''}
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="badge bg-light text-primary">${members.length} alumnos</span>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-light" onclick="editControlGroup(${group.id})" title="Editar grupo"><i class="bi bi-pencil"></i></button>
                                        <button type="button" class="btn btn-outline-light" onclick="deleteControlGroup(${group.id}, ${members.length})" title="Eliminar grupo"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="input-group input-group-sm mb-3">
                                    <select class="form-select" id="addStudent-${group.id}">
                                        <option value="">Agregar alumno al grupo</option>
                                        ${available.map(student => `<option value="${escapeHtml(student.id)}">${escapeHtml(student.id)} - ${escapeHtml(student.nombres)} ${escapeHtml(student.apa || '')} ${student.grupo ? `(actual: ${escapeHtml(student.grupo)})` : '(sin grupo)'}</option>`).join('')}
                                    </select>
                                    <button class="btn btn-outline-primary" onclick="assignStudentToGroup(${group.id})"><i class="bi bi-plus"></i></button>
                                </div>
                                ${members.length ? members.map(student => `<div class="d-flex justify-content-between align-items-center border-bottom py-2 gap-2"><span>${escapeHtml(student.id)} - ${escapeHtml(student.nombres)} ${escapeHtml(student.apa || '')}</span><div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-primary" onclick="openUserModal('${escapeHtml(student.id)}')"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-warning" onclick="removeStudentFromGroup('${escapeHtml(student.id)}')" title="Quitar del grupo"><i class="bi bi-person-dash"></i></button></div></div>`).join('') : '<p class="text-muted mb-0">Sin alumnos en este grupo.</p>'}
                            </div>
                        </div>
                    </div>`;
            }).join('') || '<div class="col-12"><p class="text-muted">No hay grupos creados para este semestre. Crea uno desde Asignaturas > Gestionar Cargas.</p></div>';
        }

        async function createControlGroup() {
            openGroupForm(null);
        }

        async function editControlGroup(groupId) {
            const group = controlGroups.find(item => Number(item.id) === Number(groupId));
            if (!group) return;
            openGroupForm(group);
        }

        function openGroupForm(group = null) {
            if (!groupFormModal) groupFormModal = new bootstrap.Modal(document.getElementById('groupFormModal'));
            editingGroupId = group?.id || null;
            document.getElementById('groupForm').reset();
            document.getElementById('groupFormAlert').innerHTML = '';
            document.getElementById('groupFormTitle').innerHTML = editingGroupId
                ? '<i class="bi bi-pencil"></i> Editar grupo'
                : '<i class="bi bi-plus-circle"></i> Nuevo grupo';
            document.getElementById('groupFormSaveBtn').innerHTML = editingGroupId
                ? '<i class="bi bi-save"></i> Guardar cambios'
                : '<i class="bi bi-plus-circle"></i> Crear grupo';
            document.getElementById('groupFormMoveNotice').classList.toggle('d-none', !editingGroupId);
            document.getElementById('groupFormName').value = group?.nombre || '';
            document.getElementById('groupFormSemester').value = group?.semestre || document.getElementById('controlSemester').value || '5';
            document.getElementById('groupFormCode').value = group?.grupo || '';
            document.getElementById('groupFormPeriod').value = group?.periodo || '';
            groupFormModal.show();
        }

        async function saveControlGroup(event) {
            event.preventDefault();
            const group = editingGroupId ? controlGroups.find(item => Number(item.id) === Number(editingGroupId)) : null;
            const data = {
                nombre: document.getElementById('groupFormName').value.trim(),
                semestre: Number(document.getElementById('groupFormSemester').value),
                grupo: document.getElementById('groupFormCode').value.trim().toUpperCase(),
                periodo: document.getElementById('groupFormPeriod').value.trim(),
                asignatura_ids: (group?.asignaturas || []).map(subject => subject.id),
            };

            if (!data.nombre || !data.grupo) {
                document.getElementById('groupFormAlert').innerHTML = '<div class="alert alert-warning">Nombre y grupo son obligatorios.</div>';
                return;
            }

            const button = document.getElementById('groupFormSaveBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
            try {
                const duplicate = await findDuplicateGroup(data.semestre, data.grupo, editingGroupId);
                if (duplicate) {
                    document.getElementById('groupFormAlert').innerHTML = `<div class="alert alert-warning">Ya existe el grupo ${escapeHtml(data.semestre)} ${escapeHtml(data.grupo)}: ${escapeHtml(duplicate.nombre)}.</div>`;
                    return;
                }

                if (editingGroupId) {
                    await api.put(`/subject-groups/${editingGroupId}`, data);
                    swalToast('success', 'Grupo actualizado');
                } else {
                    await api.post('/subject-groups', data);
                    swalToast('success', 'Grupo creado');
                }

                groupFormModal.hide();
                document.getElementById('controlSemester').value = data.semestre;
                if (document.getElementById('semesterFilter').value) {
                    document.getElementById('semesterFilter').value = data.semestre;
                }
                await loadControlGroups();
                await loadGroupsForFilter();
                await loadUsers(currentPage);
            } catch (error) {
                document.getElementById('groupFormAlert').innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message || 'Error guardando el grupo')}</div>`;
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        async function findDuplicateGroup(semester, groupCode, ignoreId = null) {
            const visibleGroups = String(document.getElementById('controlSemester').value) === String(semester)
                ? controlGroups
                : await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });

            return visibleGroups.find(group => {
                const sameGroup = String(group.grupo || '').toUpperCase() === String(groupCode || '').toUpperCase();
                const sameSemester = String(group.semestre || '') === String(semester || '');
                const isSameRecord = ignoreId && Number(group.id) === Number(ignoreId);
                return sameSemester && sameGroup && !isSameRecord;
            }) || null;
        }

        async function deleteControlGroup(groupId, membersCount) {
            const group = controlGroups.find(item => Number(item.id) === Number(groupId));
            if (!group) return;

            const confirmed = await confirmAction({
                title: 'Eliminar grupo',
                text: membersCount > 0
                    ? `El grupo se eliminara y ${membersCount} alumno(s) quedaran sin semestre/grupo asignado. No se eliminara ningun alumno.`
                    : 'El grupo se eliminara. No se eliminara ningun alumno.',
                confirmButtonText: 'Si, eliminar'
            });
            if (!confirmed) return;

            try {
                await api.delete(`/subject-groups/${groupId}`);
                swalToast('success', 'Grupo eliminado');
                await loadControlGroups();
                await loadGroupsForFilter();
                await loadUsers(currentPage);
            } catch (error) {
                showError(error.message || 'Error eliminando el grupo');
            }
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

        async function protectedAdminAction(userId) {
            const password = await promptPassword({
                title: 'Autorizar desactivacion',
                inputPlaceholder: 'Contraseña del administrador actual'
            });
            if (!password) return;

            await toggleUserStatus(userId, password);
        }

        function downloadUsersExcelTemplate() {
            fetch(`${API_BASE_URL}/users-template.xls`, {
                credentials: 'include'
            })
                .then(async response => {
                    if (!response.ok) {
                        const text = await response.text().catch(() => '');
                        throw new Error(text || `No se pudo generar la plantilla (${response.status})`);
                    }
                    return response.blob();
                })
                .then(blob => {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'plantilla_usuarios.xls';
                    link.click();
                    URL.revokeObjectURL(url);
                })
                .catch(error => showError(error.message || 'No se pudo generar la plantilla Excel'));
        }

        function openUsersImportModal() {
            if (!usersImportModal) usersImportModal = new bootstrap.Modal(document.getElementById('usersImportModal'));
            document.getElementById('usersImportForm').reset();
            document.getElementById('usersImportAlert').innerHTML = '';
            usersImportModal.show();
        }

        async function importUsersExcel(event) {
            event.preventDefault();
            const file = document.getElementById('usersImportFile').files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('archivo', file);
            const button = document.getElementById('usersImportBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importando...';

            try {
                const result = await api.post('/users/import-excel', formData);
                renderImportResult('#usersImportAlert', result);
                await loadUsers(1);
            } catch (error) {
                renderImportException('#usersImportAlert', error);
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        function renderImportException(target, error) {
            const result = error.result || {};
            const errors = result.errors || error.errors || {};
            const messages = Array.isArray(errors)
                ? errors.flatMap(item => item.errores || item)
                : Object.values(errors).flat();
            const details = messages.length
                ? `<hr><div class="small">${messages.slice(0, 12).map(message => `<div>${escapeHtml(message)}</div>`).join('')}${messages.length > 12 ? '<div>...</div>' : ''}</div>`
                : '';
            document.querySelector(target).innerHTML = `
                <div class="alert alert-danger">
                    ${escapeHtml(error.message || result.message || 'No se pudo importar el archivo')}
                    ${details}
                </div>`;
        }

        function renderImportResult(target, result) {
            const errors = result.errors || [];
            const details = errors.length
                ? `<hr><div class="small">${errors.slice(0, 10).map(item => `<div><strong>Fila ${escapeHtml(item.fila)}:</strong> ${escapeHtml((item.errores || []).join(' | '))}</div>`).join('')}${errors.length > 10 ? '<div>...</div>' : ''}</div>`
                : '';
            document.querySelector(target).innerHTML = `
                <div class="alert ${errors.length ? 'alert-warning' : 'alert-success'}">
                    Registros creados: <strong>${Number(result.created || 0)}</strong>. Errores: <strong>${errors.length}</strong>.
                    ${details}
                </div>`;
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
