<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !can_manage_academics()) {
    header('Location: ' . dashboard_url());
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de cuenta - <?= APP_NAME ?></title>
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
            <div class="mb-4">
                <span class="text-uppercase small fw-bold text-muted">Personas</span>
                <h1 class="h2 mb-2">Solicitudes de cuenta</h1>
                <p class="text-muted mb-0">Alumnos que confirmaron su correo institucional. La cuenta se crea al aprobar; hace falta un periodo abierto en la carrera.</p>
            </div>
            <div class="d-flex gap-2 mb-3">
                <button class="btn btn-primary" type="button" data-status="por_aprobar">Por revisar</button>
                <button class="btn btn-outline-primary" type="button" data-status="rechazada">Rechazadas</button>
            </div>
            <section class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Alumno</th><th>Carrera</th><th>Grupo</th><th>Correo confirmado</th><th id="lastColumn">Acciones</th></tr></thead>
                        <tbody id="requestRows"><tr><td colspan="5" class="text-center text-muted py-4">Cargando...</td></tr></tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script>
const requestEscape = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
const requestDate = value => value ? new Date(String(value).replace(' ', 'T')).toLocaleString('es-MX', {dateStyle: 'medium', timeStyle: 'short'}) : '—';
const requestName = row => [row.nombres, row.apa, row.ama].filter(Boolean).join(' ');
let requestStatus = 'por_aprobar';
let requestRows = [];

async function loadRequests() {
    const body = document.getElementById('requestRows');
    const pending = requestStatus === 'por_aprobar';
    document.getElementById('lastColumn').textContent = pending ? 'Acciones' : 'Motivo';
    try {
        requestRows = await api.get('/self-registration/requests', {_fresh: true, status: requestStatus});
        body.innerHTML = requestRows.map(row => `<tr>
            <td style="white-space:normal;overflow-wrap:anywhere;min-width:240px"><strong>${requestEscape(requestName(row))}</strong><div class="small text-muted">${requestEscape(row.matricula)} · ${requestEscape(row.correo)}</div></td>
            <td style="white-space:normal">${requestEscape(row.carrera || '—')}</td>
            <td style="white-space:normal">${row.grupo ? `${row.grupo.semestre ? `${Number(row.grupo.semestre)}° · ` : ''}${requestEscape(row.grupo.nombre)}` : 'Ya no disponible'}</td>
            <td class="text-nowrap">${requestEscape(requestDate(row.verificado_en))}</td>
            <td style="white-space:normal">${pending
                ? `<div class="d-flex gap-2"><button class="btn btn-sm btn-success text-nowrap" type="button" onclick="approveRequest(${Number(row.id)}, this)"><i class="bi bi-check-lg"></i> Aprobar</button><button class="btn btn-sm btn-outline-danger text-nowrap" type="button" onclick="rejectRequest(${Number(row.id)})"><i class="bi bi-x-lg"></i> Rechazar</button></div>`
                : requestEscape(row.motivo)}</td>
        </tr>`).join('') || `<tr><td colspan="5" class="text-center text-muted py-4">${pending ? 'No hay solicitudes por revisar.' : 'No hay solicitudes rechazadas.'}</td></tr>`;
    } catch (error) {
        body.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${requestEscape(error.message)}</td></tr>`;
    }
}

function requestDone(response) {
    swalToast('success', response.message);
    if (response.email_sent === false) swalToast('warning', 'No se pudo enviar el correo de aviso. Avísale al alumno por otra vía.', 8000);
    return loadRequests();
}

async function approveRequest(id, button) {
    const row = requestRows.find(item => Number(item.id) === id);
    const confirmed = await confirmAction({title: 'Aprobar cuenta', text: `Se creará la cuenta ${row.matricula} de ${requestName(row)} y quedará inscrita en su grupo.`, confirmButtonText: 'Aprobar', icon: 'question'});
    if (!confirmed) return;
    button.disabled = true;
    try {
        await requestDone(await api.put(`/self-registration/requests/${id}/approve`, {}));
    } catch (error) {
        swalToast('error', error.message, 8000);
        button.disabled = false;
    }
}

async function rejectRequest(id) {
    const row = requestRows.find(item => Number(item.id) === id);
    const reason = await promptText({title: `Rechazar a ${requestName(row)}`, inputPlaceholder: 'El alumno recibirá este motivo por correo', confirmButtonText: 'Rechazar', inputValidator: value => String(value || '').trim().length >= 5 ? null : 'Escribe el motivo (mínimo 5 caracteres).'});
    if (reason === null) return;
    try {
        await requestDone(await api.put(`/self-registration/requests/${id}/reject`, {reason: reason.trim()}));
    } catch (error) {
        swalToast('error', error.message, 8000);
    }
}

document.querySelectorAll('[data-status]').forEach(button => button.addEventListener('click', () => {
    requestStatus = button.dataset.status;
    document.querySelectorAll('[data-status]').forEach(other => {
        other.classList.toggle('btn-primary', other === button);
        other.classList.toggle('btn-outline-primary', other !== button);
    });
    loadRequests();
}));
document.addEventListener('DOMContentLoaded', loadRequests);
window.onLiveChange?.(['registration_requests'], loadRequests);
</script>
</body>
</html>
