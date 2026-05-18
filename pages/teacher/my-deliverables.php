<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || !is_teacher()) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregables de mis proyectos - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>

    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>

        <main class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <h1 class="mb-1">Entregables de mis proyectos</h1>
                        <p class="text-muted mb-0">Consulta entregas realizadas y faltantes por alumno, materia y competencia.</p>
                    </div>
                    <button class="btn btn-outline-primary" onclick="loadDeliverablesMatrix()">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                </div>

                <div id="alertContainer" class="mb-3"></div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="studentSearch">Buscar alumno</label>
                        <input type="search" class="form-control" id="studentSearch" placeholder="Matricula, nombre o apellido" oninput="debouncedLoad()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="subjectFilter">Materia</label>
                        <select class="form-select" id="subjectFilter" onchange="loadDeliverablesMatrix()">
                            <option value="">Todas las materias</option>
                        </select>
                    </div>
                </div>

                <div id="matrixContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="gradeModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" onsubmit="saveGrade(event)">
                <div class="modal-header">
                    <h5 class="modal-title">Calificar entregable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="gradeAlert"></div>
                    <input type="hidden" id="gradeDeliverableId">
                    <p class="mb-2" id="gradeDeliverableName"></p>
                    <label class="form-label" for="gradeInput">Calificacion (0-100)</label>
                    <input type="number" class="form-control" id="gradeInput" min="0" max="100" step="0.01" required>
                    <div class="form-text">De 69 hacia abajo se considera reprobatorio y cuenta como 0 efectivo; no entra al promedio mostrado.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar calificacion</button>
                </div>
            </form>
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
        let gradeModal;
        let loadTimer;
        let lastMatrix = [];

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fullName(user) {
            return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || '';
        }

        function debouncedLoad() {
            clearTimeout(loadTimer);
            loadTimer = setTimeout(loadDeliverablesMatrix, 350);
        }

        async function loadDeliverablesMatrix() {
            const container = document.getElementById('matrixContainer');
            container.innerHTML = '<div class="text-center py-5"><div class="spinner-border" role="status"></div></div>';

            const params = {};
            const student = document.getElementById('studentSearch').value.trim();
            const subjectId = document.getElementById('subjectFilter').value;
            if (student) params.student = student;
            if (subjectId) params.asignatura_id = subjectId;

            try {
                const response = await api.get('/teacher/deliverables-matrix', params);
                lastMatrix = response.data || [];
                renderSubjectFilter(lastMatrix);
                renderMatrix(lastMatrix);
            } catch (error) {
                container.innerHTML = '';
                showAlert('#alertContainer', 'danger', error.message || 'Error cargando entregables');
            }
        }

        function renderSubjectFilter(projects) {
            const select = document.getElementById('subjectFilter');
            const current = select.value;
            const subjects = new Map();
            projects.forEach(project => (project.subjects || []).forEach(subject => {
                subjects.set(String(subject.id), subject);
            }));

            select.innerHTML = '<option value="">Todas las materias</option>' + [...subjects.values()]
                .sort((a, b) => String(a.nombre || '').localeCompare(String(b.nombre || ''), 'es', { sensitivity: 'base' }))
                .map(subject => `<option value="${escapeHtml(subject.id)}">${escapeHtml(subject.nombre)}${subject.clave ? ' - ' + escapeHtml(subject.clave) : ''}</option>`)
                .join('');
            select.value = [...subjects.keys()].includes(String(current)) ? current : '';
        }

        function renderMatrix(projects) {
            const container = document.getElementById('matrixContainer');
            if (!projects.length) {
                container.innerHTML = '<div class="alert alert-light border">No hay proyectos asesorados con alumnos para los filtros seleccionados.</div>';
                return;
            }

            container.innerHTML = projects.map(projectBlock => `
                <section class="mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div>
                            <h2 class="h5 mb-1">${escapeHtml(projectBlock.project.title)}</h2>
                            <span class="text-muted small">Semestre ${escapeHtml(projectBlock.project.semestre || '-')} · ${escapeHtml(projectBlock.project.year || '-')}</span>
                        </div>
                    </div>
                    <div class="accordion" id="project-${projectBlock.project.id}">
                        ${(projectBlock.students || []).map((studentBlock, index) => renderStudent(projectBlock.project.id, studentBlock, index)).join('')}
                    </div>
                </section>
            `).join('');
        }

        function renderStudent(projectId, studentBlock, index) {
            const student = studentBlock.student;
            const summary = studentBlock.summary || {};
            const collapseId = `student-${projectId}-${escapeHtml(student.id)}`;
            const average = summary.promedio === null || summary.promedio === undefined ? '-' : `${summary.promedio}%`;
            return `
                <div class="accordion-item border-0 shadow-sm mb-2">
                    <h3 class="accordion-header">
                        <button class="accordion-button ${index ? 'collapsed' : ''}" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                            <span class="d-flex flex-wrap align-items-center gap-2 w-100">
                                <strong>${escapeHtml(student.id)} - ${escapeHtml(fullName(student))}</strong>
                                <span class="badge bg-light text-dark">${escapeHtml(student.semestre || '-')} ${escapeHtml(student.grupo || '')}</span>
                                <span class="badge bg-success">${summary.entregados || 0} entregados</span>
                                <span class="badge bg-secondary">${summary.faltantes || 0} faltantes</span>
                                <span class="badge bg-primary ms-md-auto">Promedio valido: ${average}</span>
                            </span>
                        </button>
                    </h3>
                    <div id="${collapseId}" class="accordion-collapse collapse ${index ? '' : 'show'}">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Materia</th>
                                            <th>Competencia</th>
                                            <th>Estado</th>
                                            <th>Calificacion</th>
                                            <th>Archivo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${(studentBlock.items || []).map(renderItem).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>`;
        }

        function renderItem(item) {
            const deliverable = item.deliverable;
            const grade = item.calificacion;
            const effective = item.calificacion_efectiva;
            const statusBadge = item.status === 'faltante'
                ? '<span class="badge bg-secondary">Faltante</span>'
                : item.approved
                    ? '<span class="badge bg-success">Entregado aprobado</span>'
                    : grade === null || grade === undefined
                        ? '<span class="badge bg-info text-dark">Entregado sin calificar</span>'
                        : '<span class="badge bg-danger">Reprobatorio</span>';
            const gradeText = grade === null || grade === undefined
                ? '<span class="text-muted">Sin calificar</span>'
                : `<strong>${Number(grade).toFixed(2)}%</strong><div class="small text-muted">Efectiva: ${Number(effective || 0).toFixed(2)}%</div>`;
            const fileButton = deliverable?.archivo_path
                ? `<button class="btn btn-sm btn-outline-info" onclick="descargarEntregable(${deliverable.id}, '${escapeHtml(deliverable.nombre)}')" title="Descargar"><i class="bi bi-download"></i></button>`
                : '<span class="text-muted small">Sin archivo</span>';
            const gradeButton = deliverable
                ? `<button class="btn btn-sm btn-outline-primary" onclick="openGradeModal(${deliverable.id}, '${escapeHtml(deliverable.nombre)}', ${grade === null || grade === undefined ? 'null' : Number(grade)})" title="Calificar"><i class="bi bi-star"></i></button>`
                : '<span class="text-muted small">No entregado</span>';

            return `
                <tr>
                    <td>
                        <strong>${escapeHtml(item.asignatura?.nombre || '-')}</strong>
                        ${item.asignatura?.clave ? `<div class="small text-muted">${escapeHtml(item.asignatura.clave)}</div>` : ''}
                    </td>
                    <td>${escapeHtml(item.competencia?.nombre || '-')}</td>
                    <td>${statusBadge}</td>
                    <td>${gradeText}</td>
                    <td>${fileButton}</td>
                    <td>${gradeButton}</td>
                </tr>`;
        }

        function openGradeModal(deliverableId, name, currentGrade = null) {
            if (!gradeModal) gradeModal = new bootstrap.Modal(document.getElementById('gradeModal'));
            document.getElementById('gradeDeliverableId').value = deliverableId;
            document.getElementById('gradeDeliverableName').textContent = name;
            document.getElementById('gradeInput').value = currentGrade === null ? '' : currentGrade;
            document.getElementById('gradeAlert').innerHTML = '';
            gradeModal.show();
        }

        async function saveGrade(event) {
            event.preventDefault();
            const id = document.getElementById('gradeDeliverableId').value;
            const grade = document.getElementById('gradeInput').value;
            if (!validarCalificacion(grade)) {
                showAlert('#gradeAlert', 'danger', 'La calificacion debe estar entre 0 y 100.');
                return;
            }

            try {
                await api.post(`/deliverables/${id}/calificar`, { calificacion: Number(grade) });
                gradeModal.hide();
                await loadDeliverablesMatrix();
                showAlert('#alertContainer', 'success', 'Calificacion guardada.');
            } catch (error) {
                showAlert('#gradeAlert', 'danger', error.message || 'Error guardando calificacion.');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            gradeModal = new bootstrap.Modal(document.getElementById('gradeModal'));
            loadDeliverablesMatrix();
        });
    </script>
</body>
</html>
