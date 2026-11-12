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
    <title>Documentos de evaluación - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
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
        .delivery-slot.is-done { border-color: var(--success-accent); }
        .readiness-missing { font-size: .85rem; }
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
                    <h1 class="mb-1">Documentos de evaluación</h1>
                    <p class="text-muted mb-0">Revisión de entregas de evaluación, tesis y residencias.</p>
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
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';
// Mismo criterio que role:admin en la API (administración, jefatura o administración general).
const CAN_PUBLISH_REPOSITORY = <?= is_admin() ? 'true' : 'false' ?>;</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
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
        state.presentationMaxMb = Number(projectResponse.presentation_max_mb || 100);
        // Se agrupa por la etapa que presentará el proyecto este periodo (incluye casos especiales).
        state.projects = (projectResponse.data || []).filter(item => {
            const semester = evaluationSemester(item);
            return Number.isInteger(semester) && semester >= 5 && semester <= 9;
        });
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
    const semesters = [...new Set(state.projects.map(evaluationSemester))].sort((a, b) => a - b);
    const projectSection = semesters.length ? semesters.map(semester => `
        <div class="mb-4">
            <h4 class="mb-3">${semester}° · ${esc(STAGE_LABELS[semester] || 'Evaluación')}</h4>
            ${state.projects.filter(item => evaluationSemester(item) === semester).map(renderProjectCard).join('')}
        </div>
    `).join('') : '<div class="card border-0 shadow-sm mb-4"><div class="card-body text-center py-5"><i class="bi bi-file-earmark-ppt display-4 text-muted"></i><h5 class="mt-3">Sin proyectos disponibles</h5></div></div>';

    container.innerHTML = `
        <div class="nav nav-pills gap-2 mb-4">
            <button class="btn ${state.section === 'evaluations' ? 'btn-primary' : 'btn-outline-primary'}" onclick="setDocumentSection('evaluations')">
                <i class="bi bi-clipboard-check"></i> Entregas de evaluación
            </button>
            <button class="btn ${state.section === 'thesis' ? 'btn-primary' : 'btn-outline-primary'}" onclick="setDocumentSection('thesis')">
                <i class="bi bi-journal-text"></i> Tesis y residencias
            </button>
        </div>

        <section class="${state.section === 'evaluations' ? '' : 'd-none'}">
            <h3 class="mb-1">Entregas de evaluación</h3>
            <p class="text-muted mb-4">Lo que cada proyecto necesita para presentar: hoja de liberación y presentación; desde 6° también el enlace de Drive y el documento de reporte. Sólo presentan los alumnos liberados.</p>
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
    if (state.section === section) return;
    SGPIViewTransition.run(() => {
        state.section = section;
        renderDocuments();
    }, document.getElementById('documentsContainer'));
}

const STAGE_LABELS = { 5: 'Propuesta', 6: 'Avance', 7: 'Avance', 8: 'Titulación', 9: 'Residencia o tesis' };

function evaluationSemester(item) {
    return Number(item?.evaluation_semester ?? item?.project?.semestre);
}

function renderProjectCard(project) {
    const readiness = project.readiness || {};
    const requirements = readiness.requirements || {};
    const badge = readiness.ready
        ? '<span class="badge text-bg-success"><i class="bi bi-check2-circle"></i> Listo para evaluar</span>'
        : '<span class="badge text-bg-warning"><i class="bi bi-hourglass-split"></i> Incompleto</span>';
    const missing = readiness.ready || !(readiness.missing || []).length ? ''
        : `<div class="readiness-missing text-muted mt-1">Falta: ${esc(readiness.missing.join(', '))}.</div>`;
    const released = readiness.release_sheet_uploaded
        ? `<span class="badge text-bg-light border"><i class="bi bi-person-check"></i> ${Number(readiness.released_count || 0)} de ${(readiness.students || []).length} liberados</span>` : '';
    const slots = [renderEvaluationDelivery(project, 'release_sheet'), renderEvaluationDelivery(project, 'presentation')];
    if (requirements.link) slots.push(renderLinkSlot(project));
    if (requirements.report) slots.push(renderEvaluationDelivery(project, 'report'));

    return `
        <div class="card border-0 shadow-sm document-card mb-3">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <h5 class="mb-2">${esc(project.project.title)}</h5>
                        <div class="project-meta">
                            <span class="badge text-bg-light border"><i class="bi bi-people"></i> ${esc(memberNames(project.integrantes))}</span>
                            <span class="badge text-bg-secondary">Se evalúa en ${evaluationSemester(project)}° · ${esc(STAGE_LABELS[evaluationSemester(project)] || '')}</span>
                            ${released}
                        </div>
                    </div>
                    <div class="text-lg-end">${badge}${missing}</div>
                </div>
                ${renderDenials(project)}
                <hr>
                <div class="row g-3">${slots.map(slot => `<div class="col-xl-6">${slot}</div>`).join('')}</div>
            </div>
        </div>`;
}

function renderLinkSlot(project) {
    const link = project.readiness?.link || '';
    return `
        <div class="delivery-slot ${reviewState(project, 'link').state === 'aprobada' ? 'is-done' : ''}">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0"><i class="bi bi-link-45deg"></i> 3. Enlace de Drive</h5>
                ${reviewBadge(project, 'link')}
            </div>
            <p class="small text-muted">Carpeta de Google Drive con el video de la presentación y la presentación con el video agregado. Compártela para que la vean los evaluadores.</p>
            ${link ? `<p class="small mb-2 text-break"><a href="${escAttr(link)}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"></i> ${esc(link)}</a></p>` : ''}
            ${project.puede_subir ? `
                <div class="input-group input-group-sm">
                    <input class="form-control" type="url" id="link-${project.project.id}" placeholder="https://drive.google.com/..." value="${escAttr(link)}" maxlength="500">
                    <button class="btn btn-primary" type="button" onclick="saveEvaluationLink(${project.project.id})"><i class="bi bi-save"></i> Guardar</button>
                </div>` : ''}
            ${renderReview(project, 'link')}
        </div>`;
}

const REVIEW_BADGES = {
    faltante: ['danger', 'Pendiente de entregar'],
    pendiente: ['info', 'En revisión'],
    con_observaciones: ['warning', 'Con observaciones'],
    aprobada: ['success', 'Aprobada']
};

function reviewState(project, type) {
    return project.readiness?.deliveries?.[type] || { state: 'faltante' };
}

function reviewBadge(project, type) {
    const [tone, label] = REVIEW_BADGES[reviewState(project, type).state] || REVIEW_BADGES.faltante;
    return `<span class="badge text-bg-${tone}">${label}</span>`;
}

// Comentario del encargado y, para quien revisa, los botones para aprobar o devolver con observaciones.
function renderReview(project, type) {
    const review = reviewState(project, type);
    const comment = review.comment ? `
        <div class="alert ${review.state === 'con_observaciones' ? 'alert-warning' : 'alert-light border'} py-2 px-3 small mb-2">
            <strong>${review.state === 'con_observaciones' ? 'Observaciones' : 'Comentario'}${review.reviewed_by ? ` de ${esc(review.reviewed_by)}` : ''}:</strong> ${esc(review.comment)}
        </div>` : '';
    const actions = project.puede_revisar && review.state !== 'faltante' ? `
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-success" type="button" onclick="reviewDelivery(${project.project.id}, '${type}', 'aprobada')" ${review.state === 'aprobada' ? 'disabled' : ''}><i class="bi bi-check2"></i> Aprobar</button>
            <button class="btn btn-sm btn-outline-warning" type="button" onclick="reviewDelivery(${project.project.id}, '${type}', 'con_observaciones')"><i class="bi bi-chat-left-text"></i> Con observaciones</button>
        </div>` : '';
    return comment || actions ? `<div class="mt-2">${comment}${actions}</div>` : '';
}

async function reviewDelivery(projectId, type, status) {
    let comment = null;
    if (status === 'con_observaciones') {
        const result = await Swal.fire({
            title: 'Observaciones para el equipo',
            input: 'textarea',
            inputPlaceholder: 'Qué deben corregir antes de presentar',
            inputAttributes: { maxlength: 1000 },
            showCancelButton: true,
            confirmButtonText: 'Enviar observaciones',
            cancelButtonText: 'Cancelar',
            inputValidator: value => (value || '').trim().length < 5 ? 'Escribe qué deben corregir.' : undefined
        });
        if (!result.isConfirmed) return;
        comment = result.value.trim();
    }
    try {
        const response = await api.put('/repositorio/evaluation-documents/review', { project_id: projectId, delivery: type, status, comment });
        showAlert('#alertContainer', 'success', response.message || 'Revisión guardada.');
        await loadDocuments(true);
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'No se pudo guardar la revisión.');
    }
}

function deliveryFor(project, type) {
    return type === 'release_sheet' ? project?.release_sheet : (type === 'presentation' ? project?.presentation : project?.report);
}

function renderEvaluationDelivery(project, type) {
    const delivery = deliveryFor(project, type) || { allowed_extensions: [] };
    const reportStage = project.readiness?.requirements?.report;
    const title = { release_sheet: '1. Hoja de liberación', presentation: '2. Presentación', report: `4. Documento de reporte (${reportStage === 'final' ? 'final' : 'avance'})` }[type];
    const icon = { release_sheet: 'bi-file-earmark-check', presentation: 'bi-file-earmark-slides', report: 'bi-file-earmark-text' }[type];
    const hint = {
        release_sheet: 'Formatos: PDF, DOC o DOCX.',
        presentation: `Sólo PPTX, hasta ${Number(delivery.max_mb || state.presentationMaxMb || 100)} MB y sin video; el video va en el enlace de Drive.`,
        report: `Formatos: PDF, DOC o DOCX, hasta ${Number(delivery.max_mb || 25)} MB.`
    }[type];
    const accepted = (delivery.allowed_extensions || []).map(ext => `.${ext}`).join(',');
    const reviewed = type !== 'release_sheet';
    const status = reviewed ? reviewBadge(project, type) : (delivery.uploaded
        ? '<span class="badge text-bg-success">Archivo cargado</span>'
        : '<span class="badge text-bg-danger">Archivo pendiente</span>');
    const done = reviewed ? reviewState(project, type).state === 'aprobada' : delivery.uploaded;
    const document = delivery.document;

    return `
        <div class="delivery-slot ${done ? 'is-done' : ''}">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0"><i class="bi ${icon}"></i> ${title}</h5>
                ${status}
            </div>
            ${document ? `
                <div class="small mb-3">
                    <strong>${esc(document.nombre)}</strong>
                    <div class="text-muted">${document.created_at ? new Date(document.created_at).toLocaleString('es-MX') : ''}</div>
                    <button class="btn btn-sm btn-outline-secondary mt-2" data-name="${esc(document.nombre)}" onclick="downloadRepositoryDocument(${Number(document.id)}, this.dataset.name)">
                        <i class="bi bi-download"></i> Descargar
                    </button>
                </div>` : ''}
            ${project.puede_subir ? `
                <div class="mb-3">
                    <input class="form-control form-control-sm" type="file" id="${type}-file-${project.project.id}" accept="${accepted}">
                    <div class="form-text">${esc(hint)}</div>
                    <button class="btn btn-sm btn-primary mt-2" onclick="uploadEvaluationDelivery(${project.project.id}, '${type}')">
                        <i class="bi bi-upload"></i> ${delivery.uploaded ? 'Reemplazar archivo' : 'Subir archivo'}
                    </button>
                </div>` : ''}
            ${type === 'release_sheet' ? renderReleaseReview(project, delivery) : renderReview(project, type)}
        </div>
    `;
}

const DEFAULT_DENIAL = 'Se te impide continuar por una decisión administrativa.';

// Negar la presentación por decisión administrativa (PresentationDenialService en la API; igual que la app).
// Los nombres viajan en atributos data-*, nunca dentro del código de los botones.
function renderDenials(project) {
    const team = project.readiness?.denial;
    const students = project.readiness?.students || [];
    const denied = students.filter(student => student.denial);
    if (!project.puede_revisar) {
        const me = String((auth.getCurrentUser() || {}).id || '');
        const mine = team || students.find(student => String(student.id) === me)?.denial;
        return mine ? `<div class="alert alert-danger small mt-3 mb-0"><strong>${team ? 'La presentación de tu equipo fue negada.' : 'Tu presentación fue negada.'}</strong> Motivo: ${esc(mine.reason)}</div>` : '';
    }
    const allowButton = (denial, who) => `<button type="button" class="btn btn-sm btn-outline-primary" data-allow-denial="${Number(denial.id)}" data-allow-who="${esc(who)}"><i class="bi bi-unlock"></i> Permitir de nuevo</button>`;
    const teamRow = team
        ? `<div class="alert alert-danger small d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"><span><strong>Presentación del equipo negada.</strong> Motivo: ${esc(team.reason)}${team.denied_by ? ` <span class="text-muted">· ${esc(team.denied_by)}, ${esc(new Date(team.denied_at).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' }))}</span>` : ''}</span>${allowButton(team, 'del equipo')}</div>`
        : `<div class="d-flex flex-wrap align-items-center gap-2 mb-2"><span class="small text-muted me-auto">Decisión administrativa</span><button type="button" class="btn btn-sm btn-outline-danger" data-deny-project="${Number(project.project.id)}"><i class="bi bi-slash-circle"></i> Negar al equipo</button></div>`;
    const studentRows = denied.map(student => `<div class="alert alert-danger small d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"><span><strong>${esc(student.name)}:</strong> presentación negada. Motivo: ${esc(student.denial.reason)}</span>${allowButton(student.denial, `de ${student.name}`)}</div>`).join('');
    const perStudent = !team && students.length > 1 ? `<div class="d-flex flex-wrap gap-1">${students.filter(student => !student.denial).map(student => `<button type="button" class="btn btn-sm btn-link text-danger" data-deny-project="${Number(project.project.id)}" data-deny-student="${esc(student.id)}" data-deny-name="${esc(student.name)}"><i class="bi bi-slash-circle"></i> Negar a ${esc(student.name.split(' ')[0])}</button>`).join('')}</div>` : '';
    return `<div class="mt-3">${teamRow}${studentRows}${perStudent}</div>`;
}

document.addEventListener('click', async event => {
    const deny = event.target.closest('[data-deny-project]');
    const allow = event.target.closest('[data-allow-denial]');
    if (deny) {
        const studentId = deny.dataset.denyStudent || null;
        const name = deny.dataset.denyName || '';
        const result = await Swal.fire({
            title: studentId ? `Negar la presentación de ${name}` : 'Negar la presentación del equipo',
            text: `${studentId ? 'El alumno no podrá ser liberado ni presentar; el resto del equipo sí presenta.' : 'El equipo no podrá ponerse en sala ni ser evaluado en este periodo.'} El motivo es opcional: si lo dejas vacío verán «${DEFAULT_DENIAL}»`,
            input: 'textarea', inputPlaceholder: 'Motivo (opcional)', showCancelButton: true, reverseButtons: true,
            confirmButtonText: 'Negar presentación', cancelButtonText: 'Cancelar', confirmButtonColor: '#b23b3b',
        });
        if (!result.isConfirmed) return;
        try {
            const response = await api.post('/repositorio/evaluation-documents/denials', { project_id: Number(deny.dataset.denyProject), student_id: studentId, reason: String(result.value || '').trim() || null });
            showAlert('#alertContainer', 'success', response.message);
            await loadDocuments(true);
        } catch (error) {
            showAlert('#alertContainer', 'danger', error.message || 'No se pudo negar la presentación.');
        }
    }
    if (allow) {
        if (!await confirmAction({ title: 'Permitir de nuevo', text: `La presentación ${allow.dataset.allowWho} vuelve a estar permitida y se le avisará en la app y por correo.`, confirmButtonText: 'Sí, permitir' })) return;
        try {
            const response = await api.delete(`/repositorio/evaluation-documents/denials/${Number(allow.dataset.allowDenial)}`);
            showAlert('#alertContainer', 'success', response.message);
            await loadDocuments(true);
        } catch (error) {
            showAlert('#alertContainer', 'danger', error.message || 'No se pudo permitir la presentación.');
        }
    }
});

function renderReleaseReview(project, delivery) {
    if (!delivery.uploaded) {
        return '<p class="text-muted small mb-0">La revisión por alumno estará disponible cuando se cargue la hoja.</p>';
    }

    return `
        <div class="mt-3">
            <h6>Alumnos liberados</h6>
            ${project.puede_revisar ? '' : '<p class="small text-muted mb-1">La liberación la deciden la jefatura, la asistencia o la coordinación de proyectos.</p>'}
            ${(delivery.students || []).map(student => `
                <label class="release-student">
                    <span>${esc(fullName(student))}${isDenied(project, student) ? ' <span class="badge bg-danger">Presentación negada</span>' : ''}</span>
                    <span class="form-check form-switch mb-0">
                        <input class="form-check-input release-status-${documentId(delivery)}" type="checkbox"
                            data-student-id="${escAttr(student.id)}" ${student.released && !isDenied(project, student) ? 'checked' : ''}
                            ${project.puede_revisar && !isDenied(project, student) ? '' : 'disabled'}>
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

function isDenied(project, student) {
    return (project.readiness?.students || []).some(entry => String(entry.id) === String(student.id) && entry.denial);
}

function documentId(delivery) {
    return Number(delivery?.document?.id || 0);
}

function renderThesisUploadBox() {
    return `
        <div class="document-row mb-3 bg-light">
            <h6><i class="bi bi-cloud-arrow-up"></i> Subir avance de tesis de 9no</h6>
            <p class="small text-muted mb-2">¿Haces residencia? Sube tus documentos en <a href="/pages/academic-workflows.php?tab=residencies">Seguimiento académico → Residencias</a>.</p>
            <div class="row g-2">
                <div class="col-md-3">
                    <!-- La API rechaza tipo=residencias (409); las residencias usan el expediente individual. -->
                    <select class="form-select form-select-sm" id="thesisTipo">
                        <option value="tesis">Tesis general</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input class="form-control form-control-sm" id="thesisName" maxlength="255" placeholder="Nombre del avance">
                </div>
                <div class="col-md-3">
                    <input class="form-control form-control-sm" id="thesisDesc" maxlength="5000" placeholder="Descripción breve">
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
    const canPublish = CAN_PUBLISH_REPOSITORY;

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
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-name="${esc(document.nombre)}" onclick="downloadRepositoryDocument(${Number(document.id)}, this.dataset.name)"><i class="bi bi-download"></i> Descargar</button>
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
    const delivery = deliveryFor(project, type);
    const allowed = delivery?.allowed_extensions || [];
    const maxMb = Number(delivery?.max_mb || window.SGPI_SETTINGS?.max_file_size_mb || 50);

    if (!file) {
        showAlert('#alertContainer', 'warning', 'Selecciona un archivo antes de guardar.');
        return;
    }

    const extension = file.name.split('.').pop().toLowerCase();
    if (!allowed.includes(extension)) {
        showAlert('#alertContainer', 'danger', `Formato no permitido. Usa: ${allowed.join(', ').toUpperCase()}`);
        return;
    }

    if (file.size > maxMb * 1024 * 1024) {
        showAlert('#alertContainer', 'danger', `El archivo pesa ${(file.size / 1048576).toFixed(1)} MB; el máximo es ${maxMb} MB.`);
        return;
    }

    const formData = new FormData();
    formData.append('project_id', projectId);
    formData.append('document_type', type);
    const names = { release_sheet: 'Hoja de liberación', presentation: 'Presentación', report: 'Documento de reporte' };
    formData.append('nombre', names[type]);
    formData.append('descripcion', `Entrega de evaluación: ${names[type].toLowerCase()}.`);
    formData.append('autores', memberNames(project?.integrantes || []));
    formData.append('archivo', file);

    try {
        // Una presentación de hasta 100 MB puede tardar en subir.
        await api.post('/repositorio/evaluation-documents', formData, { _timeout: 600000 });
        showAlert('#alertContainer', 'success', 'Entrega guardada correctamente.');
        await loadDocuments(true);
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'Error al guardar el documento.');
    }
}

async function saveEvaluationLink(projectId) {
    const url = document.getElementById(`link-${projectId}`)?.value.trim() || '';
    if (!/^https:\/\/(drive|docs)\.google\.com\//i.test(url)) {
        showAlert('#alertContainer', 'warning', 'Pega un enlace de Google Drive que empiece con https://drive.google.com o https://docs.google.com.');
        return;
    }
    try {
        await api.post('/repositorio/evaluation-documents/link', { project_id: projectId, url });
        showAlert('#alertContainer', 'success', 'Enlace guardado.');
        await loadDocuments(true);
    } catch (error) {
        showAlert('#alertContainer', 'danger', error.message || 'No se pudo guardar el enlace.');
    }
}

async function saveReleaseReview(documentId) {
    const students = [...document.querySelectorAll(`.release-status-${documentId}`)].map(input => ({
        student_id: input.dataset.studentId,
        released: input.checked && !input.disabled
    }));

    try {
        await api.put(`/repositorio/evaluation-documents/${documentId}/release-status`, { students });
        showAlert('#alertContainer', 'success', 'Liberación actualizada.');
        await loadDocuments(true);
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
        showAlert('#alertContainer', 'danger', `Archivo muy grande. Máximo ${Number(window.SGPI_SETTINGS?.max_file_size_mb || 50)}MB.`);
        return;
    }

    const currentUser = auth.getCurrentUser() || {};
    const formData = new FormData();
    formData.append('tipo', document.getElementById('thesisTipo').value);
    formData.append('nombre', document.getElementById('thesisName').value.trim() || 'Avance de tesis o residencias');
    formData.append('descripcion', document.getElementById('thesisDesc').value.trim() || 'Avance privado para revisión.');
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
        if (!response.ok) {
            const payload = await response.json().catch(() => ({}));
            throw new Error(payload.error || payload.message || 'No se pudo descargar el documento.');
        }
        saveBlob(await response.blob(), downloadFilename(response, name || 'documento'));
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
            ['release_sheet', 'presentation', 'report'].forEach(type => {
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
