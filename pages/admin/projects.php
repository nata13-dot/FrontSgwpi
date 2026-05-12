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
                    <a href="/pages/admin/project-create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Proyecto
                    </a>
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

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
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
                            <td><small>${escapeHtml(project.authors || '-')}</small></td>
                            <td>${project.year || '-'}</td>
                            <td>${companyInfo}</td>
                            <td>${escapeHtml(creatorName)}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-outline-secondary" onclick="showProjectDetails(${project.id})" title="Ver detalles"><i class="bi bi-eye"></i></button>
                                    ${isAdmin ? `<a href="/pages/admin/project-edit.php?id=${project.id}" class="btn btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <button class="btn btn-outline-danger" onclick="deleteProject(${project.id})" title="Eliminar"><i class="bi bi-trash"></i></button>` : ''}
                                </div>
                            </td>
                        </tr>`;
                });
            } catch (error) {
                showAlert('#alertContainer', 'danger', 'Error cargando proyectos: ' + error.message);
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
                            <p><strong>Autores:</strong><br>${escapeHtml(project.authors || '-')}</p>
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
            if (params.get('id')) showProjectDetails(params.get('id'));
        });
    </script>
</body>
</html>