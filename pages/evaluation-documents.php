<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos de evaluacion - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .document-card { border-radius: 8px; }
        .document-row { border: 1px solid var(--bs-border-color); border-radius: 8px; padding: 1rem; }
        .document-row + .document-row { margin-top: .75rem; }
        .project-meta { display: flex; flex-wrap: wrap; gap: .5rem; }
    </style>
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
<div class="d-flex content-wrapper">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
    <main class="main-content flex-grow-1">
        <div class="container-xl py-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1">Documentos de evaluacion</h1>
                    <p class="text-muted mb-0">Entregables adicionales ligados a proyectos y evaluaciones.</p>
                </div>
                <button class="btn btn-outline-primary" type="button" onclick="loadDocuments(true)">
                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                </button>
            </div>

            <div id="alertContainer"></div>
            <div id="documentsContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-3 mb-0">Cargando documentos...</p>
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
const state = { projects: [] };

document.addEventListener('DOMContentLoaded', () => {
    if (!auth.isAuthenticated()) {
        window.location.replace('/index.php');
        return;
    }
    loadDocuments();
});

async function loadDocuments(forceFresh = false) {
    const container = document.getElementById('documentsContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-3 mb-0">Cargando documentos...</p></div>';

    try {
        const response = await api.get('/evaluation-documents', forceFresh ? { _fresh: true } : {});
        state.projects = response.data || [];
        renderDocuments();
    } catch (error) {
        container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${esc(error.message || 'No se pudieron cargar los documentos.')}</div>`;
    }
}

function renderDocuments() {
    const container = document.getElementById('documentsContainer');

    if (!state.projects.length) {
        container.innerHTML = '<div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-file-earmark-slides display-4 text-muted"></i><h5 class="mt-3">Sin proyectos disponibles</h5><p class="text-muted mb-0">Aun no hay proyectos vinculados a documentos de evaluacion.</p></div></div>';
        return;
    }

    container.innerHTML = state.projects.map(project => `
        <div class="card border-0 shadow-sm document-card mb-3">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <h5 class="mb-2">${esc(project.project.title)}</h5>
                        <div class="project-meta">
                            <span class="badge text-bg-light border"><i class="bi bi-people"></i> ${esc(memberNames(project.integrantes))}</span>
                            <span class="badge text-bg-light border"><i class="bi bi-book"></i> ${esc(subjectNames(project.asignaturas))}</span>
                            ${project.requiere_documento_investigacion ? '<span class="badge text-bg-info">Taller de investigacion</span>' : ''}
                        </div>
                    </div>
                    <div class="text-lg-end">
                        <small class="text-muted d-block">Evaluaciones</small>
                        <strong>${project.evaluaciones.length ? project.evaluaciones.length : 'Sin sala asignada'}</strong>
                    </div>
                </div>
                <hr>
                ${project.deliverables.map(deliverable => renderDeliverable(project, deliverable)).join('')}
            </div>
        </div>
    `).join('');
}

function renderDeliverable(project, deliverable) {
    const allowed = deliverable.allowed_extensions || [];
    const accept = allowed.map(ext => `.${ext}`).join(',');
    const submittedBy = deliverable.submitted_by ? fullName(deliverable.submitted_by) : 'Sin carga';
    const canUpload = project.puede_subir;

    return `
        <div class="document-row">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h6 class="mb-0">${esc(deliverable.nombre)}</h6>
                        <span class="badge ${statusClass(deliverable.estado)}">${esc(deliverable.estado || 'pendiente')}</span>
                    </div>
                    <p class="text-muted mb-2">${esc(deliverable.descripcion || '')}</p>
                    <small class="text-muted">
                        <i class="bi bi-person-check"></i> ${esc(submittedBy)}
                        <span class="mx-2">|</span>
                        Formatos: ${esc(allowed.join(', ').toUpperCase())}
                    </small>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    ${deliverable.archivo_path ? `<button type="button" class="btn btn-sm btn-outline-secondary" onclick="descargarEntregable(${deliverable.id}, '${escAttr(deliverable.nombre)}')"><i class="bi bi-download"></i> Descargar</button>` : ''}
                    <input class="form-control form-control-sm" type="file" id="file-${deliverable.id}" accept="${accept}" ${canUpload ? '' : 'disabled'}>
                    <button type="button" class="btn btn-sm btn-primary" onclick="uploadDocument(${deliverable.id})" ${canUpload ? '' : 'disabled'}>
                        <i class="bi bi-upload"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    `;
}

async function uploadDocument(deliverableId) {
    const input = document.getElementById(`file-${deliverableId}`);
    const file = input?.files?.[0];
    const deliverable = state.projects.flatMap(project => project.deliverables).find(item => Number(item.id) === Number(deliverableId));
    const allowed = deliverable?.allowed_extensions || [];

    if (!file) {
        showAlert('#alertContainer', 'warning', 'Selecciona un archivo antes de guardar.');
        return;
    }

    const extension = file.name.split('.').pop().toLowerCase();
    if (!allowed.includes(extension)) {
        showAlert('#alertContainer', 'danger', `Formato no permitido. Usa: ${allowed.join(', ').toUpperCase()}`);
        return;
    }

    if (!validarTamañoArchivo(file.size)) {
        showAlert('#alertContainer', 'danger', `Archivo muy grande. Maximo ${Number(window.SGPI_SETTINGS?.max_file_size_mb || 50)}MB.`);
        return;
    }

    const formData = new FormData();
    formData.append('archivo', file);

    try {
        const response = await fetch(`${API_BASE_URL}/deliverables/${deliverableId}/upload`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${auth.getToken()}` },
            body: formData
        });
        const result = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(result.error || result.message || 'No se pudo guardar el archivo.');
        }

        api.clearCache();
        showAlert('#alertContainer', 'success', 'Documento guardado correctamente.');
        await loadDocuments(true);
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error al guardar el documento.');
    }
}

function statusClass(status) {
    return {
        pendiente: 'text-bg-warning',
        enviado: 'text-bg-primary',
        revisado: 'text-bg-info',
        aprobado: 'text-bg-success'
    }[status] || 'text-bg-secondary';
}

function memberNames(members) {
    const names = (members || []).map(fullName).filter(Boolean);
    return names.length ? names.join(', ') : 'Sin integrantes';
}

function subjectNames(subjects) {
    const names = (subjects || []).map(subject => subject.nombre || subject.clave).filter(Boolean);
    return names.length ? names.join(', ') : 'Sin asignaturas';
}

function fullName(user) {
    return [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ').trim();
}

function esc(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

function escAttr(value) {
    return esc(value).replaceAll("'", '&#39;');
}
</script>
</body>
</html>
