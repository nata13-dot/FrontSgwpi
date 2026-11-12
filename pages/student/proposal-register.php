<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !is_student()) { header('Location: /index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi proyecto - <?= APP_NAME ?></title>
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
        <div class="container-xl mt-5 mb-5">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1">Mi proyecto</h1>
                    <p class="text-muted mb-0">Registra la propuesta de tu proyecto integrador o únete a la de tu compañero.</p>
                </div>
                <button class="btn btn-primary" onclick="loadStatus()" aria-label="Actualizar"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
            <div id="statusBox"></div>

            <div class="card border-0 shadow-sm mb-4 d-none" id="partnerQuestion"><div class="card-body">
                <h5 class="mb-1">¿Desarrollas tu proyecto junto con un compañero?</h5>
                <p class="text-muted small">Si un compañero ya registró la propuesta, pide unirte a ella en lugar de registrarla otra vez.</p>
                <div class="d-flex flex-wrap gap-2">
                    <input type="radio" class="btn-check" name="withPartner" id="partnerYes" value="yes" autocomplete="off">
                    <label class="btn btn-outline-primary rounded-pill" for="partnerYes">Sí, con un compañero</label>
                    <input type="radio" class="btn-check" name="withPartner" id="partnerNo" value="no" autocomplete="off">
                    <label class="btn btn-outline-primary rounded-pill" for="partnerNo">No</label>
                </div>
            </div></div>

            <div class="card border-0 shadow-sm mb-4 d-none" id="groupProjectsCard"><div class="card-body">
                <h5 class="mb-1">Propuestas de tu grupo</h5>
                <p class="text-muted small">Elige la de tu compañero; un integrante debe autorizar tu solicitud.</p>
                <div id="groupProjects" class="list-group list-group-flush"></div>
            </div></div>

            <div class="card border-0 shadow-sm d-none" id="formCard">
                <form id="projectForm" class="card-body">
                    <h5 class="mb-3" id="formTitle">Registrar propuesta</h5>
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label" for="title">Nombre del proyecto</label><input class="form-control" id="title" name="title" maxlength="255" required></div>
                        <div class="col-12"><label class="form-label" for="description">Descripción</label><textarea class="form-control" id="description" name="description" rows="4" required></textarea></div>
                        <div class="col-12"><label class="form-label" for="objetivo_general">Objetivo general</label><textarea class="form-control" id="objetivo_general" name="objetivo_general" rows="3" required></textarea></div>
                        <div class="col-12">
                            <label class="form-label">Objetivos específicos</label>
                            <div id="objectives" class="d-grid gap-2"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addObjective"><i class="bi bi-plus-lg"></i> Agregar objetivo</button>
                        </div>
                        <div class="col-12"><h6 class="mt-2 mb-0">Empresa o beneficiario</h6></div>
                        <div class="col-md-6"><label class="form-label" for="company_name">Empresa o negocio</label><input class="form-control" id="company_name" name="company_name" required></div>
                        <div class="col-md-6"><label class="form-label" for="company_giro">Giro</label><input class="form-control" id="company_giro" name="company_giro" required></div>
                        <div class="col-md-6"><label class="form-label" for="company_contact_name">Persona a cargo</label><input class="form-control" id="company_contact_name" name="company_contact_name" required></div>
                        <div class="col-md-6"><label class="form-label" for="company_contact_position">Puesto</label><input class="form-control" id="company_contact_position" name="company_contact_position" required></div>
                        <div class="col-12"><label class="form-label" for="company_address">Dirección</label><input class="form-control" id="company_address" name="company_address" required><div class="form-text">Separa calle, número, colonia y municipio con comas (,).</div></div>
                        <div class="col-md-6"><label class="form-label" for="company_contact_phone">Teléfono de contacto <span class="text-muted small">(opcional)</span></label><input class="form-control" id="company_contact_phone" name="company_contact_phone" type="tel" inputmode="tel"></div>
                        <div class="col-md-6"><label class="form-label" for="company_contact_email">Correo de contacto <span class="text-muted small">(opcional)</span></label><input class="form-control" id="company_contact_email" name="company_contact_email" type="email"></div>
                    </div>
                    <div class="text-end mt-4"><button type="submit" class="btn btn-primary" id="submitBtn"><i class="bi bi-send"></i> Registrar propuesta</button></div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script src="<?= asset('/assets/js/proposals.js') ?>"></script>
<script>
const { esc, badge, details, dateTime } = ProposalUI;
const FIELDS = ['title', 'description', 'objetivo_general', 'company_name', 'company_giro', 'company_contact_name', 'company_contact_position', 'company_address', 'company_contact_phone', 'company_contact_email'];
const readOnly = Boolean(window.SGPI_SESSION?.user?.view_as);
let statusData = null;
let editing = false;

const show = (id, visible) => document.getElementById(id).classList.toggle('d-none', !visible);
const shortName = person => [String(person.nombres || '').split(' ')[0], person.apellido_paterno || person.apa].filter(Boolean).join(' ');

function objectiveRow(value = '') {
    const row = document.createElement('div');
    row.className = 'input-group';
    row.innerHTML = `<span class="input-group-text"></span><input class="form-control objective-input" value="${esc(value)}" aria-label="Objetivo específico"><button type="button" class="btn btn-outline-danger" aria-label="Quitar objetivo"><i class="bi bi-trash"></i></button>`;
    row.querySelector('button').addEventListener('click', () => { row.remove(); numberObjectives(); });
    document.getElementById('objectives').appendChild(row);
    numberObjectives();
}

function numberObjectives() {
    const rows = [...document.querySelectorAll('#objectives .input-group')];
    rows.forEach((row, index) => {
        row.querySelector('.input-group-text').textContent = `${index + 1}.`;
        row.querySelector('input').required = index === 0;
        row.querySelector('button').classList.toggle('d-none', rows.length === 1);
    });
    document.getElementById('addObjective').classList.toggle('d-none', rows.length >= 12);
}

function fillForm(project) {
    FIELDS.forEach(key => { document.getElementById(key).value = project?.[key] || ''; });
    document.getElementById('objectives').innerHTML = '';
    (project?.objetivos?.length ? project.objetivos : ['']).forEach(objectiveRow);
}

function resetChoice() {
    document.querySelectorAll('[name="withPartner"]').forEach(input => { input.checked = false; });
    ['partnerQuestion', 'groupProjectsCard', 'formCard'].forEach(id => show(id, false));
}

async function loadStatus() {
    resetChoice();
    const box = document.getElementById('statusBox');
    try {
        statusData = await api.get('/proposal/student-status', { _fresh: true });
    } catch (error) {
        box.innerHTML = `<div class="alert alert-danger">${esc(error.message)}</div>`;
        return;
    }
    const data = statusData;
    const project = data.project;
    editing = false;

    if (data.profile_required) {
        box.innerHTML = '<div class="alert alert-warning">Completa tu perfil antes de registrar una propuesta.</div>';
        return;
    }
    if (project) {
        const canFix = project.proposal_status === 'requiere_cambios' && project.revision_allowed_until && new Date(project.revision_allowed_until) > new Date();
        const slots = Math.max(0, (data.max_members || 4) - (project.students || []).length);
        const requests = project.join_requests || [];
        box.innerHTML = `
            <div class="card border-0 shadow-sm mb-4"><div class="card-body">
                <div class="d-flex justify-content-between gap-2 mb-1"><h5 class="mb-0">${esc(project.title)}</h5>${badge(project.proposal_status)}</div>
                ${project.proposal_review_comment ? `<div class="alert ${canFix ? 'alert-warning' : 'alert-light border'} small"><strong>Observaciones:</strong> ${esc(project.proposal_review_comment)}${canFix ? `<br>Puedes corregir hasta ${esc(dateTime(project.revision_allowed_until))}.` : ''}</div>` : ''}
                ${project.proposal_status === 'pendiente' ? '<p class="text-muted small"><i class="bi bi-clock"></i> Espera la validación de coordinación, jefatura o de un docente designado.</p>' : ''}
                ${details(project)}
            </div></div>
            ${project.is_proposal ? `<div class="card border-0 shadow-sm mb-4"><div class="card-body">
                <h5 class="mb-1">Solicitudes para unirse</h5>
                <p class="text-muted small">${slots ? `Quedan ${slots} lugar${slots === 1 ? '' : 'es'} en el equipo.` : 'El equipo está completo.'}</p>
                ${requests.length ? `<div class="list-group list-group-flush">${requests.map(request => `
                    <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                        <div><strong>${esc(shortName(request))}</strong><br><small class="text-muted">${esc(request.estudiante_id)} · ${esc(dateTime(request.creada_en))}</small></div>
                        ${readOnly ? '' : `<div class="d-flex gap-2"><button class="btn btn-sm btn-success" ${slots ? '' : 'disabled'} onclick="answerRequest(${request.id}, 'aceptada')"><i class="bi bi-person-check"></i> Aceptar</button><button class="btn btn-sm btn-outline-danger" onclick="answerRequest(${request.id}, 'rechazada')"><i class="bi bi-x-lg"></i> Rechazar</button></div>`}
                    </div>`).join('')}</div>` : '<p class="text-muted small mb-0">Nadie ha pedido unirse a tu proyecto.</p>'}
            </div></div>` : ''}`;
        if (canFix && !readOnly) {
            editing = true;
            fillForm(project);
            document.getElementById('formTitle').textContent = 'Corregir propuesta';
            document.getElementById('submitBtn').innerHTML = '<i class="bi bi-send"></i> Enviar corrección';
            show('formCard', true);
        }
        return;
    }
    if (data.my_request) {
        box.innerHTML = `<div class="card border-0 shadow-sm"><div class="card-body">
            <div class="d-flex justify-content-between gap-2"><h5 class="mb-1">Solicitud enviada</h5><span class="badge bg-warning text-dark">En espera</span></div>
            <p class="mb-1">Pediste unirte a <strong>${esc(data.my_request.project_title)}</strong>. Un integrante del proyecto debe aceptarte.</p>
            <p class="text-muted small">Enviada: ${esc(dateTime(data.my_request.creada_en))}</p>
            ${readOnly ? '' : `<button class="btn btn-outline-danger" onclick="cancelRequest(${data.my_request.id})"><i class="bi bi-x-lg"></i> Cancelar solicitud</button>`}
        </div></div>`;
        return;
    }
    if (!data.subject_group) {
        box.innerHTML = '<div class="alert alert-secondary">No tienes un grupo asignado en el periodo actual. Pide a la jefatura que te asigne uno.</div>';
        return;
    }
    if (!data.window?.open) {
        box.innerHTML = `<div class="alert alert-secondary">El registro de propuestas de tu carrera está cerrado.${data.window?.closes_at ? ` Cerró el ${esc(dateTime(data.window.closes_at))}.` : ''}</div>`;
        return;
    }
    box.innerHTML = `
        ${data.last_rejected ? `<div class="alert alert-danger"><strong>Tu propuesta anterior fue rechazada:</strong> ${esc(data.last_rejected.titulo)}${data.last_rejected.comentario_revision ? `<br>${esc(data.last_rejected.comentario_revision)}` : ''}</div>` : ''}
        <div class="alert alert-success">Registro abierto hasta ${esc(dateTime(data.window.closes_at))} · ${esc(data.subject_group.nombre)}</div>`;
    if (!readOnly) show('partnerQuestion', true);
}

async function loadGroupProjects() {
    const list = document.getElementById('groupProjects');
    list.innerHTML = '<div class="text-muted small">Cargando propuestas...</div>';
    try {
        const data = await api.get('/proposal/group-projects', { _fresh: true });
        list.innerHTML = (data.projects || []).length ? data.projects.map(project => `
            <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                <div><strong>${esc(project.title)}</strong><br><small class="text-muted">${esc(project.members.join(', ') || 'Sin integrantes')} · ${project.slots ? `${project.slots} lugar${project.slots === 1 ? '' : 'es'}` : 'Equipo completo'}</small></div>
                <button class="btn btn-sm btn-primary" ${project.slots ? '' : 'disabled'} onclick="requestJoin(${project.id})"><i class="bi bi-person-plus"></i> Solicitar unirme</button>
            </div>`).join('')
            : '<div class="alert alert-light border mb-0">Ningún compañero de tu grupo ha registrado una propuesta. Si tu compañero aún no la registra, uno de los dos debe elegir «No» y registrarla.</div>';
    } catch (error) {
        list.innerHTML = `<div class="alert alert-danger">${esc(error.message)}</div>`;
    }
}

async function run(request, fallback) {
    try {
        const response = await request();
        swalToast('success', response?.message || fallback);
        loadStatus();
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

const requestJoin = id => run(() => api.post(`/proposal/projects/${id}/join-requests`), 'Solicitud enviada');
const answerRequest = (id, decision) => run(() => api.put(`/proposal/join-requests/${id}`, { decision }), decision === 'aceptada' ? 'Integrante agregado' : 'Solicitud rechazada');
async function cancelRequest(id) {
    const confirmed = await Swal.fire({ title: 'Cancelar solicitud', text: 'Ya no pedirás unirte a ese proyecto.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, cancelar', cancelButtonText: 'Volver' });
    if (confirmed.isConfirmed) run(() => api.delete(`/proposal/join-requests/${id}`), 'Solicitud cancelada');
}

document.querySelectorAll('[name="withPartner"]').forEach(input => input.addEventListener('change', () => {
    const yes = input.value === 'yes' && input.checked;
    show('groupProjectsCard', yes);
    show('formCard', !yes);
    if (yes) {
        loadGroupProjects();
    } else {
        fillForm(null);
        document.getElementById('formTitle').textContent = 'Registrar propuesta';
        document.getElementById('submitBtn').innerHTML = '<i class="bi bi-send"></i> Registrar propuesta';
    }
}));
document.getElementById('addObjective').addEventListener('click', () => objectiveRow());

document.getElementById('projectForm').addEventListener('submit', async event => {
    event.preventDefault();
    const button = document.getElementById('submitBtn');
    if (button.disabled) return;
    const payload = Object.fromEntries(FIELDS.map(key => [key, document.getElementById(key).value.trim()]));
    payload.objetivos = [...document.querySelectorAll('.objective-input')].map(input => input.value.trim()).filter(Boolean);
    button.disabled = true;
    try {
        const response = editing ? await api.put('/proposal/mine', payload) : await api.post('/proposal/register', payload);
        swalToast('success', response.message || 'Propuesta registrada');
        loadStatus();
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    } finally {
        button.disabled = false;
    }
});

document.addEventListener('DOMContentLoaded', loadStatus);
window.onLiveChange?.(['proposals', 'join_requests', 'projects'], loadStatus);
</script>
</body>
</html>
