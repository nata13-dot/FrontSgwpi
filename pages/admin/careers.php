<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

if (!is_authenticated() || !is_general_admin()) {
    header('Location: ' . dashboard_url());
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carreras y accesos - <?= APP_NAME ?></title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
<div class="d-flex content-wrapper">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
    <main class="main-content flex-grow-1">
        <div class="container-xl py-4 py-lg-5">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <span class="text-uppercase small fw-bold text-muted">Administración institucional</span>
                    <h1 class="h2 mb-2">Carreras y accesos</h1>
                    <p class="text-muted mb-0">Elige una carrera para ver cómo va, cambiar su identidad o su personal. Para cargar datos dentro de ella, «Entrar».</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#newCareerModal"><i class="bi bi-plus-lg"></i> Nueva carrera</button>
                    <button class="btn btn-outline-primary" type="button" onclick="downloadInstitutionalSummary()"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar resumen</button>
                </div>
            </div>

            <div id="careerAlert"></div>
            <section class="career-admin-grid mb-4" id="careerCards" aria-label="Carreras">
                <div class="career-admin-loading">Cargando carreras...</div>
            </section>

            <section class="card border-0 shadow-sm career-sheet d-none" id="careerSheet">
                <header class="career-sheet-head" id="careerSheetHead"></header>
                <ul class="nav nav-tabs career-sheet-tabs px-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" type="button" data-career-tab="summary"><i class="bi bi-speedometer2"></i> Resumen</button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-career-tab="identity"><i class="bi bi-palette"></i> Identidad</button></li>
                    <li class="nav-item"><button class="nav-link" type="button" data-career-tab="people"><i class="bi bi-people"></i> Personal <span class="badge rounded-pill text-bg-light" id="peopleCount"></span></button></li>
                </ul>
                <div class="card-body p-4">
                    <div data-career-panel="summary" id="careerSummary"></div>

                    <div data-career-panel="identity" class="d-none">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <form id="careerIdentityForm" data-preview="identityPreview">
                                    <input type="hidden" id="identityCareerId">
                                    <div class="row g-3">
                                        <div class="col-sm-7"><label class="form-label" for="careerName">Nombre</label><input class="form-control" id="careerName" required maxlength="180"></div>
                                        <div class="col-sm-5"><label class="form-label" for="careerShortName">Nombre corto</label><input class="form-control" id="careerShortName" required maxlength="100"></div>
                                        <div class="col-4"><label class="form-label" for="careerPrimary">Principal</label><input class="form-control form-control-color w-100" type="color" id="careerPrimary"></div>
                                        <div class="col-4"><label class="form-label" for="careerSecondary">Secundario</label><input class="form-control form-control-color w-100" type="color" id="careerSecondary"></div>
                                        <div class="col-4"><label class="form-label" for="careerAccent">Acento</label><input class="form-control form-control-color w-100" type="color" id="careerAccent"></div>
                                        <div class="col-12"><label class="form-label" for="careerMotto">Lema</label><input class="form-control" id="careerMotto" maxlength="255"></div>
                                        <div class="col-12">
                                            <label class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" id="careerActive">
                                                <span class="form-check-label">Carrera activa <small class="d-block text-muted">Desactivada no aparece para entrar ni para el registro de alumnos.</small></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Guardar cambios</button>
                                        <button class="btn btn-outline-secondary" type="button" id="identityReset">Descartar</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-6">
                                <div class="small text-muted mb-2">Vista previa</div>
                                <div class="career-preview mb-3" id="identityPreview" aria-label="Vista previa"></div>
                                <div class="career-image-slots">
                                    <?php foreach (['logo' => 'Logo', 'portada' => 'Portada'] as $tipo => $label): ?>
                                        <div class="career-image-slot" data-image-slot="<?= $tipo ?>">
                                            <div class="career-image-thumb <?= $tipo ?>"><span class="text-muted small">Sin <?= strtolower($label) ?></span></div>
                                            <div class="d-flex gap-1 mt-2">
                                                <label class="btn btn-sm btn-outline-primary flex-grow-1 mb-0"><i class="bi bi-upload"></i> <?= $label ?>
                                                    <input type="file" accept="image/jpeg,image/png,image/webp" class="d-none" data-image-upload="<?= $tipo ?>">
                                                </label>
                                                <button type="button" class="btn btn-sm btn-outline-danger d-none" data-image-remove="<?= $tipo ?>" title="Quitar <?= strtolower($label) ?>"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="small text-muted mt-2 mb-0">Sin portada propia se usa la foto del instituto con el color de la carrera. JPG, PNG o WebP de hasta 4 MB.</p>
                            </div>
                        </div>
                    </div>

                    <div data-career-panel="people" class="d-none">
                        <form class="people-add" id="membershipForm" autocomplete="off">
                            <div class="people-add-search">
                                <label class="form-label" for="candidateSearch">Agregar persona</label>
                                <div class="position-relative">
                                    <i class="bi bi-search people-add-icon"></i>
                                    <input class="form-control ps-5" id="candidateSearch" placeholder="Escribe nombre o número de empleado…" maxlength="100">
                                    <div class="people-suggest d-none" id="candidateList" role="listbox"></div>
                                </div>
                                <div class="people-picked d-none" id="candidatePicked"></div>
                            </div>
                            <div>
                                <label class="form-label" for="membershipRole">Como</label>
                                <select class="form-select" id="membershipRole">
                                    <option value="2">Docente</option>
                                    <option value="7">Coordinador de proyectos integradores</option>
                                    <option value="6">Asistente de jefatura</option>
                                    <option value="5">Jefe de carrera</option>
                                </select>
                            </div>
                            <button class="btn btn-primary" type="submit" id="membershipSubmit" disabled><i class="bi bi-person-plus"></i> Agregar</button>
                        </form>
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 mb-2">
                            <div class="people-filters" id="peopleFilters"></div>
                            <input class="form-control form-control-sm people-filter-input" id="membershipSearch" placeholder="Filtrar personal…">
                        </div>
                        <div id="membershipRows" class="people-list"><div class="text-muted py-4 text-center">Cargando…</div></div>
                        <p class="small text-muted mt-3 mb-0"><i class="bi bi-info-circle"></i> Aquí sólo aparece el personal. Los alumnos se gestionan dentro de la carrera, en Personas › Usuarios.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>

<div class="modal fade" id="newCareerModal" tabindex="-1" aria-labelledby="newCareerTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" id="newCareerForm" data-preview="newCareerPreview">
            <div class="modal-header">
                <h2 class="modal-title h5" id="newCareerTitle"><i class="bi bi-plus-circle"></i> Nueva carrera</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="career-preview mb-3" id="newCareerPreview" aria-label="Vista previa"></div>
                <div id="newCareerAlert"></div>
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label" for="newClave">Clave</label>
                        <input class="form-control text-uppercase" id="newClave" name="clave" required maxlength="20" pattern="[A-Za-z0-9]+" placeholder="Ej. IIA">
                        <small class="text-muted">Sólo letras y números.</small>
                    </div>
                    <div class="col-sm-8">
                        <label class="form-label" for="newNombre">Nombre</label>
                        <input class="form-control" id="newNombre" name="nombre" required maxlength="180" placeholder="Ingeniería en …">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="newNombreCorto">Nombre corto</label>
                        <input class="form-control" id="newNombreCorto" name="nombre_corto" required maxlength="100">
                    </div>
                    <div class="col-4 col-sm-2">
                        <label class="form-label" for="newPrimary">Principal</label>
                        <input class="form-control form-control-color w-100" type="color" id="newPrimary" name="color_primario" value="#1B396A">
                    </div>
                    <div class="col-4 col-sm-2">
                        <label class="form-label" for="newSecondary">Secundario</label>
                        <input class="form-control form-control-color w-100" type="color" id="newSecondary" name="color_secundario" value="#2D5A96">
                    </div>
                    <div class="col-4 col-sm-2">
                        <label class="form-label" for="newAccent">Acento</label>
                        <input class="form-control form-control-color w-100" type="color" id="newAccent" name="color_acento" value="#00A6D6">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="newLema">Lema</label>
                        <input class="form-control" id="newLema" name="lema" maxlength="255">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="newJefe">Jefatura (No. de empleado)</label>
                        <input class="form-control" id="newJefe" name="jefe_id" maxlength="20" placeholder="Opcional">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="newAsistente">Asistencia de jefatura (No. de empleado)</label>
                        <input class="form-control" id="newAsistente" name="asistente_id" maxlength="20" placeholder="Opcional">
                    </div>
                    <div class="col-12">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="newActiva" name="activa" checked>
                            <span class="form-check-label">Activa desde ahora</span>
                        </label>
                    </div>
                </div>
                <p class="small text-muted mt-3 mb-0">Se crea con sus módulos habilitados y sin datos. Después entra a la carrera para cargar asignaturas, grupos y alumnos; el logo y la portada se suben desde «Identidad de carrera».</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Crear carrera</button>
            </div>
        </form>
    </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script>
const CAREER_STORAGE_URL = <?= json_encode(API_ORIGIN_URL . '/storage/') ?>;
const DEFAULT_COVER = '/assets/img/ITSSMT/fondochido2.webp';
const ROLE_NAMES = {5: 'Jefe de carrera', 6: 'Asistente de jefatura', 7: 'Coordinación de proyectos', 1: 'Administrador', 2: 'Docente', 3: 'Estudiante'};
const STAFF_ROLES = [5, 6, 7, 2];
const READINESS = {
    identity: ['Identidad', 'Nombre y colores', 'identity'],
    career_management: ['Jefatura', 'Asigna un jefe de carrera', 'people'],
    subjects: ['Asignaturas', 'Entra y carga el plan de estudios', 'enter'],
    groups: ['Grupos', 'Entra y crea los grupos', 'enter'],
    students: ['Estudiantes', 'Entra y da de alta o abre el autorregistro', 'enter'],
    configuration: ['Configuración', 'Entra y revisa los ajustes', 'enter'],
    rubrics: ['Rúbricas', 'Entra y crea las rúbricas de evaluación', 'enter'],
};
const careerState = { careers: [], selected: null, tab: 'summary', roleFilter: 'all', members: [], picked: null };
const remember = (key, value) => { try { value === undefined ? localStorage.removeItem(key) : localStorage.setItem(key, value); } catch (e) { /* opcional */ } };
const recall = (key) => { try { return localStorage.getItem(key); } catch (e) { return null; } };

function careerEsc(value) {
    return String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
}
function careerMessage(type, message) {
    const box = document.getElementById('careerAlert');
    box.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">${careerEsc(message)}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>`;
    if (type === 'success') setTimeout(() => { if (box.firstElementChild) box.innerHTML = ''; }, 4000);
}
function careerImage(path) {
    if (!path) return null;
    return /^(https?:)?\/\//.test(path) || path.startsWith('/') ? path : CAREER_STORAGE_URL + path.replace(/^\/+/, '');
}
function personName(user) {
    return [user?.nombres, user?.apellido_paterno, user?.apellido_materno].filter(Boolean).join(' ') || user?.id || '';
}

// ---------- Tarjetas de carreras ----------
function renderCareerCards() {
    const cards = careerState.careers.map(career => `
        <button type="button" class="career-admin-card ${careerState.selected?.id === career.id ? 'active' : ''} ${career.activa ? '' : 'inactive'}" data-id="${career.id}" style="--card-primary:${career.color_primario};--card-secondary:${career.color_secundario};--card-accent:${career.color_acento || career.color_primario}">
            <span class="career-admin-code">${careerEsc(career.clave)}${career.activa ? '' : ' · Inactiva'}</span>
            <strong>${careerEsc(career.nombre_corto)}</strong>
            <span class="career-admin-meter"><span style="width:${Number(career.readiness?.percentage || 0)}%"></span></span>
            <small>${Number(career.readiness?.percentage || 0)}% lista · ${Number(career.members_count || 0)} personas</small>
        </button>`).join('');
    document.getElementById('careerCards').innerHTML = cards + `
        <button type="button" class="career-admin-card career-admin-new" data-bs-toggle="modal" data-bs-target="#newCareerModal">
            <i class="bi bi-plus-lg"></i><strong>Nueva carrera</strong><small>Clave, nombre y colores</small>
        </button>`;
    document.querySelectorAll('.career-admin-card[data-id]').forEach(card => card.addEventListener('click', () => selectCareer(Number(card.dataset.id), true)));
}

function selectCareer(id, scroll = false) {
    const career = careerState.careers.find(item => Number(item.id) === Number(id));
    if (!career) return;
    const changed = careerState.selected?.id !== career.id;
    careerState.selected = career;
    remember('sgpi-careers-selected', String(career.id));
    renderCareerCards();
    document.getElementById('careerSheet').classList.remove('d-none');
    renderSheetHead();
    renderSummary();
    fillIdentity();
    if (changed) { careerState.roleFilter = 'all'; document.getElementById('membershipSearch').value = ''; clearPicked(); loadMemberships(); }
    showTab(careerState.tab);
    if (scroll) document.getElementById('careerSheet').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function renderSheetHead() {
    const career = careerState.selected;
    const logo = careerImage(career.logo_ruta);
    document.getElementById('careerSheet').style.setProperty('--sheet-color', career.color_primario || '#1B396A');
    document.getElementById('careerSheetHead').innerHTML = `
        <div class="career-sheet-id">
            ${logo ? `<img src="${careerEsc(logo)}" alt="" class="career-sheet-logo">` : `<span class="career-sheet-code">${careerEsc(career.clave)}</span>`}
            <div>
                <h2 class="h5 mb-0">${careerEsc(career.nombre)}</h2>
                <small class="text-muted">${careerEsc(career.clave)} · ${career.activa ? '<span class="text-success">Activa</span>' : '<span class="text-secondary">Inactiva</span>'}${career.period?.nombre ? ` · Periodo ${careerEsc(career.period.nombre)}` : ' · Sin periodo abierto'}</small>
            </div>
        </div>
        ${career.activa ? `<button class="btn btn-primary" type="button" data-enter="${career.id}"><i class="bi bi-box-arrow-in-right"></i> Entrar a la carrera</button>`
            : `<button class="btn btn-outline-success" type="button" data-activate="${career.id}"><i class="bi bi-power"></i> Activar</button>`}`;
    document.querySelector('[data-enter]')?.addEventListener('click', () => enterCareer(career.id));
    document.querySelector('[data-activate]')?.addEventListener('click', () => saveIdentity({ activa: true }, 'Carrera activada.'));
}

function enterCareer(careerId) {
    // Entrar abre el inicio de la carrera; desde ahí se modifica todo como su jefatura.
    window.SGPICareerSwitch(careerId, '/pages/admin/dashboard.php');
}

// ---------- Pestañas ----------
function showTab(tab) {
    careerState.tab = tab;
    remember('sgpi-careers-tab', tab);
    document.querySelectorAll('[data-career-tab]').forEach(button => button.classList.toggle('active', button.dataset.careerTab === tab));
    document.querySelectorAll('[data-career-panel]').forEach(panel => panel.classList.toggle('d-none', panel.dataset.careerPanel !== tab));
}
document.querySelectorAll('[data-career-tab]').forEach(button => button.addEventListener('click', () => showTab(button.dataset.careerTab)));

// ---------- Resumen ----------
function renderSummary() {
    const career = careerState.selected;
    const roles = career.role_counts || {};
    const readiness = career.readiness || {};
    const checklist = readiness.checklist || {};
    const stats = [
        ['bi-person-badge', roles.career_heads || 0, 'Jefatura', 'people'],
        ['bi-person-gear', (roles.career_head_assistants || 0) + (roles.project_coordinators || 0), 'Asistencia y coordinación', 'people'],
        ['bi-person-video3', roles.teachers || 0, 'Docentes', 'people'],
        ['bi-mortarboard', roles.students || 0, 'Estudiantes'],
        ['bi-journal-bookmark', career.subjects_count || 0, 'Asignaturas'],
        ['bi-collection', career.groups_count || 0, 'Grupos'],
        ['bi-diagram-3', career.projects_count || 0, 'Proyectos'],
        ['bi-clipboard-check', career.evaluations_count || 0, 'Evaluaciones'],
    ];
    const pending = Object.entries(READINESS).filter(([key]) => !checklist[key]);
    document.getElementById('careerSummary').innerHTML = `
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="career-stats">${stats.map(([icon, value, label, tab]) => `
                    <${tab ? `button type="button" data-goto="${tab}"` : 'div'} class="career-stat"><i class="bi ${icon}"></i><strong>${Number(value)}</strong><span>${label}</span></${tab ? 'button' : 'div'}>`).join('')}
                </div>
            </div>
            <div class="col-lg-5">
                <div class="career-ready">
                    <div class="d-flex justify-content-between align-items-baseline mb-1"><strong>Puesta en marcha</strong><span class="fw-bold">${Number(readiness.percentage || 0)}%</span></div>
                    <div class="progress mb-3" style="height:8px"><div class="progress-bar ${readiness.percentage >= 100 ? 'bg-success' : ''}" style="width:${Number(readiness.percentage || 0)}%"></div></div>
                    <ul class="career-ready-list">${Object.entries(READINESS).map(([key, [label, hint, action]]) => `
                        <li class="${checklist[key] ? 'done' : ''}">
                            <i class="bi ${checklist[key] ? 'bi-check-circle-fill' : 'bi-circle'}"></i>
                            <span><strong>${label}</strong>${checklist[key] ? '' : `<small>${hint}</small>`}</span>
                            ${checklist[key] ? '' : (action === 'enter'
                                ? (career.activa ? `<button type="button" class="btn btn-sm btn-link" data-enter-from-summary>Entrar</button>` : '')
                                : `<button type="button" class="btn btn-sm btn-link" data-goto="${action}">Ir</button>`)}
                        </li>`).join('')}
                    </ul>
                    ${pending.length ? '' : '<p class="small text-success mb-0"><i class="bi bi-check2-all"></i> Lista para operar.</p>'}
                </div>
            </div>
        </div>`;
    document.querySelectorAll('#careerSummary [data-goto]').forEach(button => button.addEventListener('click', () => showTab(button.dataset.goto)));
    document.querySelectorAll('#careerSummary [data-enter-from-summary]').forEach(button => button.addEventListener('click', () => enterCareer(career.id)));
}

// ---------- Identidad ----------
function fillIdentity() {
    const career = careerState.selected;
    document.getElementById('identityCareerId').value = career.id;
    document.getElementById('careerName').value = career.nombre || '';
    document.getElementById('careerShortName').value = career.nombre_corto || '';
    document.getElementById('careerPrimary').value = career.color_primario || '#1B396A';
    document.getElementById('careerSecondary').value = career.color_secundario || '#2D5A96';
    document.getElementById('careerAccent').value = career.color_acento || career.color_primario || '#00A6D6';
    document.getElementById('careerMotto').value = career.lema || '';
    document.getElementById('careerActive').checked = Boolean(career.activa);
    renderPreview(document.getElementById('careerIdentityForm'));
    renderImageSlots();
}

// Vista previa en vivo: la portada (propia o la del instituto) con el tono de la carrera, como en los tableros.
function renderPreview(form) {
    const target = document.getElementById(form.dataset.preview);
    if (!target) return;
    const value = (id) => form.querySelector(id)?.value || '';
    const isNew = form.id === 'newCareerForm';
    const data = isNew
        ? { clave: value('#newClave').toUpperCase(), nombre: value('#newNombre'), corto: value('#newNombreCorto'), p: value('#newPrimary'), s: value('#newSecondary'), a: value('#newAccent'), lema: value('#newLema'), cover: null, logo: null }
        : { clave: careerState.selected?.clave || '', nombre: value('#careerName'), corto: value('#careerShortName'), p: value('#careerPrimary'), s: value('#careerSecondary'), a: value('#careerAccent'), lema: value('#careerMotto'),
            cover: careerImage(careerState.selected?.portada_ruta), logo: careerImage(careerState.selected?.logo_ruta) };
    target.style.setProperty('--preview-primary', data.p || '#1B396A');
    target.style.setProperty('--preview-secondary', data.s || '#2D5A96');
    target.style.setProperty('--preview-accent', data.a || data.p || '#00A6D6');
    target.style.backgroundImage = `url("${data.cover || DEFAULT_COVER}")`;
    target.innerHTML = `<div class="career-preview-overlay"></div>
        <div class="career-preview-body">
            ${data.logo ? `<img class="career-preview-logo" src="${careerEsc(data.logo)}" alt="">` : ''}
            <span class="career-admin-code">${careerEsc(data.clave || 'CLAVE')}</span>
            <strong>${careerEsc(data.corto || 'Nombre corto')}</strong>
            <small>${careerEsc(data.nombre || 'Nombre de la carrera')}</small>
            ${data.lema ? `<em>${careerEsc(data.lema)}</em>` : ''}
            <span class="career-preview-chip">Botón de muestra</span>
        </div>`;
}

function renderImageSlots() {
    const career = careerState.selected;
    document.querySelectorAll('[data-image-slot]').forEach(slot => {
        const tipo = slot.dataset.imageSlot;
        const url = careerImage(career?.[tipo === 'logo' ? 'logo_ruta' : 'portada_ruta']);
        slot.querySelector('.career-image-thumb').innerHTML = url ? `<img src="${careerEsc(url)}" alt="">` : `<span class="text-muted small">Sin ${tipo}</span>`;
        slot.querySelector('[data-image-remove]').classList.toggle('d-none', !url);
    });
}

// Vuelve a leer la sesión (lista de carreras, colores) para que el menú y el selector muestren los cambios.
async function refreshSessionCareers() {
    try {
        await fetch('/api/set-session.php', {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ auth_token: window.SGPI_SESSION?.token, remember: Boolean(window.SGPI_SESSION?.remember) })
        });
    } catch (error) { /* el menú se actualiza en el siguiente inicio de sesión */ }
}

async function saveIdentity(changes, message) {
    try {
        await api.put(`/admin/careers/${careerState.selected.id}`, changes);
        careerMessage('success', message);
        await loadCareers();
        refreshSessionCareers();
    } catch (error) {
        careerMessage('danger', error.message);
    }
}

document.getElementById('careerIdentityForm').addEventListener('submit', event => {
    event.preventDefault();
    saveIdentity({
        nombre: document.getElementById('careerName').value.trim(),
        nombre_corto: document.getElementById('careerShortName').value.trim(),
        color_primario: document.getElementById('careerPrimary').value,
        color_secundario: document.getElementById('careerSecondary').value,
        color_acento: document.getElementById('careerAccent').value,
        lema: document.getElementById('careerMotto').value.trim(),
        activa: document.getElementById('careerActive').checked
    }, 'Identidad guardada.');
});
document.getElementById('identityReset').addEventListener('click', fillIdentity);

// ---------- Personal ----------
async function loadMemberships() {
    if (!careerState.selected) return;
    const careerId = careerState.selected.id;
    document.getElementById('membershipRows').innerHTML = '<div class="text-muted py-4 text-center">Cargando…</div>';
    try {
        const response = await api.get('/admin/career-memberships', { carrera_id: careerId, per_page: 100, _fresh: true });
        if (careerState.selected?.id !== careerId) return;
        careerState.members = response.data || [];
        renderMembers();
    } catch (error) {
        document.getElementById('membershipRows').innerHTML = `<div class="text-danger py-4">${careerEsc(error.message)}</div>`;
    }
}

function renderMembers() {
    const term = document.getElementById('membershipSearch').value.trim().toLowerCase();
    const counts = { all: careerState.members.length };
    careerState.members.forEach(item => { counts[item.perfil_id] = (counts[item.perfil_id] || 0) + 1; });
    document.getElementById('peopleCount').textContent = counts.all || '';
    document.getElementById('peopleFilters').innerHTML = [['all', 'Todos'], ...STAFF_ROLES.map(id => [id, ROLE_NAMES[id]])]
        .filter(([key]) => key === 'all' || counts[key])
        .map(([key, label]) => `<button type="button" class="btn btn-sm ${String(careerState.roleFilter) === String(key) ? 'btn-primary' : 'btn-outline-secondary'}" data-role-filter="${key}">${label} <span class="opacity-75">${counts[key] || 0}</span></button>`).join('');
    document.querySelectorAll('[data-role-filter]').forEach(button => button.addEventListener('click', () => { careerState.roleFilter = button.dataset.roleFilter; renderMembers(); }));

    const rows = careerState.members.filter(item => {
        if (careerState.roleFilter !== 'all' && String(item.perfil_id) !== String(careerState.roleFilter)) return false;
        return !term || `${personName(item.user)} ${item.user?.id}`.toLowerCase().includes(term);
    });
    const options = (selected) => STAFF_ROLES.concat(STAFF_ROLES.includes(Number(selected)) ? [] : [Number(selected)])
        .map(id => `<option value="${id}" ${Number(selected) === id ? 'selected' : ''}>${ROLE_NAMES[id] || 'Otro'}</option>`).join('');
    document.getElementById('membershipRows').innerHTML = rows.length ? rows.map(item => `
        <div class="people-row ${item.activo ? '' : 'inactive'}">
            <span class="people-avatar">${careerEsc(personName(item.user).split(' ').slice(0, 2).map(w => w[0]).join(''))}</span>
            <div class="people-name"><strong>${careerEsc(personName(item.user))}</strong><small>${careerEsc(item.user?.id)}${item.es_principal ? ' · Carrera principal' : ''}${item.activo ? '' : ' · Sin acceso'}</small></div>
            <select class="form-select form-select-sm people-role" data-role-change="${item.id}" aria-label="Rol">${options(item.perfil_id)}</select>
            <div class="form-check form-switch m-0" title="${item.activo ? 'Con acceso' : 'Sin acceso'}">
                <input class="form-check-input" type="checkbox" role="switch" data-toggle-membership="${item.id}" ${item.activo ? 'checked' : ''} aria-label="Acceso">
            </div>
            <button class="btn btn-sm btn-link text-danger" data-delete-membership="${item.id}" title="Quitar de la carrera"><i class="bi bi-x-lg"></i></button>
        </div>`).join('')
        : `<div class="text-muted py-4 text-center">${careerState.members.length ? 'Nadie coincide con el filtro.' : 'Esta carrera aún no tiene personal. Búscalo arriba para agregarlo.'}</div>`;

    document.querySelectorAll('[data-role-change]').forEach(select => select.addEventListener('change', async () => {
        const item = careerState.members.find(m => String(m.id) === select.dataset.roleChange);
        try {
            await api.put(`/admin/career-memberships/${item.id}`, { perfil_id: Number(select.value) });
            careerMessage('success', `${personName(item.user)} ahora es ${ROLE_NAMES[select.value]}.`);
            loadMemberships(); loadCareers();
        } catch (error) { select.value = item.perfil_id; careerMessage('danger', error.message); }
    }));
    document.querySelectorAll('[data-toggle-membership]').forEach(input => input.addEventListener('change', async () => {
        try { await api.put(`/admin/career-memberships/${input.dataset.toggleMembership}`, { activo: input.checked }); loadMemberships(); }
        catch (error) { input.checked = !input.checked; careerMessage('danger', error.message); }
    }));
    document.querySelectorAll('[data-delete-membership]').forEach(button => button.addEventListener('click', async () => {
        const item = careerState.members.find(m => String(m.id) === button.dataset.deleteMembership);
        const ok = window.Swal
            ? (await Swal.fire({ icon: 'warning', title: 'Quitar de la carrera', text: `${personName(item.user)} perderá el acceso a ${careerState.selected.nombre_corto}.`, showCancelButton: true, confirmButtonText: 'Quitar', cancelButtonText: 'Cancelar', confirmButtonColor: '#b23b3b' })).isConfirmed
            : confirm('¿Quitar a esta persona de la carrera?');
        if (!ok) return;
        try { await api.delete(`/admin/career-memberships/${item.id}`); careerMessage('success', 'Persona quitada de la carrera.'); loadMemberships(); loadCareers(); }
        catch (error) { careerMessage('danger', error.message); }
    }));
}
document.getElementById('membershipSearch').addEventListener('input', renderMembers);

// Buscador de personal para agregar (no incluye alumnos).
let candidateTimer = null;
const candidateInput = document.getElementById('candidateSearch');
const candidateList = document.getElementById('candidateList');
function clearPicked() {
    careerState.picked = null;
    document.getElementById('candidatePicked').classList.add('d-none');
    candidateInput.closest('.position-relative').classList.remove('d-none');
    candidateInput.value = '';
    candidateList.classList.add('d-none');
    document.getElementById('membershipSubmit').disabled = true;
}
function pickCandidate(person) {
    careerState.picked = person;
    candidateList.classList.add('d-none');
    candidateInput.closest('.position-relative').classList.add('d-none');
    const picked = document.getElementById('candidatePicked');
    picked.innerHTML = `<span class="people-avatar">${careerEsc(person.nombre.split(' ').slice(0, 2).map(w => w[0]).join(''))}</span>
        <span><strong>${careerEsc(person.nombre)}</strong><small>${careerEsc(person.id)}</small></span>
        <button type="button" class="btn btn-sm btn-link" id="candidateChange">Cambiar</button>`;
    picked.classList.remove('d-none');
    document.getElementById('candidateChange').addEventListener('click', () => { clearPicked(); candidateInput.focus(); });
    document.getElementById('membershipRole').value = person.perfil_aqui && STAFF_ROLES.includes(person.perfil_aqui) ? String(person.perfil_aqui) : '2';
    document.getElementById('membershipSubmit').disabled = false;
}
candidateInput.addEventListener('input', () => {
    clearTimeout(candidateTimer);
    const term = candidateInput.value.trim();
    if (term.length < 2) { candidateList.classList.add('d-none'); return; }
    candidateTimer = setTimeout(async () => {
        try {
            const response = await api.get('/admin/career-candidates', { carrera_id: careerState.selected.id, search: term, _fresh: true });
            const people = response.data || [];
            candidateList.innerHTML = people.length ? people.map((person, index) => `
                <button type="button" class="people-suggest-item" data-index="${index}">
                    <strong>${careerEsc(person.nombre)}</strong>
                    <small>${careerEsc(person.id)}${person.carreras.length ? ' · ' + person.carreras.map(c => `${careerEsc(c.clave)}: ${careerEsc(ROLE_NAMES[c.perfil_id] || '')}`).join(', ') : ' · Sin carrera'}</small>
                    ${person.perfil_aqui ? '<span class="badge text-bg-light">Ya está en esta carrera</span>' : ''}
                </button>`).join('') : '<div class="people-suggest-empty">Sin coincidencias entre el personal. Si es nuevo, regístralo primero en Usuarios dentro de una carrera.</div>';
            candidateList.classList.remove('d-none');
            candidateList.querySelectorAll('[data-index]').forEach(button => button.addEventListener('click', () => pickCandidate(people[Number(button.dataset.index)])));
        } catch (error) {
            candidateList.innerHTML = `<div class="people-suggest-empty text-danger">${careerEsc(error.message)}</div>`;
            candidateList.classList.remove('d-none');
        }
    }, 250);
});
document.addEventListener('click', event => { if (!event.target.closest('.people-add-search')) candidateList.classList.add('d-none'); });

document.getElementById('membershipForm').addEventListener('submit', async event => {
    event.preventDefault();
    const person = careerState.picked;
    if (!person) return;
    const role = Number(document.getElementById('membershipRole').value);
    const button = document.getElementById('membershipSubmit');
    button.disabled = true;
    try {
        await api.post('/admin/career-memberships', { usuario_id: person.id, carrera_id: careerState.selected.id, perfil_id: role, activo: true });
        careerMessage('success', `${person.nombre} agregado como ${ROLE_NAMES[role]}.`);
        clearPicked();
        loadMemberships(); loadCareers();
    } catch (error) {
        button.disabled = false;
        careerMessage('danger', error.message);
    }
});

// ---------- Carga ----------
async function loadCareers() {
    try {
        const response = await api.get('/admin/careers', {_fresh: true});
        careerState.careers = response.careers || [];
        const preferred = careerState.selected?.id || Number(recall('sgpi-careers-selected')) || Number(window.SGPI_SESSION?.user?.active_career?.id) || careerState.careers[0]?.id;
        if (careerState.careers.some(c => Number(c.id) === Number(preferred))) selectCareer(Number(preferred));
        else if (careerState.careers[0]) selectCareer(careerState.careers[0].id);
        else renderCareerCards();
    } catch (error) {
        careerMessage('danger', error.message);
    }
}

async function downloadInstitutionalSummary() {
    try {
        const response = await fetch(`${window.SGPI_API_BASE_URL}/admin/careers/export`, {
            headers: {Authorization: `Bearer ${window.SGPI_SESSION?.token || ''}`, Accept: 'text/csv'}
        });
        if (!response.ok) throw new Error('No se pudo generar el resumen institucional.');
        saveBlob(await response.blob(), downloadFilename(response, `resumen_carreras_${new Date().toISOString().slice(0, 10)}.csv`));
    } catch (error) {
        careerMessage('danger', error.message);
    }
}

['careerIdentityForm', 'newCareerForm'].forEach(id => {
    const form = document.getElementById(id);
    form.addEventListener('input', () => renderPreview(form));
});
document.getElementById('newCareerModal').addEventListener('shown.bs.modal', () => {
    renderPreview(document.getElementById('newCareerForm'));
    document.getElementById('newClave').focus();
});
// El nombre corto se propone a partir del nombre mientras no se haya escrito uno.
document.getElementById('newNombre').addEventListener('input', event => {
    const short = document.getElementById('newNombreCorto');
    if (short.dataset.touched) return;
    short.value = event.target.value.replace(/^Ingenier[ií]a en /i, 'Ing. ').replace(/^Licenciatura en /i, 'Lic. ').slice(0, 100);
});
document.getElementById('newNombreCorto').addEventListener('input', event => { event.target.dataset.touched = '1'; });

document.getElementById('newCareerForm').addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.target;
    const button = form.querySelector('[type="submit"]');
    const field = (name) => form.elements[name].value.trim();
    const payload = {
        clave: field('clave').toUpperCase(), nombre: field('nombre'), nombre_corto: field('nombre_corto'),
        color_primario: field('color_primario'), color_secundario: field('color_secundario'), color_acento: field('color_acento'),
        lema: field('lema') || null, activa: form.elements.activa.checked,
        jefe_id: field('jefe_id') || null, asistente_id: field('asistente_id') || null
    };
    button.disabled = true;
    try {
        const response = await api.post('/admin/careers', payload);
        bootstrap.Modal.getInstance(document.getElementById('newCareerModal'))?.hide();
        form.reset();
        delete document.getElementById('newNombreCorto').dataset.touched;
        await refreshSessionCareers();
        remember('sgpi-careers-selected', String(response.career?.id || ''));
        remember('sgpi-careers-tab', 'summary');
        try { sessionStorage.setItem('sgpi-career-created', payload.clave); } catch (e) { /* opcional */ }
        window.location.reload();
    } catch (error) {
        document.getElementById('newCareerAlert').innerHTML = `<div class="alert alert-danger">${careerEsc(error.message)}</div>`;
    } finally {
        button.disabled = false;
    }
});

document.querySelectorAll('[data-image-upload]').forEach(input => input.addEventListener('change', async () => {
    const file = input.files[0];
    if (!file || !careerState.selected) return;
    if (file.size > 4 * 1024 * 1024) { careerMessage('danger', 'La imagen supera 4 MB.'); input.value = ''; return; }
    const body = new FormData();
    body.append('tipo', input.dataset.imageUpload);
    body.append('imagen', file);
    try {
        await api.post(`/admin/careers/${careerState.selected.id}/image`, body);
        careerMessage('success', input.dataset.imageUpload === 'logo' ? 'Logo actualizado.' : 'Portada actualizada.');
        await loadCareers();
    } catch (error) {
        careerMessage('danger', error.message);
    } finally {
        input.value = '';
    }
}));
document.querySelectorAll('[data-image-remove]').forEach(button => button.addEventListener('click', async () => {
    const tipo = button.dataset.imageRemove;
    if (!careerState.selected || !confirm(tipo === 'logo' ? '¿Quitar el logo?' : '¿Quitar la portada? Se usará la foto del instituto.')) return;
    try {
        await api.delete(`/admin/careers/${careerState.selected.id}/image/${tipo}`);
        await loadCareers();
    } catch (error) {
        careerMessage('danger', error.message);
    }
}));

document.addEventListener('DOMContentLoaded', () => {
    const tab = recall('sgpi-careers-tab');
    if (['summary', 'identity', 'people'].includes(tab)) careerState.tab = tab;
    try {
        const created = sessionStorage.getItem('sgpi-career-created');
        if (created) {
            sessionStorage.removeItem('sgpi-career-created');
            careerMessage('success', `Carrera ${created} creada. Agrega su personal aquí y entra a ella para cargar asignaturas, grupos y alumnos.`);
        }
    } catch (error) { /* sin almacenamiento no se muestra el aviso */ }
    loadCareers();
});
</script>
</body>
</html>
