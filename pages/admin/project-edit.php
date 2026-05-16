<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || !is_admin()) {
    header('Location: /index.php');
    exit;
}

$projectId = $_GET['id'] ?? null;
if (!$projectId) {
    header('Location: /pages/admin/projects.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proyecto - <?= APP_NAME ?></title>
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
                <h1 class="mb-4">Editar Proyecto</h1>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div id="alertBox"></div>
                        <form id="projectForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titulo del Proyecto</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripcion</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>                            <div class="border rounded p-3 mb-4">
                                <h5 class="mb-3">Empresa beneficiada</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="company_name" class="form-label">Nombre de la empresa o negocio</label>
                                        <input type="text" class="form-control" id="company_name" maxlength="255" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company_giro" class="form-label">Giro</label>
                                        <input type="text" class="form-control" id="company_giro" maxlength="255" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company_contact_name" class="form-label">Persona a cargo</label>
                                        <input type="text" class="form-control" id="company_contact_name" maxlength="255" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company_contact_position" class="form-label">Puesto de la persona a cargo</label>
                                        <input type="text" class="form-control" id="company_contact_position" maxlength="255" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company_address" class="form-label">Direccion de la empresa o negocio</label>
                                        <textarea class="form-control" id="company_address" rows="2" maxlength="1000" required></textarea>
                                        <div class="form-text">Separa calle, numero, colonia y municipio con comas (,).</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="semestre" class="form-label">Semestre</label>
                                    <select class="form-select" id="semestre" name="semestre" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="5">5 - Propuesta</option>
                                        <option value="6">6 - Avance</option>
                                        <option value="7">7 - Avance</option>
                                        <option value="8">8 - Titulacion</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="year" class="form-label">Año</label>
                                    <input type="number" class="form-control" id="year" name="year" min="2000" max="2100" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="subject_group_id" class="form-label">Grupo / carga de asignaturas</label>
                                    <select class="form-select" id="subject_group_id" name="subject_group_id" required>
                                        <option value="">Selecciona primero un semestre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="studentSearch" class="form-label">Autores estudiantes</label>
                                <div class="input-group">
                                    <select class="form-select" id="studentSearch">
                                        <option value="">Cargando estudiantes...</option>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" onclick="addStudentFromSearch()"><i class="bi bi-person-plus"></i> Agregar</button>
                                </div>
                                <div id="selectedStudents" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar Cambios</button>
                                <a href="/pages/admin/projects.php" class="btn btn-secondary"><i class="bi bi-x"></i> Cancelar</a>
                            </div>
                        </form>
                    </div>
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
        const form = document.getElementById('projectForm');
        const projectId = '<?= htmlspecialchars($projectId) ?>';
        let students = [];
        let assignedStudentProject = {};
        let selectedStudents = [];

        function escapeHtml(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function fullName(student) {
            return [student.nombres, student.apa, student.ama].filter(Boolean).join(' ');
        }

        async function loadStudents() {
            const [usersResponse, projectsResponse] = await Promise.all([
                api.get('/users', { perfil_id: 3, status: 'active', compact: 1, per_page: 100 }),
                api.get('/projects', { per_page: 100 })
            ]);

            students = usersResponse.data || [];
            assignedStudentProject = {};
            (projectsResponse.data || []).forEach(project => {
                if (String(project.id) === String(projectId)) return;
                (project.students || []).forEach(student => {
                    assignedStudentProject[String(student.id)] = project.title;
                });
            });

            renderStudentOptions();
        }

        function renderStudentOptions() {
            const selectedIds = selectedStudents.map(student => String(student.id));
            document.getElementById('studentSearch').innerHTML = '<option value="">Selecciona un estudiante disponible</option>' + students
                .filter(student => !assignedStudentProject[String(student.id)])
                .filter(student => !selectedIds.includes(String(student.id)))
                .map(student => `<option value="${escapeHtml(student.id)}">${escapeHtml(student.id)} - ${escapeHtml(fullName(student))}</option>`)
                .join('');
        }

        function findStudentFromInput(value) {
            return students.find(student => String(student.id) === String(value));
        }

        function renderSelectedStudents() {
            const container = document.getElementById('selectedStudents');
            if (!selectedStudents.length) {
                container.innerHTML = '<span class="text-muted small">No hay autores agregados.</span>';
                return;
            }

            container.innerHTML = selectedStudents.map(student => `
                <span class="badge text-bg-primary d-inline-flex align-items-center gap-2 p-2">
                    <i class="bi bi-mortarboard"></i> ${escapeHtml(student.id)} - ${escapeHtml(fullName(student))}
                    <button type="button" class="btn-close btn-close-white" aria-label="Quitar" onclick="removeStudent('${escapeHtml(student.id)}')"></button>
                </span>`).join('');
        }

        function addStudentFromSearch() {
            const input = document.getElementById('studentSearch');
            const student = findStudentFromInput(input.value);
            if (!student) {
                showAlert('#alertBox', 'danger', 'Selecciona un estudiante activo disponible de la lista');
                return;
            }
            if (assignedStudentProject[String(student.id)]) {
                showAlert('#alertBox', 'danger', fullName(student) + ' ya es autor del proyecto ' + assignedStudentProject[String(student.id)]);
                return;
            }
            if (!selectedStudents.some(item => String(item.id) === String(student.id))) {
                selectedStudents.push(student);
                renderSelectedStudents();
                renderStudentOptions();
            }
            input.value = '';
        }

        function removeStudent(studentId) {
            selectedStudents = selectedStudents.filter(student => String(student.id) !== String(studentId));
            renderSelectedStudents();
            renderStudentOptions();
        }

        async function loadSubjectGroups(selectedId = null) {
            const semester = document.getElementById('semestre').value;
            const select = document.getElementById('subject_group_id');
            select.innerHTML = semester ? '<option value="">Sin grupo asignado</option>' : '<option value="">Selecciona primero un semestre</option>';
            if (!semester) return;

            const groups = await api.get('/subject-groups', { semestre: semester });
            groups.forEach(group => {
                const subjects = (group.asignaturas || []).map(item => item.nombre).join(', ');
                select.innerHTML += `<option value="${group.id}">${escapeHtml(group.nombre)}${group.periodo ? ' - ' + escapeHtml(group.periodo) : ''}${subjects ? ' (' + escapeHtml(subjects) + ')' : ''}</option>`;
            });
            select.value = selectedId || '';
        }

        async function loadProject() {
            try {
                const project = await api.get(`/projects/${projectId}`);
                document.getElementById('title').value = project.title || '';
                document.getElementById('description').value = project.description || '';
                document.getElementById('semestre').value = project.semestre || '';
                document.getElementById('year').value = project.year || new Date().getFullYear();
                document.getElementById('company_name').value = project.company_name || '';
                document.getElementById('company_giro').value = project.company_giro || '';
                document.getElementById('company_contact_name').value = project.company_contact_name || '';
                document.getElementById('company_contact_position').value = project.company_contact_position || '';
                document.getElementById('company_address').value = project.company_address || '';
                selectedStudents = project.students || [];
                renderSelectedStudents();
                await loadSubjectGroups(project.subject_group_id || '');
            } catch (error) {
                showAlert('#alertBox', 'danger', 'Error al cargar los datos del proyecto');
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!selectedStudents.length) {
                showAlert('#alertBox', 'danger', 'Selecciona al menos un estudiante sin proyecto asignado.');
                return;
            }
            const formData = {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                semestre: document.getElementById('semestre').value || null,
                subject_group_id: document.getElementById('subject_group_id').value || null,
                year: document.getElementById('year').value ? parseInt(document.getElementById('year').value) : null,
                company_name: document.getElementById('company_name').value.trim() || null,
                company_giro: document.getElementById('company_giro').value.trim() || null,
                company_contact_name: document.getElementById('company_contact_name').value.trim() || null,
                company_contact_position: document.getElementById('company_contact_position').value.trim() || null,
                company_address: document.getElementById('company_address').value.trim() || null,
                student_ids: selectedStudents.map(student => student.id)
            };

            try {
                await api.put(`/projects/${projectId}`, formData);
                showAlert('#alertBox', 'success', 'Proyecto actualizado exitosamente');
                setTimeout(() => window.location.href = '/pages/admin/projects.php', 1200);
            } catch (error) {
                showAlert('#alertBox', 'danger', error.message || 'Error al actualizar proyecto');
            }
        });

        document.addEventListener('DOMContentLoaded', async () => {
            document.getElementById('semestre').addEventListener('change', () => loadSubjectGroups());
            renderSelectedStudents();
            await loadStudents();
            await loadProject();
        });
    </script>
</body>
</html>