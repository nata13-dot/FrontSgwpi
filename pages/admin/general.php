<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_general_admin()) {
    header('Location: ' . (is_authenticated() ? dashboard_url() : '/index.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel institucional - <?= APP_NAME ?></title>
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
            <section class="general-admin-hero mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <small class="text-uppercase fw-bold">Administración general</small>
                        <h1 class="h3 mb-1">Panel institucional</h1>
                        <p class="mb-0 opacity-75">Todas las carreras en un solo lugar. Desde aquí entras a administrar una carrera o la ves como la ve cada perfil, sin mezclar su información.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-light" onclick="openViewAs()"><i class="bi bi-layers"></i> Simular nivel…</button>
                        <a class="btn btn-outline-light" href="/pages/admin/careers.php"><i class="bi bi-diagram-3"></i> Carreras y accesos</a>
                    </div>
                </div>
            </section>

            <div id="generalAlert"></div>

            <section class="row g-3 mb-4" id="generalKpis" aria-label="Indicadores institucionales">
                <div class="col-6 col-lg-2"><div class="general-kpi"><strong id="kpiCareers">–</strong><span>Carreras activas</span></div></div>
                <div class="col-6 col-lg-2"><div class="general-kpi"><strong id="kpiPeople">–</strong><span>Membresías activas</span></div></div>
                <div class="col-6 col-lg-2"><div class="general-kpi" id="kpiPeriodsBox"><strong id="kpiPeriods">–</strong><span>Periodos abiertos</span></div></div>
                <div class="col-6 col-lg-2"><div class="general-kpi" id="kpiAlertsBox"><strong id="kpiAlerts">–</strong><span>Alertas abiertas</span></div></div>
                <div class="col-6 col-lg-2"><div class="general-kpi" id="kpiBackupsBox"><strong id="kpiBackups">–</strong><span>Respaldos</span></div></div>
                <div class="col-6 col-lg-2"><div class="general-kpi" id="kpiIntegrityBox"><strong id="kpiIntegrity">–</strong><span>Última integridad</span></div></div>
            </section>

            <section class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0"><i class="bi bi-buildings"></i> Carreras</h2>
                    <small class="text-muted">"Administrar" entra a la carrera con tu perfil de administrador general.</small>
                </div>
                <div class="row g-3" id="generalCareers"><div class="col-12 text-muted">Cargando carreras...</div></div>
            </section>

            <section>
                <h2 class="h5 mb-3"><i class="bi bi-lightning-charge"></i> Gestiones institucionales</h2>
                <div class="row g-3">
                    <div class="col-md-6 col-xl-4"><a class="general-quick-link" href="/pages/admin/careers.php"><i class="bi bi-diagram-3"></i><span>Carreras y accesos<small>Identidad, membresías y jefaturas</small></span></a></div>
                    <div class="col-md-6 col-xl-4"><a class="general-quick-link" href="/pages/academic-workflows.php"><i class="bi bi-calendar3"></i><span>Calendario institucional<small>Periodos, conciliación y salones</small></span></a></div>
                    <div class="col-md-6 col-xl-4"><a class="general-quick-link" href="/pages/admin/operations.php"><i class="bi bi-activity"></i><span>Operaciones<small>Alertas y continuidad</small></span></a></div>
                    <div class="col-md-6 col-xl-4"><a class="general-quick-link" href="/pages/admin/career-audit.php"><i class="bi bi-shield-lock"></i><span>Auditoría<small>Quién cambió qué, por carrera</small></span></a></div>
                    <div class="col-md-6 col-xl-4"><a class="general-quick-link" href="/pages/admin/career-integrity.php"><i class="bi bi-shield-check"></i><span>Integridad<small>Verificaciones entre carreras</small></span></a></div>
                    <div class="col-md-6 col-xl-4"><a class="general-quick-link" href="/pages/admin/database-backups.php"><i class="bi bi-database-check"></i><span>Respaldos<small>Copias y verificación</small></span></a></div>
                </div>
            </section>
        </div>
    </main>
</div>

<div class="modal fade" id="viewAsModal" tabindex="-1" aria-labelledby="viewAsTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewAsTitle"><i class="bi bi-layers"></i> Simular un nivel de perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Revisa la carrera con los permisos de otro nivel, usando tu propia cuenta y en <strong>sólo lectura</strong>. No se entra a la cuenta de ninguna persona: las secciones personales (mis proyectos, mis entregas) aparecen vacías.</p>
                <div id="viewAsAlert"></div>
                <label class="form-label" for="viewAsCareer">Carrera</label>
                <select class="form-select mb-3" id="viewAsCareer"></select>
                <span class="form-label d-block">Nivel</span>
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action" onclick="startViewAs(5, this)"><strong>2 · Jefe de carrera</strong></button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="startViewAs(6, this)"><strong>3 · Asistente de jefatura</strong></button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="startViewAs(7, this)"><strong>4 · Coordinador de proyectos integradores</strong></button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="startViewAs(2, this)"><strong>5 · Docente</strong></button>
                    <button type="button" class="list-group-item list-group-item-action" onclick="startViewAs(3, this)"><strong>6 · Estudiante</strong></button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script>
const generalState = { careers: [] };
const PERIOD_STATES = { abierto: ['success', 'Abierto'], en_cierre: ['warning', 'En cierre'], preparacion: ['secondary', 'En preparación'] };
let viewAsModal = null;

function esc(value) {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function setKpi(id, value, warning = false) {
    document.getElementById(id).textContent = value;
    document.getElementById(`${id}Box`)?.classList.toggle('is-warning', warning);
}

function renderCareers() {
    const container = document.getElementById('generalCareers');
    container.innerHTML = generalState.careers.map(career => {
        const period = career.period;
        const [periodClass, periodLabel] = PERIOD_STATES[period?.estado] || ['light text-dark border', 'Sin periodo'];
        const roles = career.role_counts || {};
        const readiness = Number(career.readiness?.percentage || 0);
        return `
        <div class="col-md-6 col-xl-3">
            <article class="general-career-card d-flex flex-column gap-2" style="--card-primary:${esc(career.color_primario || '#1B396A')}">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <strong class="d-block">${esc(career.nombre_corto)}</strong>
                        <small class="text-muted">${esc(career.clave)}</small>
                    </div>
                    <span class="badge text-bg-${periodClass}" title="Periodo de la carrera">${esc(periodLabel)}${period ? ` · ${esc(period.nombre)}` : ''}</span>
                </div>
                <small class="text-muted">${Number(roles.students || 0)} alumnos · ${Number(roles.teachers || 0)} docentes · ${Number(career.projects_count || 0)} proyectos</small>
                <div>
                    <div class="d-flex justify-content-between small"><span>Preparación</span><span>${readiness}%</span></div>
                    <div class="progress" style="height:6px"><div class="progress-bar" style="width:${readiness}%"></div></div>
                </div>
                <div class="form-check form-switch small">
                    <input class="form-check-input" type="checkbox" role="switch" id="basic${career.id}" ${career.basic_semesters_enabled ? 'checked' : ''} onchange="toggleBasicSemesters(${career.id}, this)">
                    <label class="form-check-label" for="basic${career.id}">Registrar 1° a 4° (casos integradores)</label>
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button type="button" class="btn btn-sm btn-primary flex-grow-1" onclick="SGPICareerSwitch(${career.id})"><i class="bi bi-box-arrow-in-right"></i> Administrar</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openViewAs(${career.id})" title="Simular un nivel en esta carrera"><i class="bi bi-layers"></i></button>
                </div>
            </article>
        </div>`;
    }).join('') || '<div class="col-12 text-muted">No hay carreras registradas.</div>';
}

async function toggleBasicSemesters(careerId, input) {
    input.disabled = true;
    try {
        await api.put(`/admin/careers/${careerId}`, { basic_semesters_enabled: input.checked });
        swalToast('success', input.checked ? 'La carrera ya puede registrar 1° a 4°' : 'La carrera registra sólo 5° a 9°');
    } catch (error) {
        input.checked = !input.checked;
        showAlert('#generalAlert', 'danger', error.message || 'No se pudo actualizar la carrera.');
    } finally {
        input.disabled = false;
    }
}

async function loadGeneralPanel() {
    try {
        const response = await api.get('/admin/careers', { _fresh: true });
        generalState.careers = (response.careers || []).filter(career => career.activa !== false);
        renderCareers();
        setKpi('kpiCareers', generalState.careers.length);
        setKpi('kpiPeople', generalState.careers.reduce((total, career) => total + Number(career.members_count || 0), 0));
        const open = generalState.careers.filter(career => career.period?.estado === 'abierto').length;
        setKpi('kpiPeriods', `${open}/${generalState.careers.length}`, open < generalState.careers.length);
    } catch (error) {
        showAlert('#generalAlert', 'danger', error.message || 'No se pudo cargar el panel.');
    }
    const [alerts, backups, integrity] = await Promise.allSettled([
        api.get('/admin/operational-alerts/summary', { _cache_ttl: 60000 }),
        api.get('/admin/database-backups-health', { _cache_ttl: 60000 }),
        api.get('/admin/integrity/history', { _cache_ttl: 60000 })
    ]);
    if (alerts.status === 'fulfilled') setKpi('kpiAlerts', Number(alerts.value.open || 0), Number(alerts.value.critical || 0) > 0);
    if (backups.status === 'fulfilled') setKpi('kpiBackups', backups.value.healthy ? 'Sanos' : 'Revisar', !backups.value.healthy);
    if (integrity.status === 'fulfilled') {
        const last = (integrity.value.data || [])[0];
        setKpi('kpiIntegrity', last ? (last.saludable ? 'Correcta' : `${last.incidencias} incid.`) : 'Sin datos', !last || !last.saludable);
    }
}

function openViewAs(careerId = null) {
    const select = document.getElementById('viewAsCareer');
    select.innerHTML = generalState.careers.map(career => `<option value="${career.id}">${esc(career.nombre_corto)}</option>`).join('');
    if (careerId) select.value = String(careerId);
    document.getElementById('viewAsAlert').innerHTML = '';
    viewAsModal ??= new bootstrap.Modal(document.getElementById('viewAsModal'));
    viewAsModal.show();
}

async function startViewAs(profileId, button) {
    button.disabled = true;
    try {
        const bridge = await fetch('/api/view-as.php', {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ action: 'start', perfil_id: profileId, carrera_id: Number(document.getElementById('viewAsCareer').value) })
        });
        const session = await bridge.json().catch(() => ({}));
        if (!bridge.ok) throw new Error(session.error || 'No se pudo abrir la simulación.');
        try { sessionStorage.clear(); } catch (error) { /* la recarga basta */ }
        window.location.replace(session.dashboard_url || '/index.php');
    } catch (error) {
        button.disabled = false;
        showAlert('#viewAsAlert', 'danger', error.message || 'No se pudo abrir la simulación.');
    }
}

document.addEventListener('DOMContentLoaded', loadGeneralPanel);
</script>
</body>
</html>
