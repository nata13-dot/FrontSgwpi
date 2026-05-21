<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !is_admin()) { header('Location: /index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Propuestas - <?= APP_NAME ?></title>
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
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1">Gestion de Propuestas</h1>
                    <p class="text-muted mb-0">Selecciona la materia y asigna los docentes responsables por grupo.</p>
                </div>
                <button class="btn btn-primary" onclick="loadConfig()"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            <div class="card border-0 shadow-sm border-start border-4 border-primary mb-4">
                <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0"><i class="bi bi-person-workspace"></i> Docentes responsables por materia</h5>
                        <small>La materia se toma de las asignaturas registradas y ligadas a cada carga/grupo.</small>
                    </div>
                    <span class="badge bg-light text-primary" id="responsibleCounter">0 grupos</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-lg-5">
                            <label class="form-label">Materia a supervisar</label>
                            <select class="form-select" id="subjectFilter" onchange="renderResponsibleTable()">
                                <option value="">Selecciona una materia</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Semestre</label>
                            <select class="form-select" id="semesterFilter" onchange="renderResponsibleTable()">
                                <option value="">Todos</option>
                                <option value="5">5to semestre</option>
                                <option value="6">6to semestre</option>
                                <option value="7">7mo semestre</option>
                                <option value="8">8vo semestre</option>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Buscar grupo</label>
                            <input class="form-control" id="groupSearch" placeholder="Ej. 5to A, B, 2026" oninput="renderResponsibleTable()">
                        </div>
                    </div>

                    <div class="alert alert-info d-flex gap-2 align-items-start" id="subjectHelp">
                        <i class="bi bi-info-circle"></i>
                        <div>Primero selecciona una materia. Despues podras asignar uno o varios docentes, cada uno con su grupo a cargo.</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Grupo / Carga</th>
                                    <th>Materia seleccionada</th>
                                    <th>Docentes responsables</th>
                                    <th>Asignar docente al grupo</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="responsibleTable">
                                <tr><td colspan="5" class="text-center text-muted py-4">Selecciona una materia.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Alumnos extra para revision</h5>
                    <span class="badge bg-secondary" id="exceptionCounter">0 excepciones</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3"><label class="form-label">Materia</label><select class="form-select" id="exceptionSubject"></select></div>
                        <div class="col-lg-3"><label class="form-label">Docente revisor</label><select class="form-select" id="exceptionTeacher"></select></div>
                        <div class="col-lg-3"><label class="form-label">Buscar alumno</label><input class="form-control" id="studentSearch" placeholder="Control, nombre o apellido" oninput="searchStudentsForException()"></div>
                        <div class="col-lg-3"><label class="form-label">Alumno</label><select class="form-select" id="exceptionStudent"></select></div>
                        <div class="col-lg-9"><input class="form-control" id="exceptionNotes" placeholder="Motivo o nota opcional"></div>
                        <div class="col-lg-3 d-grid"><button class="btn btn-primary" onclick="addException()"><i class="bi bi-plus-circle"></i> Agregar excepcion</button></div>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Materia</th><th>Docente</th><th>Alumno</th><th>Grupo alumno</th><th>Nota</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody id="exceptionsTable"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-range"></i> Ventanas de registro por grupo</h5>
                    <span class="badge bg-secondary">Administracion de fechas</span>
                </div>
                <div class="card-body">
                    <div id="groupsContainer" class="row g-4"></div>
                </div>
            </div>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/api.js"></script>
<script>
let config = { subject_groups: [], teachers: [], asignaturas: [], exceptions: [] };
const esc = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));

async function loadConfig() {
    config = await api.get('/proposal/config');
    renderSubjectOptions();
    renderResponsibleTable();
    renderWindows();
    renderExceptions();
}

function renderSubjectOptions() {
    const select = document.getElementById('subjectFilter');
    const current = select.value;
    select.innerHTML = '<option value="">Selecciona una materia</option>' + config.asignaturas.map(subject => `
        <option value="${subject.id}">${esc(subject.clave ? subject.clave + ' - ' : '')}${esc(subject.nombre)}</option>
    `).join('');
    if (current) select.value = current;
    document.getElementById('exceptionSubject').innerHTML = select.innerHTML;
    document.getElementById('exceptionTeacher').innerHTML = '<option value="">Selecciona docente</option>' + teacherOptions();
}

function teacherOptions() {
    return config.teachers.map(teacher => `
        <option value="${esc(teacher.id)}">${esc(teacher.id)} - ${esc(teacher.nombres)} ${esc(teacher.apa || '')}</option>
    `).join('');
}

function selectedSubject() {
    const id = Number(document.getElementById('subjectFilter').value || 0);
    return config.asignaturas.find(subject => Number(subject.id) === id) || null;
}

function groupHasSubject(group, subjectId) {
    return (group.asignaturas || []).some(subject => Number(subject.id) === Number(subjectId));
}

function subjectAssignments(group, subjectId) {
    return (group.teacher_assignments || []).filter(item => Number(item.asignatura_id) === Number(subjectId));
}

function filteredGroups() {
    const subject = selectedSubject();
    if (!subject) return [];

    const search = document.getElementById('groupSearch').value.toLowerCase().trim();
    const semester = document.getElementById('semesterFilter').value;

    return config.subject_groups.filter(group => {
        const text = `${group.nombre} ${group.periodo || ''}`.toLowerCase();
        return groupHasSubject(group, subject.id)
            && (!semester || String(group.semestre) === semester)
            && (!search || text.includes(search));
    });
}

function renderResponsibleTable() {
    const tbody = document.getElementById('responsibleTable');
    const subject = selectedSubject();
    const groups = filteredGroups();
    document.getElementById('responsibleCounter').textContent = `${groups.length} grupo${groups.length === 1 ? '' : 's'}`;

    if (!subject) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Selecciona una materia para ver los grupos donde esta ligada.</td></tr>';
        document.getElementById('subjectHelp').className = 'alert alert-info d-flex gap-2 align-items-start';
        return;
    }

    document.getElementById('subjectHelp').className = groups.length ? 'alert alert-success d-flex gap-2 align-items-start' : 'alert alert-warning d-flex gap-2 align-items-start';
    document.getElementById('subjectHelp').innerHTML = `<i class="bi bi-info-circle"></i><div>Materia seleccionada: <strong>${esc(subject.nombre)}</strong>. ${groups.length ? 'Asigna docentes a los grupos correspondientes.' : 'Esta materia aun no esta ligada a ninguna carga/grupo.'}</div>`;

    if (!groups.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No hay grupos con esta materia. Ligala primero desde Asignaturas &gt; Gestionar Cargas.</td></tr>';
        return;
    }

    tbody.innerHTML = groups.map(group => {
        const assignments = subjectAssignments(group, subject.id);
        const currentTeachers = assignments.length
            ? assignments.map(item => `<span class="badge bg-info text-dark me-1 mb-1"><i class="bi bi-person-check"></i> ${esc(item.teacher?.nombres || 'Docente')} ${esc(item.teacher?.apa || '')}</span>`).join('')
            : '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle"></i> Sin docente</span>';

        return `
            <tr>
                <td>
                    <strong>${esc(group.nombre)}</strong>
                    <div class="small text-muted">${esc(group.semestre)} semestre ${group.periodo ? '| ' + esc(group.periodo) : ''}</div>
                </td>
                <td><span class="badge bg-primary">${esc(subject.nombre)}</span></td>
                <td>${currentTeachers}</td>
                <td style="min-width: 260px;">
                    <select class="form-select form-select-sm" id="teacher-${group.id}">
                        <option value="">Seleccionar docente</option>
                        ${teacherOptions()}
                    </select>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-primary" onclick="assignTeacher(${group.id})" title="Asignar docente"><i class="bi bi-person-plus"></i></button>
                    ${assignments.map(item => `<button class="btn btn-sm btn-outline-danger" onclick="deleteAssignment(${item.id})" title="Quitar ${esc(item.teacher?.nombres || 'docente')}"><i class="bi bi-trash"></i></button>`).join('')}
                </td>
            </tr>
        `;
    }).join('');
}

function renderWindows() {
    const box = document.getElementById('groupsContainer');
    box.innerHTML = config.subject_groups.map(group => {
        const windows = (group.registration_windows || []).map(windowItem => `
            <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between gap-2">
                    <strong>${new Date(windowItem.starts_at).toLocaleString('es-MX')}</strong>
                    <span class="badge ${windowItem.activo ? 'bg-success' : 'bg-secondary'}">${windowItem.activo ? 'Activa' : 'Inactiva'}</span>
                </div>
                <small class="text-muted">Hasta ${new Date(windowItem.ends_at).toLocaleString('es-MX')}</small>
                <button class="btn btn-sm btn-outline-danger float-end" onclick="deleteWindow(${windowItem.id})"><i class="bi bi-trash"></i></button>
            </div>
        `).join('') || '<p class="text-muted small">Sin ventanas</p>';

        return `
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <strong>${esc(group.nombre)}</strong>
                        <span class="badge bg-light text-primary">${esc(group.semestre)} semestre</span>
                    </div>
                    <div class="card-body">
                        <h6>Ventanas de registro</h6>
                        ${windows}
                        <div class="row g-2 mt-2">
                            <div class="col-md-5"><input type="datetime-local" class="form-control form-control-sm" id="start-${group.id}"></div>
                            <div class="col-md-5"><input type="datetime-local" class="form-control form-control-sm" id="end-${group.id}"></div>
                            <div class="col-md-2 d-grid"><button class="btn btn-sm btn-primary" onclick="addWindow(${group.id})"><i class="bi bi-plus"></i></button></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

let exceptionSearchTimer = null;
function searchStudentsForException() {
    clearTimeout(exceptionSearchTimer);
    exceptionSearchTimer = setTimeout(async () => {
        const term = document.getElementById('studentSearch').value.trim();
        if (term.length < 2) return;
        const students = await api.get('/proposal/students/search', { q: term });
        document.getElementById('exceptionStudent').innerHTML = '<option value="">Selecciona alumno</option>' + students.map(student => `
            <option value="${esc(student.id)}">${esc(student.id)} - ${esc(student.nombres)} ${esc(student.apa || '')} (${esc(student.semestre || '-')}${esc(student.grupo || '')})</option>
        `).join('');
    }, 350);
}

function renderExceptions() {
    const rows = config.exceptions || [];
    document.getElementById('exceptionCounter').textContent = `${rows.length} excepcion${rows.length === 1 ? '' : 'es'}`;
    document.getElementById('exceptionsTable').innerHTML = rows.map(item => `
        <tr>
            <td>${esc(item.asignatura?.nombre || '-')}</td>
            <td>${esc(item.teacher?.nombres || '')} ${esc(item.teacher?.apa || '')}</td>
            <td>${esc(item.student?.id || '')} - ${esc(item.student?.nombres || '')} ${esc(item.student?.apa || '')}</td>
            <td>${esc(item.student?.semestre || '-')} ${esc(item.student?.grupo || '')}</td>
            <td>${esc(item.notes || '-')}</td>
            <td class="text-end"><button class="btn btn-sm btn-outline-danger" onclick="deleteException(${item.id})"><i class="bi bi-trash"></i></button></td>
        </tr>
    `).join('') || '<tr><td colspan="6" class="text-center text-muted py-3">Sin alumnos extra configurados.</td></tr>';
}

async function addException() {
    const payload = {
        asignatura_id: document.getElementById('exceptionSubject').value,
        teacher_id: document.getElementById('exceptionTeacher').value,
        student_id: document.getElementById('exceptionStudent').value,
        notes: document.getElementById('exceptionNotes').value.trim() || null
    };
    if (!payload.asignatura_id || !payload.teacher_id || !payload.student_id) {
        Swal.fire('Faltan datos', 'Selecciona materia, docente y alumno.', 'warning');
        return;
    }
    await api.post('/proposal/exceptions', payload);
    document.getElementById('exceptionNotes').value = '';
    swalToast('Excepcion agregada', 'success');
    loadConfig();
}

async function deleteException(id) {
    if (!await confirmAction({ title: 'Quitar excepcion' })) return;
    await api.delete(`/proposal/exceptions/${id}`);
    swalToast('Excepcion removida', 'success');
    loadConfig();
}

async function assignTeacher(groupId) {
    const subject = selectedSubject();
    const teacherId = document.getElementById(`teacher-${groupId}`).value;
    if (!subject) {
        Swal.fire('Selecciona una materia', '', 'warning');
        return;
    }
    if (!teacherId) {
        Swal.fire('Selecciona un docente', 'El responsable debe ser un docente activo.', 'warning');
        return;
    }

    try {
        await api.post('/proposal/assignments', {
            subject_group_id: groupId,
            asignatura_id: subject.id,
            teacher_id: teacherId,
            labor: `Revision de propuesta: ${subject.nombre}`,
            activo: true
        });
        swalToast('Docente asignado al grupo', 'success');
        loadConfig();
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

async function deleteAssignment(id) {
    if (!await confirmAction({ title: 'Quitar docente responsable' })) return;
    await api.delete(`/proposal/assignments/${id}`);
    swalToast('Responsable removido', 'success');
    loadConfig();
}

async function addWindow(groupId) {
    const starts_at = document.getElementById(`start-${groupId}`).value;
    const ends_at = document.getElementById(`end-${groupId}`).value;
    if (!starts_at || !ends_at) {
        Swal.fire('Faltan fechas', 'Indica inicio y cierre de la ventana.', 'warning');
        return;
    }

    try {
        await api.post('/proposal/windows', { subject_group_id: groupId, starts_at, ends_at, activo: true });
        swalToast('Ventana creada', 'success');
        loadConfig();
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

async function deleteWindow(id) {
    if (!await confirmAction({ title: 'Eliminar ventana' })) return;
    await api.delete(`/proposal/windows/${id}`);
    swalToast('Ventana eliminada', 'success');
    loadConfig();
}

document.addEventListener('DOMContentLoaded', loadConfig);
</script>
</body>
</html>
