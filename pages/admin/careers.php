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
    <link rel="stylesheet" href="/assets/css/app.css">
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
                    <p class="text-muted mb-0">Configura la identidad de cada carrera y las personas que pueden administrarla o participar en ella.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary" type="button" onclick="downloadInstitutionalSummary()"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar resumen</button>
                    <span class="badge rounded-pill text-bg-primary px-3 py-2 align-self-center"><i class="bi bi-shield-check"></i> Administrador general</span>
                </div>
            </div>

            <div id="careerAlert"></div>
            <section class="career-admin-grid mb-4" id="careerCards" aria-label="Carreras disponibles">
                <div class="career-admin-loading">Cargando carreras...</div>
            </section>

            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="h5 mb-1"><i class="bi bi-bar-chart-steps"></i> Puesta en marcha</h2>
                            <p class="text-muted small mb-0">Resumen institucional sin combinar registros entre carreras.</p>
                        </div>
                        <span class="badge text-bg-light">Actualizado con datos reales</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Carrera</th><th>Personas</th><th>Académico</th><th>Operación</th><th>Preparación</th><th class="text-end">Acceso</th></tr></thead>
                        <tbody id="careerOverviewRows">
                            <tr><td colspan="6" class="text-center text-muted py-4">Cargando resumen...</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-xl-4">
                    <section class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-3"><i class="bi bi-palette"></i> Identidad de carrera</h2>
                            <form id="careerIdentityForm">
                                <input type="hidden" id="identityCareerId">
                                <div class="mb-3">
                                    <label class="form-label" for="careerName">Nombre</label>
                                    <input class="form-control" id="careerName" required maxlength="180">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="careerShortName">Nombre corto</label>
                                    <input class="form-control" id="careerShortName" required maxlength="100">
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-4">
                                        <label class="form-label" for="careerPrimary">Principal</label>
                                        <input class="form-control form-control-color w-100" type="color" id="careerPrimary">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label" for="careerSecondary">Secundario</label>
                                        <input class="form-control form-control-color w-100" type="color" id="careerSecondary">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label" for="careerAccent">Acento</label>
                                        <input class="form-control form-control-color w-100" type="color" id="careerAccent">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="careerMotto">Lema</label>
                                    <textarea class="form-control" id="careerMotto" rows="3" maxlength="255"></textarea>
                                </div>
                                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-check2-circle"></i> Guardar identidad</button>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-xl-8">
                    <section class="card border-0 shadow-sm">
                        <div class="card-header bg-white p-4 border-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <h2 class="h5 mb-1"><i class="bi bi-people"></i> Membresías</h2>
                                    <p class="text-muted small mb-0" id="membershipCareerLabel">Selecciona una carrera.</p>
                                </div>
                                <form class="d-flex gap-2" id="membershipSearchForm">
                                    <input class="form-control" id="membershipSearch" placeholder="Buscar persona...">
                                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body px-4 pt-0">
                            <form class="career-membership-form mb-4" id="membershipForm">
                                <div>
                                    <label class="form-label" for="membershipUserId">No. de control o empleado</label>
                                    <input class="form-control" id="membershipUserId" required maxlength="20">
                                </div>
                                <div>
                                    <label class="form-label" for="membershipRole">Rol en esta carrera</label>
                                    <select class="form-select" id="membershipRole" required>
                                        <option value="1">Administrador</option>
                                        <option value="2">Docente</option>
                                        <option value="3">Estudiante</option>
                                        <option value="5">Jefe de Carrera</option>
                                        <option value="6">Asistente de Jefe de Carrera</option>
                                        <option value="7">Coordinador de Proyectos</option>
                                    </select>
                                </div>
                                <label class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="membershipPrimary">
                                    <span class="form-check-label">Carrera principal</span>
                                </label>
                                <button class="btn btn-primary mt-4" type="submit"><i class="bi bi-person-plus"></i> Asignar</button>
                            </form>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead><tr><th>Persona</th><th>Rol</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                                    <tbody id="membershipRows"><tr><td colspan="4" class="text-center text-muted py-4">Selecciona una carrera.</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    </section>
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
const careerState = { careers: [], selected: null };
const roleNames = {1: 'Administrador', 2: 'Docente', 3: 'Estudiante', 5: 'Jefe de Carrera', 6: 'Asistente de Jefe de Carrera', 7: 'Coordinador de Proyectos'};

function careerEsc(value) {
    return String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));
}

function careerMessage(type, message) {
    document.getElementById('careerAlert').innerHTML = `<div class="alert alert-${type}">${careerEsc(message)}</div>`;
}

function renderCareerCards() {
    document.getElementById('careerCards').innerHTML = careerState.careers.map(career => `
        <button type="button" class="career-admin-card ${careerState.selected?.id === career.id ? 'active' : ''}" data-id="${career.id}" style="--card-primary:${career.color_primario};--card-secondary:${career.color_secundario};--card-accent:${career.color_acento || career.color_primario}">
            <span class="career-admin-code">${careerEsc(career.clave)}</span>
            <strong>${careerEsc(career.nombre_corto)}</strong>
            <small>${Number(career.members_count || 0)} integrantes · ${Number(career.readiness?.percentage || 0)}% preparada</small>
        </button>
    `).join('');
    document.querySelectorAll('.career-admin-card').forEach(card => card.addEventListener('click', () => selectCareer(Number(card.dataset.id))));
    renderCareerOverview();
}

function renderCareerOverview() {
    const labels = {
        identity: 'Identidad',
        career_management: 'Administrador o Jefe de Carrera',
        subjects: 'Asignaturas',
        groups: 'Grupos',
        students: 'Estudiantes',
        configuration: 'Configuración',
        rubrics: 'Rúbricas'
    };
    const rows = document.getElementById('careerOverviewRows');
    rows.innerHTML = careerState.careers.map(career => {
        const readiness = career.readiness || {};
        const missing = Object.entries(readiness.checklist || {})
            .filter(([, complete]) => !complete)
            .map(([key]) => labels[key] || key);
        const roles = career.role_counts || {};
        const statusClass = readiness.status === 'ready' ? 'success' : (readiness.status === 'in_progress' ? 'warning' : 'secondary');
        return `<tr>
            <td><strong>${careerEsc(career.nombre_corto)}</strong><small class="d-block text-muted">${careerEsc(career.clave)}</small></td>
            <td><strong>${Number(career.members_count || 0)}</strong><small class="d-block text-muted">${Number(roles.administrators || 0)} admin · ${Number(roles.career_heads || 0)} jefatura · ${Number(roles.career_head_assistants || 0)} asistencia · ${Number(roles.project_coordinators || 0)} coordinación · ${Number(roles.teachers || 0)} docentes · ${Number(roles.students || 0)} estudiantes</small></td>
            <td><strong>${Number(career.subjects_count || 0)} asignaturas</strong><small class="d-block text-muted">${Number(career.groups_count || 0)} grupos</small></td>
            <td><strong>${Number(career.projects_count || 0)} proyectos</strong><small class="d-block text-muted">${Number(career.evaluations_count || 0)} evaluaciones · ${Number(career.documents_count || 0)} documentos</small></td>
            <td style="min-width:220px">
                <div class="d-flex justify-content-between small mb-1"><span class="badge text-bg-${statusClass}">${Number(readiness.percentage || 0)}%</span><span>${Number(readiness.completed || 0)}/${Number(readiness.total || 7)}</span></div>
                <div class="progress" style="height:7px"><div class="progress-bar bg-${statusClass}" style="width:${Number(readiness.percentage || 0)}%"></div></div>
                <small class="d-block text-muted mt-1">${missing.length ? `Falta: ${careerEsc(missing.join(', '))}` : 'Lista para operar'}</small>
            </td>
            <td class="text-end"><button class="btn btn-sm btn-outline-primary" type="button" data-enter-career="${Number(career.id)}"><i class="bi bi-box-arrow-in-right"></i> Administrar</button></td>
        </tr>`;
    }).join('');
    document.querySelectorAll('[data-enter-career]').forEach(button => button.addEventListener('click', () => enterCareer(Number(button.dataset.enterCareer))));
}

function enterCareer(careerId) {
    const switchOption = document.querySelector(`.career-switch-option[data-career-id="${careerId}"]`);
    if (!switchOption) {
        careerMessage('danger', 'No se encontró el acceso a la carrera seleccionada.');
        return;
    }
    switchOption.click();
}

function selectCareer(id) {
    careerState.selected = careerState.careers.find(career => Number(career.id) === id) || null;
    if (!careerState.selected) return;
    renderCareerCards();
    const career = careerState.selected;
    document.getElementById('identityCareerId').value = career.id;
    document.getElementById('careerName').value = career.nombre || '';
    document.getElementById('careerShortName').value = career.nombre_corto || '';
    document.getElementById('careerPrimary').value = career.color_primario || '#1B396A';
    document.getElementById('careerSecondary').value = career.color_secundario || '#2D5A96';
    document.getElementById('careerAccent').value = career.color_acento || career.color_primario || '#00A6D6';
    document.getElementById('careerMotto').value = career.lema || '';
    document.getElementById('membershipCareerLabel').textContent = career.nombre;
    loadMemberships();
}

async function loadCareers() {
    try {
        const response = await api.get('/admin/careers', {_fresh: true});
        careerState.careers = response.careers || [];
        const preferred = careerState.selected?.id || Number(window.SGPI_SESSION?.user?.active_career?.id) || careerState.careers[0]?.id;
        selectCareer(Number(preferred));
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
        const blob = await response.blob();
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `resumen_carreras_${new Date().toISOString().slice(0, 10)}.csv`;
        link.click();
        URL.revokeObjectURL(link.href);
    } catch (error) {
        careerMessage('danger', error.message);
    }
}

async function loadMemberships() {
    if (!careerState.selected) return;
    const body = document.getElementById('membershipRows');
    body.innerHTML = '<tr><td colspan="4" class="text-center py-4">Cargando...</td></tr>';
    try {
        const response = await api.get('/admin/career-memberships', {
            carrera_id: careerState.selected.id,
            search: document.getElementById('membershipSearch').value.trim(),
            _fresh: true
        });
        const rows = response.data || [];
        body.innerHTML = rows.length ? rows.map(item => {
            const user = item.user || {};
            const name = [user.nombres, user.apellido_paterno, user.apellido_materno].filter(Boolean).join(' ');
            return `<tr>
                <td><strong>${careerEsc(name || user.id)}</strong><small class="d-block text-muted">${careerEsc(user.id)}${item.es_principal ? ' · Principal' : ''}</small></td>
                <td>${careerEsc(roleNames[item.perfil_id] || 'Sin rol')}</td>
                <td><span class="badge ${item.activo ? 'text-bg-success' : 'text-bg-secondary'}">${item.activo ? 'Activo' : 'Inactivo'}</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary" data-toggle-membership="${item.id}" data-active="${item.activo ? 1 : 0}" title="Cambiar estado"><i class="bi bi-power"></i></button>
                    <button class="btn btn-sm btn-outline-danger" data-delete-membership="${item.id}" title="Eliminar"><i class="bi bi-trash"></i></button>
                </td>
            </tr>`;
        }).join('') : '<tr><td colspan="4" class="text-center text-muted py-4">No hay membresías para mostrar.</td></tr>';
        bindMembershipActions();
    } catch (error) {
        body.innerHTML = `<tr><td colspan="4" class="text-danger py-4">${careerEsc(error.message)}</td></tr>`;
    }
}

function bindMembershipActions() {
    document.querySelectorAll('[data-toggle-membership]').forEach(button => button.addEventListener('click', async () => {
        await api.put(`/admin/career-memberships/${button.dataset.toggleMembership}`, {activo: button.dataset.active !== '1'});
        loadMemberships();
    }));
    document.querySelectorAll('[data-delete-membership]').forEach(button => button.addEventListener('click', async () => {
        if (!confirm('¿Eliminar esta membresía?')) return;
        await api.delete(`/admin/career-memberships/${button.dataset.deleteMembership}`);
        loadCareers();
    }));
}

document.getElementById('careerIdentityForm').addEventListener('submit', async event => {
    event.preventDefault();
    const id = Number(document.getElementById('identityCareerId').value);
    try {
        await api.put(`/admin/careers/${id}`, {
            nombre: document.getElementById('careerName').value.trim(),
            nombre_corto: document.getElementById('careerShortName').value.trim(),
            color_primario: document.getElementById('careerPrimary').value,
            color_secundario: document.getElementById('careerSecondary').value,
            color_acento: document.getElementById('careerAccent').value,
            lema: document.getElementById('careerMotto').value.trim()
        });
        careerMessage('success', 'Identidad actualizada correctamente.');
        await loadCareers();
    } catch (error) {
        careerMessage('danger', error.message);
    }
});

document.getElementById('membershipForm').addEventListener('submit', async event => {
    event.preventDefault();
    try {
        await api.post('/admin/career-memberships', {
            usuario_id: document.getElementById('membershipUserId').value.trim(),
            carrera_id: careerState.selected.id,
            perfil_id: Number(document.getElementById('membershipRole').value),
            es_principal: document.getElementById('membershipPrimary').checked,
            activo: true
        });
        event.target.reset();
        careerMessage('success', 'Membresía guardada correctamente.');
        await loadCareers();
    } catch (error) {
        careerMessage('danger', error.message);
    }
});

document.getElementById('membershipSearchForm').addEventListener('submit', event => {
    event.preventDefault();
    loadMemberships();
});
document.addEventListener('DOMContentLoaded', loadCareers);
</script>
</body>
</html>
