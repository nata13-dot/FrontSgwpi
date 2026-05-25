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
        /* Defensa mínima para Railway: evita vista rota si el CSS externo falla o queda cacheado. */
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background: #fff; color: #333645; }
        .navbar { background: linear-gradient(135deg, #1B396A 0%, #2D5A96 100%); box-shadow: 0 4px 12px rgba(0,0,0,.15); padding: 1rem 0; }
        .navbar .container-xl { display: flex; align-items: center; justify-content: space-between; }
        .navbar-brand { display: inline-flex; align-items: center; gap: .75rem; color: #fff; text-decoration: none; font-weight: 700; }
        .navbar-brand img { width: 48px; height: 48px; object-fit: contain; flex: 0 0 auto; }
        .navbar-brand-text { display: flex; flex-direction: column; line-height: 1.1; }
        .navbar-nav { display: flex; align-items: center; gap: .25rem; list-style: none; margin: 0; padding: 0; }
        .navbar .nav-link { color: rgba(255,255,255,.88); text-decoration: none; padding: .5rem .75rem; }
        .content-wrapper { display: flex; min-height: calc(100vh - 76px); }
        .sidebar { width: 250px; flex: 0 0 250px; min-height: 100vh; overflow-y: auto; background: #f8f9fa; border-right: 1px solid #e0e0e0; padding: 20px 0; }
        .sidebar-profile { display: flex; align-items: center; gap: 12px; padding: 15px 20px; margin-bottom: 15px; border-bottom: 1px solid #e0e0e0; }
        .sidebar-profile-photo { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
        .sidebar-item, .sidebar-group summary { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #333645; text-decoration: none; border-left: 3px solid transparent; }
        .sidebar-subitem { padding-left: 50px !important; font-size: .9rem; }
        .sidebar-item.active { background: rgba(27,57,106,.1); border-left-color: #1B396A; color: #1B396A; font-weight: 600; }
        .main-content { min-width: 0; flex: 1 1 auto; }
        .container-xl { width: min(100% - 2rem, 1320px); margin-left: auto; margin-right: auto; }
        @media (max-width: 768px) {
            .content-wrapper { display: block; }
            .sidebar { width: 100%; min-height: auto; border-right: 0; border-bottom: 1px solid #e0e0e0; }
        }
        .document-card { border-radius: 8px; }
        .document-row { border: 1px solid var(--bs-border-color); border-radius: 8px; padding: 1rem; }
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
                    <p class="text-muted mb-0">Repositorio privado para desarrollo de proyecto, tesis y residencias.</p>
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
const state = { projects: [], thesisDocs: [] };

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
    const projectSection = state.projects.length ? state.projects.map(project => `
        <div class="card border-0 shadow-sm document-card mb-3">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <h5 class="mb-2">${esc(project.project.title)}</h5>
                        <div class="project-meta">
                            <span class="badge text-bg-light border"><i class="bi bi-people"></i> ${esc(memberNames(project.integrantes))}</span>
                            <span class="badge text-bg-light border"><i class="bi bi-book"></i> ${esc(subjectNames(project.asignaturas))}</span>
                            <span class="badge text-bg-info">Taller de investigacion</span>
                        </div>
                    </div>
                    <div class="text-lg-end">
                        <small class="text-muted d-block">Repositorio privado</small>
                        <strong>${project.documents.length} documento(s)</strong>
                    </div>
                </div>
                <hr>
                ${renderUploadBox(project)}
                ${project.documents.length ? project.documents.map(document => renderRepositoryDocument(project, document)).join('') : '<p class="text-muted mb-0">Aun no hay documentos cargados para este proyecto.</p>'}
            </div>
        </div>
    `).join('') : '<div class="card border-0 shadow-sm mb-4"><div class="card-body text-center py-5"><i class="bi bi-file-earmark-ppt display-4 text-muted"></i><h5 class="mt-3">Sin proyectos disponibles</h5><p class="text-muted mb-0">Aun no hay proyectos vinculados a Taller de Investigacion I o II.</p></div></div>';

    container.innerHTML = `
        <section class="mb-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Desarrollo de proyecto</h4>
                    <p class="text-muted mb-0">Documentos privados de proyectos con Taller de Investigacion.</p>
                </div>
            </div>
            ${projectSection}
        </section>

        <section>
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h4 class="mb-1">Tesis y residencias de 9no</h4>
                    <p class="text-muted mb-0">Avances privados que docentes pueden revisar y administracion puede publicar.</p>
                </div>
            </div>
            ${canUploadThesis ? renderThesisUploadBox() : ''}
            ${state.thesisDocs.length ? state.thesisDocs.map(document => renderThesisDocument(document)).join('') : '<div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bi bi-journal-text display-4 text-muted"></i><h5 class="mt-3">Sin avances cargados</h5><p class="text-muted mb-0">Cuando un alumno de 9no suba tesis o residencias apareceran aqui.</p></div></div>'}
        </section>
    `;
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

function renderUploadBox(project) {
    if (!project.puede_subir) return '';

    return `
        <div class="document-row mb-3 bg-light">
            <h6><i class="bi bi-cloud-arrow-up"></i> Subir documento al repositorio privado</h6>
            <div class="row g-2">
                <div class="col-md-4">
                    <input class="form-control form-control-sm" id="name-${project.project.id}" maxlength="255" placeholder="Nombre del documento">
                </div>
                <div class="col-md-4">
                    <input class="form-control form-control-sm" id="desc-${project.project.id}" maxlength="5000" placeholder="Descripcion breve">
                </div>
                <div class="col-md-4">
                    <input class="form-control form-control-sm" id="authors-${project.project.id}" maxlength="1000" placeholder="Autores">
                </div>
                <div class="col-md-8">
                    <input class="form-control form-control-sm" type="file" id="file-${project.project.id}" accept=".pdf,.doc,.docx">
                    <div class="form-text">Permitidos: PDF, DOC y DOCX.</div>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-sm btn-primary w-100" onclick="uploadRepositoryDocument(${Number(project.project.id)})">
                        <i class="bi bi-upload"></i> Guardar privado
                    </button>
                </div>
            </div>
        </div>
    `;
}

function renderRepositoryDocument(project, document) {
    const allowed = document.allowed_extensions || ['pdf', 'doc', 'docx'];
    const accept = allowed.map(ext => `.${ext}`).join(',');
    const submittedBy = document.uploaded_by ? fullName(document.uploaded_by) : 'Sin carga';
    const publicBadge = document.is_public
        ? '<span class="badge text-bg-success">Publicado</span>'
        : '<span class="badge text-bg-warning">Privado</span>';

    return `
        <div class="document-row">
                <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h6 class="mb-0">${esc(document.nombre)}</h6>
                        ${publicBadge}
                    </div>
                    <p class="text-muted mb-2">${esc(document.descripcion || '')}</p>
                    <small class="text-muted">
                        <i class="bi bi-person-check"></i> ${esc(submittedBy)}
                        <span class="mx-2">|</span>
                        Formatos: ${esc(allowed.join(', ').toUpperCase())}
                        <span class="mx-2">|</span>
                        ${document.created_at ? new Date(document.created_at).toLocaleDateString('es-MX') : ''}
                    </small>
                </div>
                <div class="document-actions d-flex flex-wrap justify-content-xl-end align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadRepositoryDocument(${document.id}, '${escAttr(document.nombre)}')"><i class="bi bi-download"></i> Descargar</button>
                    ${project.puede_publicar ? `<button type="button" class="btn btn-sm ${document.is_public ? 'btn-outline-warning' : 'btn-success'}" onclick="toggleRepositoryPublication(${document.id}, ${document.is_public ? 'false' : 'true'})"><i class="bi ${document.is_public ? 'bi-eye-slash' : 'bi-globe2'}"></i> ${document.is_public ? 'Privado' : 'Publicar'}</button>` : ''}
                </div>
            </div>
        </div>
    `;
}

async function uploadRepositoryDocument(projectId) {
    const input = document.getElementById(`file-${projectId}`);
    const file = input?.files?.[0];
    const project = state.projects.find(item => Number(item.project.id) === Number(projectId));
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

    const formData = new FormData();
    formData.append('project_id', projectId);
    formData.append('nombre', document.getElementById(`name-${projectId}`).value.trim() || `Documento de investigacion - ${project?.project?.title || projectId}`);
    formData.append('descripcion', document.getElementById(`desc-${projectId}`).value.trim() || 'Documento de investigacion para revision.');
    formData.append('autores', document.getElementById(`authors-${projectId}`).value.trim() || memberNames(project?.integrantes || []));
    formData.append('archivo', file);

    try {
        const response = await fetch(`${API_BASE_URL}/repositorio/evaluation-documents`, {
            method: 'POST',
            credentials: 'include',
            headers: { Authorization: `Bearer ${auth.getToken()}` },
            body: formData
        });
        const result = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(result.error || result.message || 'No se pudo guardar el archivo.');
        }

        api.clearCache();
        showAlert('#alertContainer', 'success', 'Documento guardado en repositorio privado.');
        await loadDocuments(true);
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error al guardar el documento.');
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
        const response = await fetch(`${API_BASE_URL}/repositorio/thesis-documents`, {
            method: 'POST',
            credentials: 'include',
            headers: { Authorization: `Bearer ${auth.getToken()}` },
            body: formData
        });
        const result = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(result.error || result.message || Object.values(result.errors || {}).flat().join(' ') || 'No se pudo guardar el avance.');
        }

        api.clearCache();
        showAlert('#alertContainer', 'success', 'Avance guardado en repositorio privado.');
        await loadDocuments(true);
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
        await api.post(`/repositorio/${documentId}/publish`, { public: makePublic });
        showAlert('#alertContainer', 'success', makePublic ? 'Documento publicado.' : 'Documento marcado como privado.');
        await loadDocuments(true);
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
