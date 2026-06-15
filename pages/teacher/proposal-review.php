<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !is_teacher()) { header('Location: /index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Propuestas - <?= APP_NAME ?></title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-1">Revisión de Propuestas</h1>
                    <p class="text-muted mb-0">Fundamentos de Ingenieria de Software</p>
                </div>
                <button class="btn btn-primary" onclick="loadPage()"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-calendar-range"></i> Periodos de registro de mis grupos</h5>
                </div>
                <div class="card-body">
                    <div id="windowGroups" class="row g-3"></div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Propuestas asignadas</h4>
                <span class="badge bg-secondary" id="proposalCounter">0 propuestas</span>
            </div>
            <div id="projectsContainer" class="row g-4"></div>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL='<?= API_BASE_URL ?>';</script>
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/api.js"></script>
<script>
const esc = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[char]));
const badge = status => ({
    pendiente: 'bg-warning text-dark',
    aprobado: 'bg-success',
    requiere_cambios: 'bg-info text-dark',
    rechazado: 'bg-danger'
}[status] || 'bg-secondary');
const fullName = user => [user?.nombres, user?.apa, user?.ama].filter(Boolean).join(' ') || user?.id || '';
const activeAuthors = project => (Array.isArray(project?.students) ? project.students : []).map(fullName).filter(Boolean).join(', ') || '-';
let proposalProjects = [];
let windowData = { groups: [] };

function renderProjects() {
    const box = document.getElementById('projectsContainer');
    document.getElementById('proposalCounter').textContent = `${proposalProjects.length} propuesta${proposalProjects.length === 1 ? '' : 's'}`;
    if (!proposalProjects.length) {
        box.innerHTML = '<p class="text-muted">No tienes propuestas pendientes en tus grupos asignados.</p>';
        return;
    }

    box.innerHTML = proposalProjects.map(project => `
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <h5>${esc(project.title)}</h5>
                        <span class="badge ${badge(project.proposal_status)}">${esc(project.proposal_status)}</span>
                    </div>
                    <p class="text-muted">${esc(project.description || 'Sin descripción')}</p>
                    <small class="d-block"><i class="bi bi-people"></i> ${esc(activeAuthors(project))}</small>
                    <small class="d-block"><i class="bi bi-collection"></i> ${esc(project.subject_group?.nombre || '-')}</small>
                    <hr>
                    <strong>Empresa</strong>
                    <p class="mb-1">${esc(project.company_name || '-')}</p>
                    <small>${esc(project.company_contact_name || '')} ${esc(project.company_contact_position || '')}</small>
                    ${project.proposal_review_comment ? `<div class="alert alert-light border mt-3 mb-0">${esc(project.proposal_review_comment)}</div>` : ''}
                </div>
                <div class="card-footer bg-light border-0 d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-success" onclick="review(${project.id},'aprobado')">Aprobar</button>
                    <button class="btn btn-sm btn-warning" onclick="review(${project.id},'requiere_cambios')">Solicitar cambios</button>
                    <button class="btn btn-sm btn-danger" onclick="review(${project.id},'rechazado')">Rechazar</button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderWindowGroups() {
    const box = document.getElementById('windowGroups');
    const groups = windowData.groups || [];
    if (!groups.length) {
        box.innerHTML = '<p class="text-muted mb-0">No tienes grupos asignados para supervisar propuestas.</p>';
        return;
    }

    box.innerHTML = groups.map(group => {
        const windows = (group.registration_windows || []).map(item => `
            <div class="border rounded p-2 mb-2">
                <div><strong>${new Date(item.starts_at).toLocaleString('es-MX')}</strong></div>
                <small class="text-muted">Hasta ${new Date(item.ends_at).toLocaleString('es-MX')}</small>
                <button class="btn btn-sm btn-outline-danger float-end" onclick="deleteWindow(${item.id})" title="Eliminar periodo"><i class="bi bi-trash"></i></button>
            </div>
        `).join('') || '<p class="text-muted small">Sin periodos configurados.</p>';

        return `
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100">
                    <h6>${esc(group.nombre)} <span class="badge bg-primary">${esc(group.grupo || '')}</span></h6>
                    ${windows}
                    <div class="row g-2 mt-2">
                        <div class="col-md-5"><input type="datetime-local" class="form-control form-control-sm" id="windowStart-${group.id}"></div>
                        <div class="col-md-5"><input type="datetime-local" class="form-control form-control-sm" id="windowEnd-${group.id}"></div>
                        <div class="col-md-2 d-grid"><button class="btn btn-sm btn-primary" onclick="addWindow(${group.id})"><i class="bi bi-plus"></i></button></div>
                    </div>
                </div>
            </div>`;
    }).join('');
}

async function loadPage() {
    [proposalProjects, windowData] = await Promise.all([
        api.get('/proposal/teacher-projects'),
        api.get('/proposal/window-groups')
    ]);
    renderProjects();
    renderWindowGroups();
}

async function addWindow(groupId) {
    const startsAt = document.getElementById(`windowStart-${groupId}`).value;
    const endsAt = document.getElementById(`windowEnd-${groupId}`).value;
    if (!startsAt || !endsAt) {
        Swal.fire('Faltan fechas', 'Indica el inicio y cierre del periodo.', 'warning');
        return;
    }
    await api.post('/proposal/windows', {
        subject_group_id: groupId,
        starts_at: startsAt,
        ends_at: endsAt,
        activo: true
    });
    windowData = await api.get('/proposal/window-groups', { _fresh: 1 });
    renderWindowGroups();
    swalToast('Periodo de registro creado', 'success');
}

async function deleteWindow(id) {
    if (!await confirmAction({ title: 'Eliminar periodo de registro' })) return;
    await api.delete(`/proposal/windows/${id}`);
    windowData = await api.get('/proposal/window-groups', { _fresh: 1 });
    renderWindowGroups();
    swalToast('Periodo eliminado', 'success');
}

async function review(id, status) {
    const commentResult = await stableSwalFire({
        title: 'Comentario',
        input: 'textarea',
        inputPlaceholder: 'Observaciones para el alumno',
        showCancelButton: true,
        confirmButtonText: 'Continuar'
    });
    if (!commentResult.isConfirmed) return;

    let until = null;
    if (status === 'requiere_cambios') {
        const dateResult = await stableSwalFire({
            title: 'Permitir correccion hasta',
            input: 'datetime-local',
            showCancelButton: true,
            confirmButtonText: 'Guardar'
        });
        if (!dateResult.isConfirmed || !dateResult.value) return;
        until = dateResult.value;
    }

    try {
        const response = await api.post(`/proposal/projects/${id}/review`, {
            proposal_status: status,
            proposal_review_comment: commentResult.value || '',
            revision_allowed_until: until
        });
        const index = proposalProjects.findIndex(project => Number(project.id) === Number(id));
        if (index >= 0) proposalProjects[index] = response.project;
        renderProjects();
        swalToast('Revision registrada', 'success');
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', loadPage);
</script>
</body>
</html>
