<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/validations.php';

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
    <title>Gestion de Proyectos - <?= APP_NAME ?></title>
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
                    <div><h1 class="mb-1">Gestion de Proyectos</h1><span class="badge bg-primary"><i class="bi bi-table"></i> Vista resumida de proyectos</span></div>
                    <?php if (is_admin()): ?>
                    <button type="button" class="btn btn-primary" onclick="openProjectModal()">
                        <i class="bi bi-plus-circle"></i> Nuevo Proyecto
                    </button>
                    <?php endif; ?>
                </div>

                <div id="alertContainer" class="mb-3"></div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="semesterFilter" class="form-label">Filtrar por semestre</label>
                        <select id="semesterFilter" class="form-select" onchange="loadProjects(1)">
                            <option value="">Todos los semestres</option>
                            <option value="5">5 - Propuesta</option>
                            <option value="6">6 - Avance</option>
                            <option value="7">7 - Avance</option>
                            <option value="8">8 - Titulacion</option>
                        </select>
                    </div>
                </div>
                <div class="card border-0 shadow-sm border-start border-4 border-primary">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nombre del proyecto</th>
                                        <th>Semestre</th>
                                        <th>Autores</th>
                                        <th>Periodo / Año</th>
                                        <th>Empresa</th>
                                        <th>Registrado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="projectsTable">
                                    <tr><td colspan="7" class="text-center py-4"><div class="spinner-border" role="status"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (is_admin()): ?>
    <div class="modal fade" id="projectFormModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="projectModalForm" class="needs-validation" novalidate onsubmit="saveProjectModal(event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="projectModalTitle"><i class="bi bi-folder-plus"></i> Nuevo proyecto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="projectModalAlert"></div>
                        <input type="hidden" id="projectModalEditingId">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="projectTitle">Titulo del proyecto</label>
                                <input type="text" class="form-control" id="projectTitle" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="projectDescription">Descripcion</label>
                                <textarea class="form-control" id="projectDescription" rows="4" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="projectCompanyName">Empresa o negocio</label>
                                <input type="text" class="form-control" id="projectCompanyName" maxlength="255" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="projectCompanyGiro">Giro</label>
                                <input type="text" class="form-control" id="projectCompanyGiro" maxlength="255" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="projectCompanyContact">Persona a cargo</label>
                                <input type="text" class="form-control" id="projectCompanyContact" maxlength="255" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="projectCompanyPosition">Puesto</label>
                                <input type="text" class="form-control" id="projectCompanyPosition" maxlength="255" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="projectCompanyAddress">Direccion de empresa</label>
                                <textarea class="form-control" id="projectCompanyAddress" rows="2" maxlength="1000" required></textarea>
                                <div class="form-text">Separa calle, numero, colonia y municipio con comas (,).</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="projectSemester">Semestre</label>
                                <select class="form-select" id="projectSemester" required onchange="loadProjectModalGroups()">
                                    <option value="">Seleccionar...</option>
                                    <option value="5">5 - Propuesta</option>
                                    <option value="6">6 - Avance</option>
                                    <option value="7">7 - Avance</option>
                                    <option value="8">8 - Titulacion</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="projectYear">Año</label>
                                <input type="number" class="form-control" id="projectYear" min="2000" max="2100" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="projectSubjectGroup">Grupo / carga de asignaturas</label>
                                <select class="form-select" id="projectSubjectGroup" required>
                                    <option value="">Selecciona primero un semestre</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="projectStudentSelect">Autores estudiantes sin proyecto</label>
                                <select class="form-select" id="projectStudentSelect">
                                    <option value="">Cargando estudiantes...</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-primary w-100" onclick="addProjectStudent()"><i class="bi bi-person-plus"></i> Agregar</button>
                            </div>
                            <div class="col-12">
                                <div id="projectSelectedStudents" class="d-flex flex-wrap gap-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="projectModalSaveBtn"><i class="bi bi-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="projectSubjectsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" onsubmit="saveProjectSubjects(event)">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-book"></i> Materias del proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="projectSubjectsId">
                    <div class="alert alert-info">
                        Las materias del grupo se copian automaticamente al proyecto. Usa esta opcion para ajustar casos especiales sin ligar entregables uno por uno.
                    </div>
                    <div id="projectSubjectsList" class="row g-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar materias</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>const API_BASE_URL = 'http://127.0.0.1:8000/api';</script>
    <script src="/assets/js/auth.js"></script>
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    <script>
        let isAdmin = false;
        let isTeacher = false;
        let isStudent = false;
        let currentUserId = null;
        let projectFormModal;
        let projectSubjectsModal;
        let projectSubjectsCatalog = [];
        let projectStudents = [];
        let projectSelectedStudents = [];
        let assignedStudentProject = {};

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function projectActiveAuthors(project, emptyText = '-') {
            const students = Array.isArray(project?.students) ? project.students : [];
            const names = students.map(student => fullName(student)).filter(Boolean);
            return names.length ? names.join(', ') : emptyText;
        }

        async function loadProjects(page = 1) {
            try {
                const user = auth.getCurrentUser();
                isAdmin = user && user.perfil_id === 1;
                isTeacher = user && user.perfil_id === 2;
                isStudent = user && user.perfil_id === 3;
                currentUserId = user && user.id;

                const params = { page };
                const semester = document.getElementById('semesterFilter').value;
                if (semester) params.semestre = semester;

                const response = await api.get('/projects', params);
                const tbody = document.getElementById('projectsTable');
                tbody.innerHTML = '';

                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay proyectos</td></tr>';
                    return;
                }

                let proyectosFiltrados = response.data;
                if (isTeacher) {
                    proyectosFiltrados = response.data.filter(p => p.advisors && p.advisors.some(a => a.id === currentUserId));
                } else if (isStudent) {
                    proyectosFiltrados = response.data.filter(p => p.students && p.students.some(s => s.id === currentUserId));
                }

                if (proyectosFiltrados.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay proyectos para este filtro</td></tr>';
                    return;
                }

                proyectosFiltrados.forEach(project => {
                    const creatorName = project.creator?.nombres ? String(project.creator.nombres).split(' ')[0] : 'N/A';
                    const companyInfo = project.company_name
                        ? `<strong>${escapeHtml(project.company_name)}</strong>${project.company_contact_name ? `<div class="small text-muted">${escapeHtml(project.company_contact_name)}</div>` : ''}`
                        : '<span class="text-muted small">Sin empresa</span>';

                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${escapeHtml(project.title)}</strong></td>
                            <td>${project.semestre || '-'}</td>
                            <td><small>${escapeHtml(projectActiveAuthors(project))}</small></td>
                            <td>${project.year || '-'}</td>
                            <td>${companyInfo}</td>
                            <td>${escapeHtml(creatorName)}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-secondary" onclick="showProjectDetails(${project.id})" title="Ver detalles"><i class="bi bi-eye"></i></button>
                                    ${isAdmin ? `<button type="button" class="btn btn-outline-info" onclick="openProjectSubjectsModal(${project.id})" title="Materias"><i class="bi bi-book"></i></button>
                                    <button type="button" class="btn btn-outline-primary" onclick="openProjectModal(${project.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-danger" onclick="deleteProject(${project.id})" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                                </div>
                            </td>
                        </tr>`;
                });
            } catch (error) {
                showAlert('#alertContainer', 'danger', 'Error cargando proyectos: ' + error.message);
            }
        }

        function fullName(student) {
            return [student.nombres, student.apa, student.ama].filter(Boolean).join(' ');
        }

        function resetProjectModal() {
            document.getElementById('projectModalForm').reset();
            document.getElementById('projectModalForm').classList.remove('was-validated');
            document.getElementById('projectModalAlert').innerHTML = '';
            document.getElementById('projectModalEditingId').value = '';
            document.getElementById('projectYear').value = new Date().getFullYear();
            projectSelectedStudents = [];
            renderProjectSelectedStudents();
        }

        async function openProjectModal(projectId = null) {
            if (!projectFormModal) projectFormModal = new bootstrap.Modal(document.getElementById('projectFormModal'));
            resetProjectModal();
            const isEdit = Boolean(projectId);
            document.getElementById('projectModalTitle').innerHTML = isEdit
                ? '<i class="bi bi-pencil"></i> Editar proyecto'
                : '<i class="bi bi-folder-plus"></i> Nuevo proyecto';
            document.getElementById('projectModalSaveBtn').innerHTML = isEdit
                ? '<i class="bi bi-save"></i> Guardar cambios'
                : '<i class="bi bi-save"></i> Guardar';

            await loadProjectModalStudents(projectId);
            if (isEdit) {
                const project = await api.get(`/projects/${projectId}`);
                document.getElementById('projectModalEditingId').value = project.id;
                document.getElementById('projectTitle').value = project.title || '';
                document.getElementById('projectDescription').value = project.description || '';
                document.getElementById('projectCompanyName').value = project.company_name || '';
                document.getElementById('projectCompanyGiro').value = project.company_giro || '';
                document.getElementById('projectCompanyContact').value = project.company_contact_name || '';
                document.getElementById('projectCompanyPosition').value = project.company_contact_position || '';
                document.getElementById('projectCompanyAddress').value = project.company_address || '';
                document.getElementById('projectSemester').value = project.semestre || '';
                document.getElementById('projectYear').value = project.year || new Date().getFullYear();
                projectSelectedStudents = project.students || [];
                await loadProjectModalGroups(project.subject_group_id || '');
                renderProjectSelectedStudents();
                renderProjectStudentOptions();
            } else {
                renderProjectStudentOptions();
            }
            projectFormModal.show();
        }

        async function loadProjectModalStudents(currentProjectId = null) {
            const [usersResponse, projectsResponse] = await Promise.all([
                api.get('/users', { perfil_id: 3, status: 'active', compact: 1, per_page: 100 }),
                api.get('/projects', { per_page: 100 })
            ]);
            projectStudents = usersResponse.data || [];
            assignedStudentProject = {};
            (projectsResponse.data || []).forEach(project => {
                if (String(project.id) === String(currentProjectId || '')) return;
                (project.students || []).forEach(student => {
                    assignedStudentProject[String(student.id)] = project.title;
                });
            });
        }

        function renderProjectStudentOptions() {
            const selectedIds = projectSelectedStudents.map(student => String(student.id));
            document.getElementById('projectStudentSelect').innerHTML = '<option value="">Selecciona un estudiante disponible</option>' + projectStudents
                .filter(student => !assignedStudentProject[String(student.id)])
                .filter(student => !selectedIds.includes(String(student.id)))
                .map(student => `<option value="${escapeHtml(student.id)}">${escapeHtml(student.id)} - ${escapeHtml(fullName(student))}</option>`)
                .join('');
        }

        function renderProjectSelectedStudents() {
            const container = document.getElementById('projectSelectedStudents');
            if (!container) return;
            container.innerHTML = projectSelectedStudents.length
                ? projectSelectedStudents.map(student => `
                    <span class="badge text-bg-primary d-inline-flex align-items-center gap-2 p-2">
                        <i class="bi bi-mortarboard"></i> ${escapeHtml(student.id)} - ${escapeHtml(fullName(student))}
                        <button type="button" class="btn-close btn-close-white" aria-label="Quitar" onclick="removeProjectStudent('${escapeHtml(student.id)}')"></button>
                    </span>`).join('')
                : '<span class="text-muted small">No hay autores agregados.</span>';
        }

        function addProjectStudent() {
            const select = document.getElementById('projectStudentSelect');
            const student = projectStudents.find(item => String(item.id) === String(select.value));
            if (!student) {
                showAlert('#alertContainer', 'danger', 'Selecciona un estudiante activo disponible de la lista');
                return;
            }
            projectSelectedStudents.push(student);
            select.value = '';
            renderProjectSelectedStudents();
            renderProjectStudentOptions();
        }

        function removeProjectStudent(studentId) {
            projectSelectedStudents = projectSelectedStudents.filter(student => String(student.id) !== String(studentId));
            renderProjectSelectedStudents();
            renderProjectStudentOptions();
        }

        async function loadProjectModalGroups(selectedId = '') {
            const semester = document.getElementById('projectSemester').value;
            const select = document.getElementById('projectSubjectGroup');
            select.innerHTML = semester ? '<option value="">Seleccionar grupo...</option>' : '<option value="">Selecciona primero un semestre</option>';
            if (!semester) return;
            const groups = await api.get('/subject-groups', { semestre: semester });
            groups.forEach(group => {
                const subjects = (group.asignaturas || []).map(item => item.nombre).join(', ');
                select.innerHTML += `<option value="${group.id}">${escapeHtml(group.nombre)}${group.periodo ? ' - ' + escapeHtml(group.periodo) : ''}${subjects ? ' (' + escapeHtml(subjects) + ')' : ''}</option>`;
            });
            select.value = selectedId || '';
        }

        async function saveProjectModal(event) {
            event.preventDefault();
            const form = document.getElementById('projectModalForm');
            form.classList.add('was-validated');
            if (!form.checkValidity()) return;
            if (!projectSelectedStudents.length) {
                showAlert('#alertContainer', 'danger', 'Selecciona al menos un estudiante sin proyecto asignado.');
                return;
            }

            const editingId = document.getElementById('projectModalEditingId').value;
            const payload = {
                title: document.getElementById('projectTitle').value.trim(),
                description: document.getElementById('projectDescription').value.trim(),
                semestre: document.getElementById('projectSemester').value || null,
                subject_group_id: document.getElementById('projectSubjectGroup').value || null,
                year: Number(document.getElementById('projectYear').value),
                company_name: document.getElementById('projectCompanyName').value.trim(),
                company_giro: document.getElementById('projectCompanyGiro').value.trim(),
                company_contact_name: document.getElementById('projectCompanyContact').value.trim(),
                company_contact_position: document.getElementById('projectCompanyPosition').value.trim(),
                company_address: document.getElementById('projectCompanyAddress').value.trim(),
                student_ids: projectSelectedStudents.map(student => student.id)
            };

            const button = document.getElementById('projectModalSaveBtn');
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';
            try {
                if (editingId) {
                    await api.put(`/projects/${editingId}`, payload);
                    swalToast('success', 'Proyecto actualizado');
                } else {
                    await api.post('/projects', payload);
                    swalToast('success', 'Proyecto creado');
                }
                projectFormModal.hide();
                await loadProjects(1);
            } catch (error) {
                document.getElementById('projectModalAlert').innerHTML = `<div class="alert alert-danger">${escapeHtml(error.message || 'Error guardando proyecto')}</div>`;
            } finally {
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        async function showProjectDetails(projectId) {
            try {
                const project = await api.get(`/projects/${projectId}`);
                const advisors = (project.advisors || []).map(advisor => `${escapeHtml(advisor.nombres || '')} ${escapeHtml(advisor.apa || '')} <span class="badge bg-secondary">${escapeHtml(advisor.pivot?.rol_asesor || '')}</span>`).join('<br>') || '<span class="text-muted">Sin asesores</span>';
                const subjects = (project.asignaturas || []).map(subject => `<span class="badge bg-primary me-1 mb-1">${escapeHtml(subject.nombre || subject.name || '')}</span>`).join('') || '<span class="text-muted">Sin asignaturas</span>';
                const status = project.proposal_status ? `<span class="badge bg-info text-dark">${escapeHtml(project.proposal_status)}</span>` : '<span class="text-muted">Sin estado</span>';

                Swal.fire({
                    title: escapeHtml(project.title),
                    html: `
                        <div class="text-start">
                            <p class="text-muted">${escapeHtml(project.description || 'Sin descripcion')}</p>
                            <p><strong>Estado de propuesta:</strong> ${status}</p>
                            <p><strong>Autores:</strong><br>${escapeHtml(projectActiveAuthors(project))}</p>
                            <p><strong>Grupo / carga:</strong><br>${escapeHtml(project.subject_group?.nombre || '-')}</p>
                            <p><strong>Asignaturas:</strong><br>${subjects}</p>
                            <p><strong>Empresa:</strong><br>${escapeHtml(project.company_name || '-')}</p>
                            <p><strong>Responsable empresa:</strong><br>${escapeHtml(project.company_contact_name || '-')} ${escapeHtml(project.company_contact_position || '')}</p>
                            <p><strong>Asesores:</strong><br>${advisors}</p>
                            ${project.proposal_review_comment ? `<div class="alert alert-light border"><strong>Observaciones:</strong><br>${escapeHtml(project.proposal_review_comment)}</div>` : ''}
                        </div>
                    `,
                    width: 760,
                    confirmButtonText: 'Cerrar'
                });
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }
        async function openProjectSubjectsModal(projectId) {
            if (!projectSubjectsModal) projectSubjectsModal = new bootstrap.Modal(document.getElementById('projectSubjectsModal'));
            document.getElementById('projectSubjectsId').value = projectId;
            document.getElementById('projectSubjectsList').innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border" role="status"></div></div>';
            projectSubjectsModal.show();

            try {
                const [project, subjectsResponse] = await Promise.all([
                    api.get(`/projects/${projectId}`),
                    api.get('/asignaturas', { per_page: 100 })
                ]);
                projectSubjectsCatalog = subjectsResponse.data || [];
                const selected = (project.asignaturas || []).map(item => Number(item.id));
                document.getElementById('projectSubjectsList').innerHTML = projectSubjectsCatalog.map(subject => `
                    <div class="col-md-6">
                        <div class="form-check border rounded p-2 ps-5 h-100">
                            <input class="form-check-input project-subject-check" type="checkbox" value="${subject.id}" id="projectSubject${subject.id}" ${selected.includes(Number(subject.id)) ? 'checked' : ''}>
                            <label class="form-check-label" for="projectSubject${subject.id}">
                                <strong>${escapeHtml(subject.nombre)}</strong>
                                <span class="text-muted small d-block">${escapeHtml(subject.clave || '')}</span>
                            </label>
                        </div>
                    </div>`).join('') || '<div class="col-12 text-muted">No hay materias registradas.</div>';
            } catch (error) {
                document.getElementById('projectSubjectsList').innerHTML = '<div class="col-12 text-danger">Error cargando materias del proyecto.</div>';
            }
        }

        async function saveProjectSubjects(event) {
            event.preventDefault();
            const projectId = document.getElementById('projectSubjectsId').value;
            const asignaturaIds = [...document.querySelectorAll('.project-subject-check:checked')].map(input => Number(input.value));

            try {
                await api.post(`/projects/${projectId}/asignaturas`, { asignatura_ids: asignaturaIds });
                projectSubjectsModal.hide();
                swalToast('success', 'Materias del proyecto actualizadas');
                loadProjects();
            } catch (error) {
                swalToast('danger', error.message || 'Error guardando materias');
            }
        }

        async function deleteProject(projectId) {
            const confirmed = await confirmAction({
                title: 'Eliminar proyecto',
                text: '¿Estas seguro de que deseas eliminar este proyecto?',
                confirmButtonText: 'Si, eliminar'
            });
            if (!confirmed) return;

            api.delete(`/projects/${projectId}`).then(() => {
                showAlert('#alertContainer', 'success', 'Proyecto eliminado');
                loadProjects();
            }).catch(error => showAlert('#alertContainer', 'danger', 'Error: ' + error.message));
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await loadProjects();
            const params = new URLSearchParams(window.location.search);
            if (params.get('edit') && auth.getCurrentUser()?.perfil_id === 1) openProjectModal(params.get('edit'));
            if (params.get('id')) showProjectDetails(params.get('id'));
        });
    </script>
</body>
</html>
