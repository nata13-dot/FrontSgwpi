<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

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
    <title>Documentos de evaluacion - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .document-card { border-radius: 8px; background: var(--surface-bg); color: var(--text-dark); }
        .document-row { background: var(--surface-bg); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-dark); padding: 1rem; }
        .document-row + .document-row { margin-top: .75rem; }
        .project-meta { display: flex; flex-wrap: wrap; gap: .5rem; }
        .project-meta .badge {
            max-width: min(100%, 680px);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .document-actions {
            min-width: min(100%, 420px);
        }
        .document-actions .form-control {
            max-width: 240px;
        }
        .delivery-slot { border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; height: 100%; }
        .release-student { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .5rem 0; border-bottom: 1px solid var(--border-color); }
        .release-student:last-child { border-bottom: 0; }
        @media (max-width: 767.98px) {
            .document-actions,
            .document-actions .form-control,
            .document-actions .btn {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
    <div class="d-flex content-wrapper">
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
        <main class="main-content flex-grow-1">
            <div class="container-xl mt-5 mb-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1">Documentos de evaluacion</h1>
                    <p class="text-muted mb-0">Revision de entregas de evaluacion, tesis y residencias.</p>
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
const state = { projects: [], thesisDocs: [], section: 'evaluations' };

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
        const [projectResponse, thesisResponse] = await Promise.all([
            api.get('/repositorio/evaluation-documents', forceFresh ? { _fresh: true } : {}),
            api.get('/repositorio/thesis-documents', forceFresh ? { _fresh: true } : {})
        ]);
        state.projects = projectResponse.data || [];
        state.thesisDocs = thesisResponse.data?.data || thesisResponse.data || [];
        renderDocuments();
    } catch (error) {
        container.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${esc(error.message || 'No se pudieron cargar los documentos.')}</div>`;
    }
}

function renderDocuments() {
    const container = document.getElementById('documentsContainer');

    const currentUser = auth.getCurrentUser() || {};
    const canUploadThesis = Number(currentUser.perfil_id) === 3 && Number(currentUser.semestre) === 9;
    const semesters = [...new Set(state.projects.map(item => Number(item.project.semestre)).filter(Boolean))].sort((a, b) => a - b);
    const projectSection = semesters.length ? semesters.map(semester => `
        <div class="mb-4">
            <h4 class="mb-3">Semestre ${semester}</h4>
            ${state.projects.filter(item => Number(item.project.semestre) === semester).map(project => `
        <div class="card border-0 shadow-sm document-card mb-3">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <h5 class="mb-2">${esc(project.project.title)}</h5>
                        <div class="project-meta">
                            <span class="badge text-bg-light border"><i class="bi bi-people"></i> ${esc(memberNames(project.integrantes))}</span>
                            <span class="badge text-bg-secondary">Semestre ${esc(project.project.semestre)}</span>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row g-3">
                    <div class="col-xl-6">${renderEvaluationDelivery(project, 'release_sheet')}</div>
                    <div class="col-xl-6">${renderEvaluationDelivery(project, 'presentation')}</div>
                </div>
            </div>
        </div>
            `).join('')}
        </div>
    `).join('') : '<div class="card border-0 shadow-sm mb-4"><div class="card-body text-center py-5"><i class="bi bi-file-earmark-ppt display-4 text-muted"></i><h5 class="mt-3">Sin proyectos disponibles</h5></div></div>';

    container.innerHTML = `
        <div class="nav nav-pills gap-2 mb-4">
            <button class="btn ${state.section === 'evaluations' ? 'btn-primary' : 'btn-outline-primary'}" onclick="setDocumentSection('evaluations')">
                <i class="bi bi-clipboard-check"></i> Entregas de evaluacion
            </button>
            <button class="btn ${state.section === 'thesis' ? 'btn-primary' : 'btn-outline-primary'}" onclick="setDocumentSection('thesis')">
                <i class="bi bi-journal-text"></i> Tesis y residencias
            </button>
        </div>

        <section class="${state.section === 'evaluations' ? '' : 'd-none'}">
            <h3 class="mb-1">Entregas de evaluacion</h3>
            <p class="text-muted mb-4">Hoja de liberacion y presentacion, organizadas por semestre.</p>
            ${projectSection}
        </section>

        <section class="${state.section === 'thesis' ? '' : 'd-none'}">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h3 class="mb-1">Tesis y residencias de 9no</h3>
                    <p class="text-muted mb-0">Avances privados que docentes pueden revisar y administracion puede publicar.</p>
                </div>
            </div>
            ${canUploadThesis ? renderThesisUploadBox() : ''}
            ${state.thesisDocs.length ? state.thesisDocs.map(document => renderThesisDocument(document)).join('') : '<div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-journal-text display-4 text-muted"></i><h5 class="mt-3">Sin avances cargados</h5><p class="text-muted mb-0">Cuando un alumno de 9no suba tesis o residencias apareceran aqui.</p></div></div>'}
        </section>
    `;
}

function setDocumentSection(section) {
    state.section = section;
    renderDocuments();
}

function renderEvaluationDelivery(project, type) {
    const delivery = type === 'release_sheet' ? project.release_sheet : project.presentation;
    const title = type === 'release_sheet' ? '1. Hoja de liberacion' : '2. Presentacion';
    const icon = type === 'release_sheet' ? 'bi-file-earmark-check' : 'bi-file-earmark-slides';
    const accepted = (delivery.allowed_extensions || []).map(ext => `.${ext}`).join(',');
    const status = delivery.uploaded
        ? '<span class="badge text-bg-success">Archivo cargado</span>'
        : '<span class="badge text-bg-danger">Archivo pendiente</span>';
    const document = delivery.document;

    return `
        <div class="delivery-slot">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0"><i class="bi ${icon}"></i> ${title}</h5>
                ${status}
            </div>
            ${document ? `
                <div class="small mb-3">
                    <strong>${esc(document.nombre)}</strong>
                    <div class="text-muted">${document.created_at ? new Date(document.created_at).toLocaleString('es-MX') : ''}</div>
                    <button class="btn btn-sm btn-outline-secondary mt-2" onclick="downloadRepositoryDocument(${document.id}, '${escAttr(document.nombre)}')">
                        <i class="bi bi-download"></i> Descargar
                    </button>
                </div>` : ''}
            ${project.puede_subir ? `
                <div class="mb-3">
                    <input class="form-control form-control-sm" type="file" id="${type}-file-${project.project.id}" accept="${accepted}">
                    <div class="form-text">Formatos: ${(delivery.allowed_extensions || []).join(', ').toUpperCase()}.</div>
                    <button class="btn btn-sm btn-primary mt-2" onclick="uploadEvaluationDelivery(${project.project.id}, '${type}')">
                        <i class="bi bi-upload"></i> ${delivery.uploaded ? 'Reemplazar archivo' : 'Subir archivo'}
                    </button>
                </div>` : ''}
            ${type === 'release_sheet' ? renderReleaseReview(project, delivery) : ''}
        </div>
    `;
}

function renderReleaseReview(project, delivery) {
    if (!delivery.uploaded) {
        return '<p class="text-muted small mb-0">La revision por alumno estara disponible cuando se cargue la hoja.</p>';
    }

    return `
        <div class="mt-3">
            <h6>Alumnos liberados</h6>
            ${(delivery.students || []).map(student => `
                <label class="release-student">
                    <span>${esc(fullName(student))}</span>
                    <span class="form-check form-switch mb-0">
                        <input class="form-check-input release-status-${documentId(delivery)}" type="checkbox"
                            data-student-id="${escAttr(student.id)}" ${student.released ? 'checked' : ''}
                            ${project.puede_revisar ? '' : 'disabled'}>
                    </span>
                </label>
            `).join('')}
            ${project.puede_revisar ? `
                <button class="btn btn-sm btn-success mt-3" onclick="saveReleaseReview(${documentId(delivery)})">
                    <i class="bi bi-check2-square"></i> Guardar liberacion
                </button>` : ''}
        </div>
    `;
}

function documentId(delivery) {
    return Number(delivery?.document?.id || 0);
}

function renderThesisUploadBox() {
    return `
        <div class="document-row mb-3 bg-light">
            <h6><i class="bi bi-cloud-arrow-up"></i> Subir avance de 9no</h6>
            <div class="row g-2">
                <div class="col-md-3">
                    <select class="form-select form-select-sm" id="thesisTipo">
                        <option value="tesis">Tesis general</option>
                        <option value="residencias">Residencias</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input class="form-control form-control-sm" id="thesisName" maxlength="255" placeholder="Nombre del avance">
                </div>
                <div class="col-md-3">
                    <input class="form-control form-control-sm" id="thesisDesc" maxlength="5000" placeholder="Descripcion breve">
                </div>
                <div class="col-md-3">
                    <input class="form-control form-control-sm" id="thesisAuthors" maxlength="1000" placeholder="Autores">
                </div>
                <div class="col-md-8">
                    <input class="form-control form-control-sm" type="file" id="thesisFile" accept=".pdf,.doc,.docx">
                    <div class="form-text">Permitidos: PDF, DOC y DOCX. Se guarda privado hasta que administracion lo publique.</div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-primary w-100" onclick="uploadThesisDocument()">
                        <i class="bi bi-upload"></i> Guardar avance
                    </button>
                </div>
            </div>
        </div>
    `;
}

function renderThesisDocument(document) {
    const publicBadge = document.visibility === 'public'
        ? '<span class="badge text-bg-success">Publicado</span>'
        : '<span class="badge text-bg-warning">Privado</span>';
    const categoryBadge = document.document_category === 'thesis_residency'
        ? '<span class="badge text-bg-info">Residencias</span>'
        : '<span class="badge text-bg-primary">Tesis</span>';
    const submittedBy = document.uploader ? fullName(document.uploader) : 'Sin carga';
    const canPublish = Number(auth.getCurrentUser()?.perfil_id) === 1;

    return `
        <div class="document-row mb-3">
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h6 class="mb-0">${esc(document.nombre)}</h6>
                        ${categoryBadge}
                        ${publicBadge}
                    </div>
                    <p class="text-muted mb-2">${esc(document.descripcion || '')}</p>
                    <small class="text-muted">
                        <i class="bi bi-person-check"></i> ${esc(submittedBy)}
                        <span class="mx-2">|</span>
                        ${document.created_at ? new Date(document.created_at).toLocaleDateString('es-MX') : ''}
                    </small>
                </div>
                <div class="document-actions d-flex flex-wrap justify-content-xl-end align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadRepositoryDocument(${document.id}, '${escAttr(document.nombre)}')"><i class="bi bi-download"></i> Descargar</button>
                    ${canPublish ? `<button type="button" class="btn btn-sm ${document.visibility === 'public' ? 'btn-outline-warning' : 'btn-success'}" onclick="toggleRepositoryPublication(${document.id}, ${document.visibility === 'public' ? 'false' : 'true'})"><i class="bi ${document.visibility === 'public' ? 'bi-eye-slash' : 'bi-globe2'}"></i> ${document.visibility === 'public' ? 'Privado' : 'Publicar'}</button>` : ''}
                </div>
            </div>
        </div>
    `;
}

async function uploadEvaluationDelivery(projectId, type) {
    const input = document.getElementById(`${type}-file-${projectId}`);
    const file = input?.files?.[0];
    const project = state.projects.find(item => Number(item.project.id) === Number(projectId));
    const delivery = type === 'release_sheet' ? project?.release_sheet : project?.presentation;
    const allowed = delivery?.allowed_extensions || [];

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
    formData.append('project_id', projectId);
    formData.append('document_type', type);
    formData.append('nombre', type === 'release_sheet' ? 'Hoja de liberacion' : 'Presentacion');
    formData.append('descripcion', `Entrega de evaluacion: ${type === 'release_sheet' ? 'hoja de liberacion' : 'presentacion'}.`);
    formData.append('autores', memberNames(project?.integrantes || []));
    formData.append('archivo', file);

    try {
        const result = await api.post('/repositorio/evaluation-documents', formData, { _timeout: 120000 });
        const projectEntry = state.projects.find(item => String(item.project.id) === String(projectId));
        if (projectEntry) projectEntry[type === 'release_sheet' ? 'release_sheet' : 'presentation'] = result.document;
        showAlert('#alertContainer', 'success', 'Entrega guardada correctamente.');
        renderDocuments();
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error al guardar el documento.');
    }
}

async function saveReleaseReview(documentId) {
    const students = [...document.querySelectorAll(`.release-status-${documentId}`)].map(input => ({
        student_id: input.dataset.studentId,
        released: input.checked
    }));

    try {
        const result = await api.put(`/repositorio/evaluation-documents/${documentId}/release-status`, { students });
        const projectEntry = state.projects.find(item => String(item.release_sheet?.document?.id) === String(documentId));
        if (projectEntry) projectEntry.release_sheet = result.document;
        showAlert('#alertContainer', 'success', 'Liberacion actualizada.');
        renderDocuments();
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'No se pudo guardar la liberacion.');
    }
}

async function uploadThesisDocument() {
    const input = document.getElementById('thesisFile');
    const file = input?.files?.[0];
    const allowed = ['pdf', 'doc', 'docx'];

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

    const currentUser = auth.getCurrentUser() || {};
    const formData = new FormData();
    formData.append('tipo', document.getElementById('thesisTipo').value);
    formData.append('nombre', document.getElementById('thesisName').value.trim() || 'Avance de tesis o residencias');
    formData.append('descripcion', document.getElementById('thesisDesc').value.trim() || 'Avance privado para revision.');
    formData.append('autores', document.getElementById('thesisAuthors').value.trim() || fullName(currentUser));
    formData.append('archivo', file);

    try {
        const result = await api.post('/repositorio/thesis-documents', formData, { _timeout: 120000 });
        state.thesisDocs.unshift(result.document);
        showAlert('#alertContainer', 'success', 'Avance guardado en repositorio privado.');
        renderDocuments();
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error al guardar el avance.');
    }
}

async function downloadRepositoryDocument(documentId, name) {
    try {
        const response = await fetch(`${API_BASE_URL}/repositorio/${documentId}/download`, {
            headers: { Authorization: `Bearer ${auth.getToken()}` },
            credentials: 'include'
        });
        if (!response.ok) throw new Error('No se pudo descargar el documento.');
        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = name || 'documento';
        link.click();
        URL.revokeObjectURL(url);
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error descargando documento.');
    }
}

async function toggleRepositoryPublication(documentId, makePublic) {
    const confirmed = await confirmAction({
        title: makePublic ? 'Publicar documento' : 'Marcar como privado',
        text: makePublic
            ? 'El documento sera visible en el repositorio publico.'
            : 'El documento dejara de ser visible para visitantes del repositorio.',
        confirmButtonText: makePublic ? 'Publicar' : 'Hacer privado'
    });
    if (!confirmed) return;

    try {
        const result = await api.post(`/repositorio/${documentId}/publish`, { public: makePublic });
        const thesisIndex = state.thesisDocs.findIndex(document => String(document.id) === String(documentId));
        if (thesisIndex >= 0) state.thesisDocs[thesisIndex] = result.document;
        state.projects.forEach(project => {
            ['release_sheet', 'presentation'].forEach(type => {
                if (String(project[type]?.document?.id) === String(documentId)) {
                    project[type].document = { ...project[type].document, ...result.document };
                }
            });
        });
        showAlert('#alertContainer', 'success', makePublic ? 'Documento publicado.' : 'Documento marcado como privado.');
        renderDocuments();
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'No se pudo actualizar la visibilidad.');
    }
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
