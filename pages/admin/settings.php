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
    <title>Ajustes Generales - <?= APP_NAME ?></title>
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
                    <h1 class="mb-1">Ajustes Generales</h1>
                    <p class="text-muted mb-0">Configura reglas globales, apariencia y ciclos academicos.</p>
                </div>
                <button class="btn btn-outline-primary" onclick="loadSettings()"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            <div id="alertContainer"></div>

            <div class="row g-4">
                <div class="col-xl-7">
                    <form class="card border-0 shadow-sm" id="settingsForm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-sliders"></i> Configuracion del sistema</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="session_timeout_minutes">Inactividad antes de cerrar sesion</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="session_timeout_minutes" min="1" max="480" required>
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="default_theme">Tema por defecto</label>
                                    <select class="form-select" id="default_theme" required>
                                        <option value="system">Preferencia del navegador</option>
                                        <option value="light">Claro</option>
                                        <option value="dark">Oscuro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="active_academic_period">Periodo academico activo</label>
                                    <input type="text" class="form-control" id="active_academic_period" maxlength="40" placeholder="Ej. 2026-1" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="font_scale">Tamaño de fuente general</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="font_scale" min="85" max="125" step="5" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="max_file_size_mb">Tamaño maximo de archivo</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="max_file_size_mb" min="1" max="200" required>
                                        <span class="input-group-text">MB</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="max_project_members">Maximo de integrantes por proyecto</label>
                                    <input type="number" class="form-control" id="max_project_members" min="1" max="10" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Tipos de archivo permitidos</label>
                                    <div class="d-flex flex-wrap gap-3" id="allowedFileTypes">
                                        <?php foreach (['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'txt', 'jpg', 'jpeg', 'png'] as $type): ?>
                                            <div class="form-check">
                                                <input class="form-check-input allowed-file-type" type="checkbox" value="<?= $type ?>" id="fileType<?= $type ?>">
                                                <label class="form-check-label text-uppercase" for="fileType<?= $type ?>"><?= $type ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="global_notice">Aviso global del sistema</label>
                                    <textarea class="form-control" id="global_notice" rows="3" maxlength="1000" placeholder="Mensaje visible para usuarios en la parte superior del sistema"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="proposal_registration_enabled">
                                        <label class="form-check-label" for="proposal_registration_enabled">Activar registro de propuestas</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="grayscale_mode" onchange="previewGrayscaleMode()">
                                        <label class="form-check-label" for="grayscale_mode">Mostrar el sistema en escala de grises</label>
                                    </div>
                                    <div class="form-text">Aplica un filtro institucional sin color a todas las pantallas del sistema.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar ajustes</button>
                        </div>
                    </form>
                </div>

                <div class="col-xl-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-arrow-up-right-circle"></i> Cambio de semestre</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                Este proceso actualiza el semestre de alumnos activos. Agrega excepciones para alumnos que deban quedarse o moverse a otro semestre.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="fromSemester">Semestre origen</label>
                                    <select class="form-select" id="fromSemester" onchange="loadSemesterGroups('from')">
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="fromGroup">Grupo origen</label>
                                    <select class="form-select" id="fromGroup">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="toSemester">Semestre destino</label>
                                    <select class="form-select" id="toSemester" onchange="loadSemesterGroups('to')">
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="toGroup">Grupo destino</label>
                                    <select class="form-select" id="toGroup">
                                        <option value="">Conservar grupo actual</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="updateSubjectGroups">
                                        <label class="form-check-label" for="updateSubjectGroups">Actualizar tambien las cargas/grupos del semestre origen</label>
                                    </div>
                                </div>
                                <div class="col-12 d-grid">
                                    <button class="btn btn-outline-primary" onclick="loadSemesterPreview()"><i class="bi bi-search"></i> Cargar alumnos</button>
                                </div>
                            </div>

                            <div class="mt-4" id="semesterPreviewBox">
                                <p class="text-muted mb-0">Carga un semestre para preparar excepciones.</p>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-warning" onclick="applySemesterChange()"><i class="bi bi-check2-circle"></i> Aplicar cambio</button>
                        </div>
                    </div>
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
<script src="/assets/js/app.js"></script>
<script>
let semesterStudents = [];

function esc(value) {
    return String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
}

function previewGrayscaleMode() {
    document.documentElement.classList.toggle('grayscale-mode', document.getElementById('grayscale_mode').checked);
}

async function loadSettings() {
    try {
        const settings = await api.get('/settings');
        ['session_timeout_minutes', 'default_theme', 'active_academic_period', 'font_scale', 'max_file_size_mb', 'max_project_members', 'global_notice'].forEach(id => {
            document.getElementById(id).value = settings[id] ?? '';
        });
        document.getElementById('proposal_registration_enabled').checked = Boolean(settings.proposal_registration_enabled);
        document.getElementById('grayscale_mode').checked = Boolean(settings.grayscale_mode);
        previewGrayscaleMode();
        document.querySelectorAll('.allowed-file-type').forEach(input => {
            input.checked = (settings.allowed_file_types || []).includes(input.value);
        });
    } catch (error) {
        showAlert('#alertContainer', 'danger', 'Error cargando ajustes: ' + error.message);
    }
}

document.getElementById('settingsForm').addEventListener('submit', async event => {
    event.preventDefault();
    const allowed = [...document.querySelectorAll('.allowed-file-type:checked')].map(input => input.value);
    const payload = {
        session_timeout_minutes: Number(document.getElementById('session_timeout_minutes').value),
        default_theme: document.getElementById('default_theme').value,
        active_academic_period: document.getElementById('active_academic_period').value.trim(),
        font_scale: Number(document.getElementById('font_scale').value),
        max_file_size_mb: Number(document.getElementById('max_file_size_mb').value),
        allowed_file_types: allowed,
        max_project_members: Number(document.getElementById('max_project_members').value),
        global_notice: document.getElementById('global_notice').value.trim(),
        proposal_registration_enabled: document.getElementById('proposal_registration_enabled').checked,
        grayscale_mode: document.getElementById('grayscale_mode').checked
    };

    if (!allowed.length) {
        showAlert('#alertContainer', 'danger', 'Selecciona al menos un tipo de archivo permitido.');
        return;
    }

    try {
        const response = await api.put('/settings', payload);
        showAlert('#alertContainer', 'success', response.message || 'Ajustes guardados');
        window.SGPI_SETTINGS = response.settings;
        if (typeof applySystemSettings === 'function') applySystemSettings(response.settings);
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error guardando ajustes');
    }
});

async function loadSemesterPreview() {
    const fromSemester = document.getElementById('fromSemester').value;
    const fromGroup = document.getElementById('fromGroup').value;
    const box = document.getElementById('semesterPreviewBox');
    box.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"></div></div>';

    try {
        const params = { from_semester: fromSemester };
        if (fromGroup) params.from_group = fromGroup;
        const response = await api.get('/settings/semester-preview', params);
        semesterStudents = response.students || [];
        if (!semesterStudents.length) {
            box.innerHTML = '<p class="text-muted mb-0">No hay alumnos activos en ese semestre.</p>';
            return;
        }

        box.innerHTML = `
            <div class="table-responsive" style="max-height: 360px;">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Alumno</th><th>Grupo</th><th>Excepcion</th></tr></thead>
                    <tbody>
                        ${semesterStudents.map(student => `
                            <tr>
                                <td>
                                    <strong>${esc(student.id)}</strong>
                                    <div class="small text-muted">${esc([student.nombres, student.apa, student.ama].filter(Boolean).join(' '))}</div>
                                </td>
                                <td>${esc(student.grupo || '-')}</td>
                                <td>
                                    <select class="form-select form-select-sm semester-exception" data-user-id="${esc(student.id)}">
                                        <option value="">Sin excepcion</option>
                                        <option value="5">Enviar a 5</option>
                                        <option value="6">Enviar a 6</option>
                                        <option value="7">Enviar a 7</option>
                                        <option value="8">Enviar a 8</option>
                                        <option value="9">Enviar a 9</option>
                                    </select>
                                </td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
    } catch (error) {
        box.innerHTML = '<p class="text-danger mb-0">Error cargando alumnos.</p>';
    }
}

async function applySemesterChange() {
    if (!semesterStudents.length) {
        showAlert('#alertContainer', 'danger', 'Primero carga los alumnos del semestre origen.');
        return;
    }

    const confirmed = await confirmAction({
        title: 'Aplicar cambio de semestre',
        text: 'Esta accion modificara el semestre de los alumnos seleccionados. Revisa las excepciones antes de continuar.',
        confirmButtonText: 'Si, aplicar'
    });
    if (!confirmed) return;

    const exceptions = [...document.querySelectorAll('.semester-exception')]
        .filter(select => select.value)
        .map(select => ({ user_id: select.dataset.userId, semester: Number(select.value) }));

    try {
        const response = await api.post('/settings/apply-semester-change', {
            from_semester: Number(document.getElementById('fromSemester').value),
            from_group: document.getElementById('fromGroup').value || null,
            to_semester: Number(document.getElementById('toSemester').value),
            to_group: document.getElementById('toGroup').value || null,
            update_subject_groups: document.getElementById('updateSubjectGroups').checked,
            exceptions
        });

        const s = response.summary;
        showAlert('#alertContainer', 'success', `${response.message}. Alumnos revisados: ${s.students_reviewed}, actualizados: ${s.students_updated}, excepciones: ${s.exceptions_applied}, cargas/grupos: ${s.subject_groups_updated}.`, 9000);
        semesterStudents = [];
        document.getElementById('semesterPreviewBox').innerHTML = '<p class="text-muted mb-0">Cambio aplicado. Vuelve a cargar para preparar otro movimiento.</p>';
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error aplicando cambio de semestre');
    }
}

document.addEventListener('DOMContentLoaded', loadSettings);
async function loadSemesterGroups(kind) {
    const semester = document.getElementById(kind === 'from' ? 'fromSemester' : 'toSemester').value;
    const select = document.getElementById(kind === 'from' ? 'fromGroup' : 'toGroup');
    select.innerHTML = kind === 'from' ? '<option value="">Todos</option>' : '<option value="">Conservar grupo actual</option>';
    const groups = await api.get('/subject-groups', { semestre: semester, _cache_ttl: 60000 });
    groups.forEach(group => {
        select.innerHTML += `<option value="${esc(group.grupo)}">${esc(group.semestre)} ${esc(group.grupo)} - ${esc(group.nombre)}</option>`;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadSemesterGroups('from');
    loadSemesterGroups('to');
});
</script>
</body>
</html>
