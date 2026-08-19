<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !is_admin()) {
    header('Location: ' . dashboard_url());
    exit;
}
$career = active_career();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carga inicial - <?= APP_NAME ?></title>
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
        <div class="container-xl py-4 py-lg-5">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                <div>
                    <span class="text-uppercase small fw-bold text-muted">Puesta en marcha</span>
                    <h1 class="h2 mb-2">Carga académica de <?= htmlspecialchars($career['nombre_corto'] ?? 'la carrera') ?></h1>
                    <p class="text-muted mb-0">Importa el plan real de asignaturas y sus grupos. La información se guardará únicamente en la carrera activa.</p>
                </div>
                <div class="d-flex flex-wrap gap-2 align-self-start">
                    <button data-career-module="reportes" class="btn btn-outline-success" type="button" onclick="downloadCareerPackage()"><i class="bi bi-file-earmark-zip"></i> Exportar carrera</button>
                    <a class="btn btn-outline-primary" href="/pages/admin/asignaturas.php"><i class="bi bi-book"></i> Ver catálogo</a>
                </div>
            </div>
            <div id="setupAlert"></div>
            <div class="alert alert-info"><i class="bi bi-shield-check"></i> La importación es transaccional: si alguna fila falla, no se guardará ninguna parte del archivo.</div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <section class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h2 class="h5"><i class="bi bi-journal-plus"></i> 1. Asignaturas</h2>
                            <p class="text-muted small">Columnas: <code>clave,nombre,descripcion</code>.</p>
                            <button class="btn btn-sm btn-outline-secondary mb-3" type="button" onclick="downloadTemplate('subjects')"><i class="bi bi-download"></i> Descargar plantilla</button>
                            <input class="form-control" type="file" id="subjectsFile" accept=".csv,text/csv">
                            <div class="small text-muted mt-3" id="subjectsPreview">No se ha seleccionado archivo.</div>
                        </div>
                    </section>
                </div>
                <div class="col-lg-6">
                    <section class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h2 class="h5"><i class="bi bi-collection"></i> 2. Grupos y cargas</h2>
                            <p class="text-muted small">Columnas: <code>nombre,semestre,grupo,periodo,asignaturas</code>. Separa claves con <code>|</code>.</p>
                            <button class="btn btn-sm btn-outline-secondary mb-3" type="button" onclick="downloadTemplate('groups')"><i class="bi bi-download"></i> Descargar plantilla</button>
                            <input class="form-control" type="file" id="groupsFile" accept=".csv,text/csv">
                            <div class="small text-muted mt-3" id="groupsPreview">No se ha seleccionado archivo.</div>
                        </div>
                    </section>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div><strong id="importSummary">Selecciona al menos el archivo de asignaturas.</strong><small class="d-block text-muted">Las claves existentes en esta carrera serán actualizadas; no se eliminarán registros.</small></div>
                    <button class="btn btn-primary" id="importButton" type="button" disabled><i class="bi bi-cloud-arrow-up"></i> Importar catálogo</button>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h2 class="h5 mb-1"><i class="bi bi-ui-checks-grid"></i> 3. Rúbricas de evaluación</h2>
                        <p class="text-muted mb-0">Crea los contenedores vacíos de 5.º a 8.º semestre. Las preguntas se configuran después desde Evaluaciones.</p>
                    </div>
                    <button class="btn btn-outline-primary" id="initializeRubricsButton" type="button"><i class="bi bi-plus-circle"></i> Inicializar rúbricas</button>
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
const setupState = {subjects: [], groups: []};

function setupEscape(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
}

function parseCsv(text) {
    const rows = [];
    let row = [], field = '', quoted = false;
    for (let index = 0; index < text.length; index++) {
        const character = text[index];
        if (character === '"') {
            if (quoted && text[index + 1] === '"') { field += '"'; index++; }
            else quoted = !quoted;
        } else if (character === ',' && !quoted) {
            row.push(field.trim()); field = '';
        } else if ((character === '\n' || character === '\r') && !quoted) {
            if (character === '\r' && text[index + 1] === '\n') index++;
            row.push(field.trim()); field = '';
            if (row.some(value => value !== '')) rows.push(row);
            row = [];
        } else field += character;
    }
    row.push(field.trim());
    if (row.some(value => value !== '')) rows.push(row);
    if (rows.length < 2) return [];
    const headers = rows.shift().map(value => value.toLowerCase().replace(/^\uFEFF/, ''));
    return rows.map(values => Object.fromEntries(headers.map((header, index) => [header, values[index] ?? ''])));
}

async function readSetupFile(type, file) {
    const rows = parseCsv(await file.text());
    if (type === 'subjects') {
        setupState.subjects = rows.map(row => ({code: row.clave, name: row.nombre, description: row.descripcion || null}));
    } else {
        setupState.groups = rows.map(row => ({
            name: row.nombre,
            semester: Number(row.semestre),
            group: row.grupo,
            period: row.periodo,
            subject_codes: String(row.asignaturas || '').split('|').map(value => value.trim()).filter(Boolean)
        }));
    }
    document.getElementById(`${type}Preview`).innerHTML = rows.length
        ? `<span class="text-success"><i class="bi bi-check-circle"></i> ${rows.length} fila(s) preparadas.</span>`
        : '<span class="text-danger">El archivo no contiene filas válidas.</span>';
    updateSetupSummary();
}

function updateSetupSummary() {
    const ready = setupState.subjects.length > 0;
    document.getElementById('importButton').disabled = !ready;
    document.getElementById('importSummary').textContent = ready
        ? `${setupState.subjects.length} asignaturas y ${setupState.groups.length} grupos listos para importar.`
        : 'Selecciona al menos el archivo de asignaturas.';
}

function downloadTemplate(type) {
    const content = type === 'subjects'
        ? 'clave,nombre,descripcion\nCLAVE-01,Nombre de la asignatura,Descripción opcional\n'
        : 'nombre,semestre,grupo,periodo,asignaturas\nGrupo 5A,5,A,2026-2,CLAVE-01|CLAVE-02\n';
    const blob = new Blob(['\uFEFF' + content], {type: 'text/csv;charset=utf-8'});
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = type === 'subjects' ? 'plantilla_asignaturas.csv' : 'plantilla_grupos.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

async function downloadCareerPackage() {
    try {
        const response = await fetch(`${window.SGPI_API_BASE_URL}/career/export`, {
            headers: {Authorization: `Bearer ${window.SGPI_SESSION?.token || ''}`, Accept: 'application/zip'}
        });
        if (!response.ok) throw new Error('No se pudo generar el paquete de la carrera.');
        const blob = await response.blob();
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `sgpi_${window.SGPI_SESSION?.user?.active_career?.clave || 'carrera'}_${new Date().toISOString().slice(0, 10)}.zip`;
        link.click();
        URL.revokeObjectURL(link.href);
    } catch (error) {
        document.getElementById('setupAlert').innerHTML = `<div class="alert alert-danger">${setupEscape(error.message)}</div>`;
    }
}

document.getElementById('subjectsFile').addEventListener('change', event => event.target.files[0] && readSetupFile('subjects', event.target.files[0]));
document.getElementById('groupsFile').addEventListener('change', event => event.target.files[0] && readSetupFile('groups', event.target.files[0]));
document.getElementById('importButton').addEventListener('click', async () => {
    const button = document.getElementById('importButton');
    button.disabled = true;
    try {
        const response = await api.post('/career/setup/catalog', setupState);
        const summary = response.summary || {};
        document.getElementById('setupAlert').innerHTML = `<div class="alert alert-success">${setupEscape(response.message)} Creadas: ${Number(summary.subjects_created || 0)} asignaturas y ${Number(summary.groups_created || 0)} grupos. Actualizadas: ${Number(summary.subjects_updated || 0)} asignaturas y ${Number(summary.groups_updated || 0)} grupos.</div>`;
    } catch (error) {
        document.getElementById('setupAlert').innerHTML = `<div class="alert alert-danger">${setupEscape(error.message)}</div>`;
    } finally {
        button.disabled = false;
    }
});
document.getElementById('initializeRubricsButton').addEventListener('click', async () => {
    const button = document.getElementById('initializeRubricsButton');
    button.disabled = true;
    try {
        const response = await api.post('/evaluations/rubrics/initialize', {});
        document.getElementById('setupAlert').innerHTML = `<div class="alert alert-success">${setupEscape(response.message)} Se crearon ${Number(response.created || 0)} contenedores.</div>`;
    } catch (error) {
        document.getElementById('setupAlert').innerHTML = `<div class="alert alert-danger">${setupEscape(error.message)}</div>`;
    } finally {
        button.disabled = false;
    }
});
</script>
</body>
</html>
