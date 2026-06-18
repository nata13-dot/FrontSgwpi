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
    <title>Gestión de semestres - <?= APP_NAME ?></title>
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
                    <h1 class="mb-1">Gestión de semestres</h1>
                    <p class="text-muted mb-0">Controla períodos, promociones y presentaciones en semestres distintos.</p>
                </div>
                <button class="btn btn-primary" type="button" onclick="openPeriodForm()">
                    <i class="bi bi-calendar-plus"></i> Nuevo período
                </button>
            </div>

            <div id="alertContainer"></div>
            <div class="row g-3 mb-4" id="semesterStats"></div>

            <div class="view-switcher mb-4" role="tablist">
                <button class="btn btn-primary active" data-semester-view="periods" onclick="setSemesterView('periods')"><i class="bi bi-calendar3"></i> Períodos</button>
                <button class="btn btn-outline-primary" data-semester-view="promotion" onclick="setSemesterView('promotion')"><i class="bi bi-arrow-up-right-circle"></i> Promoción</button>
                <button class="btn btn-outline-primary" data-semester-view="exceptions" onclick="setSemesterView('exceptions')"><i class="bi bi-shuffle"></i> Presentaciones especiales</button>
            </div>

            <section id="semesterViewContent">
                <div class="text-center py-5"><div class="spinner-border" role="status"></div><p class="text-muted mt-3">Cargando gestión académica...</p></div>
            </section>
        </div>
    </main>
</div>

<div class="modal fade" id="periodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="periodForm">
            <div class="modal-header">
                <h5 class="modal-title" id="periodModalTitle">Nuevo período</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="periodId">
                <div class="mb-3">
                    <label class="form-label" for="periodName">Nombre</label>
                    <input class="form-control" id="periodName" placeholder="2026-2" maxlength="40" required>
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="periodStartsAt">Fecha inicial</label>
                        <input type="date" class="form-control" id="periodStartsAt" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="periodEndsAt">Fecha final</label>
                        <input type="date" class="form-control" id="periodEndsAt" required>
                    </div>
                </div>
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" id="periodAutomaticPromotion">
                    <label class="form-check-label" for="periodAutomaticPromotion">Promover alumnos automáticamente al iniciar</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/api.js"></script>
<script src="/assets/js/app.js"></script>
<script>
const semesterState = { view: 'periods', periods: [], exceptions: [], stats: {}, activePeriodId: null, searchResults: null };
let semesterSearchTimer = null;

function esc(value) {
    return String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
}

function localDate(value) {
    if (!value) return 'Sin fecha';
    return new Date(`${String(value).slice(0, 10)}T12:00:00`).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}

async function loadSemesterManagement(force = false) {
    try {
        const response = await api.get('/semester-management', force ? { _fresh: true } : { _cache_ttl: 45000 });
        semesterState.periods = response.periods || [];
        semesterState.exceptions = response.exceptions || [];
        semesterState.stats = response.stats || {};
        semesterState.activePeriodId = Number(response.active_period_id || 0);
        renderSemesterStats();
        renderSemesterView();
    } catch (error) {
        document.getElementById('semesterViewContent').innerHTML = `<div class="alert alert-danger">${esc(error.message || 'No se pudo cargar la gestión.')}</div>`;
    }
}

function renderSemesterStats() {
    const stats = semesterState.stats;
    document.getElementById('semesterStats').innerHTML = [
        ['Alumnos activos', stats.students || 0, 'bi-people'],
        ['Proyectos', stats.projects || 0, 'bi-folder2-open'],
        ['Cargas academicas', stats.groups || 0, 'bi-journal-bookmark'],
        ['Presentaciones especiales', stats.exceptions || 0, 'bi-shuffle']
    ].map(([label, value, icon]) => `
        <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3">
            <span class="semester-stat-icon"><i class="bi ${icon}"></i></span><div><strong class="fs-4 d-block">${esc(value)}</strong><span class="text-muted small">${esc(label)}</span></div>
        </div></div></div>`).join('');
}

function setSemesterView(view) {
    if (semesterState.view === view) return;
    SGPIViewTransition.run(() => {
        semesterState.view = view;
        document.querySelectorAll('[data-semester-view]').forEach(button => {
            const active = button.dataset.semesterView === view;
            button.classList.toggle('btn-primary', active);
            button.classList.toggle('btn-outline-primary', !active);
            button.classList.toggle('active', active);
        });
        renderSemesterView();
    }, document.getElementById('semesterViewContent'));
}

function renderSemesterView() {
    if (semesterState.view === 'promotion') return renderPromotion();
    if (semesterState.view === 'exceptions') return renderExceptions();
    renderPeriods();
}

function renderPeriods() {
    document.getElementById('semesterViewContent').innerHTML = `
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center"><strong>Periodos académicos</strong><span class="text-muted small">La fecha vigente puede activar el periodo automáticamente.</span></div>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Periodo</th><th>Fechas</th><th>Promocion</th><th>Cargas</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>${semesterState.periods.map(period => `
                    <tr>
                        <td><strong>${esc(period.nombre)}</strong></td>
                        <td>${localDate(period.fecha_inicio)}<div class="small text-muted">hasta ${localDate(period.fecha_fin)}</div></td>
                        <td>${period.promocion_automatica ? '<span class="badge bg-success-subtle text-success">Automática</span>' : '<span class="badge bg-light text-dark">Manual</span>'}</td>
                        <td>${esc(period.subject_groups_count || 0)}</td>
                        <td>${period.activo ? '<span class="badge bg-primary">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>'}</td>
                        <td><div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="openPeriodForm(${period.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                            ${period.activo ? '' : `<button class="btn btn-outline-success" onclick="activatePeriod(${period.id})" title="Activar"><i class="bi bi-check2-circle"></i></button>`}
                        </div></td>
                    </tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted py-4">No hay periodos configurados.</td></tr>'}
                </tbody>
            </table></div>
        </div>`;
}

function renderPromotion() {
    const options = semesterState.periods.map(period => `<option value="${period.id}" ${period.id === semesterState.activePeriodId ? 'selected' : ''}>${esc(period.nombre)}</option>`).join('');
    document.getElementById('semesterViewContent').innerHTML = `
        <div class="row g-4">
            <div class="col-lg-5"><div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h4>Promoción académica</h4>
                <p class="text-muted">Actualiza el semestre cursado. Las excepciones de presentación no modifican este dato.</p>
                <label class="form-label" for="promotionPeriod">Periodo destino</label>
                <select class="form-select mb-3" id="promotionPeriod">${options}</select>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="previewPromotion()"><i class="bi bi-search"></i> Vista previa</button>
                    <button class="btn btn-warning" onclick="applyPromotion()"><i class="bi bi-arrow-up-right-circle"></i> Aplicar promoción</button>
                </div>
            </div></div></div>
            <div class="col-lg-7"><div class="card border-0 shadow-sm h-100"><div class="card-header"><strong>Movimientos previstos</strong></div><div class="card-body" id="promotionPreview">
                <p class="text-muted mb-0">Selecciona un periodo y genera la vista previa.</p>
            </div></div></div>
        </div>`;
}

function renderExceptions() {
    const periodOptions = semesterState.periods.map(period => `<option value="${period.id}" ${period.id === semesterState.activePeriodId ? 'selected' : ''}>${esc(period.nombre)}</option>`).join('');
    document.getElementById('semesterViewContent').innerHTML = `
        <div class="row g-4">
            <div class="col-lg-5"><div class="card border-0 shadow-sm"><div class="card-body">
                <h4>Nueva presentación especial</h4>
                <p class="text-muted small">Busca un alumno o proyecto. Su semestre académico permanece sin cambios.</p>
                <label class="form-label" for="exceptionPeriod">Periodo</label>
                <select class="form-select mb-3" id="exceptionPeriod">${periodOptions}</select>
                <label class="form-label" for="exceptionSearch">Alumno o proyecto</label>
                <input class="form-control" id="exceptionSearch" placeholder="Nombre, matricula o proyecto" oninput="scheduleExceptionSearch()">
                <div class="semester-search-results mt-2" id="exceptionSearchResults"></div>
                <input type="hidden" id="exceptionTargetType"><input type="hidden" id="exceptionTargetId">
                <div class="mt-3">
                    <label class="form-label" for="exceptionSemester">Semestre de presentación</label>
                    <select class="form-select" id="exceptionSemester">${[5,6,7,8,9].map(item => `<option value="${item}">${item}</option>`).join('')}</select>
                </div>
                <div class="mt-3"><label class="form-label" for="exceptionReason">Motivo</label><textarea class="form-control" id="exceptionReason" rows="2" maxlength="500"></textarea></div>
                <button class="btn btn-primary w-100 mt-3" onclick="saveException()"><i class="bi bi-save"></i> Guardar excepción</button>
            </div></div></div>
            <div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-header"><strong>Excepciones activas</strong></div>
                <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Periodo</th><th>Alumno o proyecto</th><th>Presenta en</th><th></th></tr></thead>
                <tbody>${semesterState.exceptions.map(exception => {
                    const student = exception.student;
                    const project = exception.project;
                    const target = student
                        ? `${[student.nombres, student.apellido_paterno, student.apellido_materno].filter(Boolean).join(' ')} (${student.id})`
                        : project?.title || project?.titulo || 'Proyecto';
                    const origin = student?.semestre || project?.subject_group?.semestre || '-';
                    return `<tr><td>${esc(exception.period?.nombre || '-')}</td><td><strong>${esc(target)}</strong><div class="small text-muted">Semestre académico ${esc(origin)}</div></td><td><span class="badge bg-primary">${esc(exception.semestre_presentacion)}</span></td><td><button class="btn btn-sm btn-outline-danger" onclick="deleteException(${exception.id})"><i class="bi bi-trash"></i></button></td></tr>`;
                }).join('') || '<tr><td colspan="4" class="text-center text-muted py-4">No hay excepciones activas.</td></tr>'}</tbody>
                </table></div>
            </div></div>
        </div>`;
}

function openPeriodForm(id = null) {
    const period = semesterState.periods.find(item => Number(item.id) === Number(id));
    document.getElementById('periodId').value = period?.id || '';
    document.getElementById('periodName').value = period?.nombre || '';
    document.getElementById('periodStartsAt').value = String(period?.fecha_inicio || '').slice(0, 10);
    document.getElementById('periodEndsAt').value = String(period?.fecha_fin || '').slice(0, 10);
    document.getElementById('periodAutomaticPromotion').checked = Boolean(period?.promocion_automatica);
    document.getElementById('periodModalTitle').textContent = period ? 'Editar periodo' : 'Nuevo periodo';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('periodModal')).show();
}

document.getElementById('periodForm').addEventListener('submit', async event => {
    event.preventDefault();
    const id = document.getElementById('periodId').value;
    const payload = {
        name: document.getElementById('periodName').value.trim(),
        starts_at: document.getElementById('periodStartsAt').value,
        ends_at: document.getElementById('periodEndsAt').value,
        automatic_promotion: document.getElementById('periodAutomaticPromotion').checked
    };
    try {
        id ? await api.put(`/semester-management/periods/${id}`, payload) : await api.post('/semester-management/periods', payload);
        bootstrap.Modal.getInstance(document.getElementById('periodModal'))?.hide();
        showAlert('#alertContainer', 'success', 'Periodo guardado correctamente.');
        loadSemesterManagement(true);
    } catch (error) { showAlert('#alertContainer', 'danger', error.message); }
});

async function activatePeriod(id) {
    if (!await confirmAction({ title: 'Activar periodo', text: 'El periodo seleccionado sera el vigente para todo el sistema.', confirmButtonText: 'Activar' })) return;
    try { await api.post(`/semester-management/periods/${id}/activate`); loadSemesterManagement(true); }
    catch (error) { showAlert('#alertContainer', 'danger', error.message); }
}

async function previewPromotion() {
    const id = document.getElementById('promotionPeriod').value;
    const box = document.getElementById('promotionPreview');
    box.innerHTML = '<div class="spinner-border" role="status"></div>';
    try {
        const response = await api.get(`/semester-management/periods/${id}/promotion-preview`, { _fresh: true });
        box.innerHTML = (response.movements || []).map(item => `<div class="semester-movement"><strong>${item.from} → ${item.to}</strong><span>${item.students} alumno(s)</span></div>`).join('') || '<p class="text-muted mb-0">No hay alumnos elegibles para promoción.</p>';
    } catch (error) { box.innerHTML = `<p class="text-danger">${esc(error.message)}</p>`; }
}

async function applyPromotion() {
    const id = document.getElementById('promotionPeriod').value;
    if (!await confirmAction({ title: 'Aplicar promoción', text: 'Se actualizará el semestre académico de los alumnos elegibles.', confirmButtonText: 'Aplicar' })) return;
    try {
        const response = await api.post(`/semester-management/periods/${id}/promote`);
        showAlert('#alertContainer', 'success', `${response.message}: ${response.summary.updated} alumno(s) actualizados.`);
        loadSemesterManagement(true);
    } catch (error) { showAlert('#alertContainer', 'danger', error.message); }
}

function scheduleExceptionSearch() {
    clearTimeout(semesterSearchTimer);
    semesterSearchTimer = setTimeout(searchExceptionTargets, 320);
}

async function searchExceptionTargets() {
    const query = document.getElementById('exceptionSearch').value.trim();
    const box = document.getElementById('exceptionSearchResults');
    if (query.length < 2) { box.innerHTML = ''; return; }
    try {
        const response = await api.get('/semester-management/search', { q: query, _cache_ttl: 15000 });
        const rows = [
            ...(response.students || []).map(item => ({ type: 'student', id: item.id, title: [item.nombres, item.apellido_paterno, item.apellido_materno].filter(Boolean).join(' '), meta: `${item.id} · Semestre ${item.semestre || '-'}` })),
            ...(response.projects || []).map(item => ({ type: 'project', id: item.id, title: item.title || item.titulo, meta: `Proyecto · Semestre ${item.subject_group?.semestre || '-'}` }))
        ];
        box.innerHTML = rows.map(item => `<button type="button" onclick="selectExceptionTarget(this)" class="semester-search-result" data-target-type="${esc(item.type)}" data-target-id="${esc(item.id)}" data-target-title="${esc(item.title)}"><strong>${esc(item.title)}</strong><small>${esc(item.meta)}</small></button>`).join('') || '<p class="text-muted small p-2">Sin resultados.</p>';
    } catch (error) { box.innerHTML = ''; }
}

function selectExceptionTarget(button) {
    const type = button.dataset.targetType;
    const id = button.dataset.targetId;
    const title = button.dataset.targetTitle;
    document.getElementById('exceptionTargetType').value = type;
    document.getElementById('exceptionTargetId').value = id;
    document.getElementById('exceptionSearch').value = title;
    document.getElementById('exceptionSearchResults').innerHTML = '';
}

async function saveException() {
    const type = document.getElementById('exceptionTargetType').value;
    const id = document.getElementById('exceptionTargetId').value;
    if (!type || !id) { showAlert('#alertContainer', 'warning', 'Selecciona un alumno o proyecto de los resultados.'); return; }
    const payload = {
        period_id: Number(document.getElementById('exceptionPeriod').value),
        project_id: type === 'project' ? Number(id) : null,
        student_id: type === 'student' ? id : null,
        presentation_semester: Number(document.getElementById('exceptionSemester').value),
        reason: document.getElementById('exceptionReason').value.trim()
    };
    try { await api.post('/semester-management/exceptions', payload); showAlert('#alertContainer', 'success', 'Excepcion guardada.'); loadSemesterManagement(true); }
    catch (error) { showAlert('#alertContainer', 'danger', error.message); }
}

async function deleteException(id) {
    if (!await confirmAction({ title: 'Eliminar excepción', text: 'La presentación volverá a usar el semestre académico.', confirmButtonText: 'Eliminar' })) return;
    try { await api.delete(`/semester-management/exceptions/${id}`); loadSemesterManagement(true); }
    catch (error) { showAlert('#alertContainer', 'danger', error.message); }
}

document.addEventListener('DOMContentLoaded', loadSemesterManagement);
</script>
</body>
</html>
