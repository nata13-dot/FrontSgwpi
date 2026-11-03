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
    <title>Correos enviados - <?= APP_NAME ?></title>
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
                <span class="text-uppercase small fw-bold text-muted">Continuidad operativa</span>
                <h1 class="h2 mb-2">Correos enviados</h1>
                <p class="text-muted mb-0">A quién se envió cada correo y si llegó al servidor de Gmail. El contenido y el asunto no se conservan; el registro dura 90 días.</p>
            </div>

            <section class="card border-0 shadow-sm mb-4"><div class="card-body p-4" id="mailerStatus"><div class="text-center text-muted py-2">Consultando el servicio de correo...</div></div></section>

            <section class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
                <form class="row g-3" id="emailFilters">
                    <div class="col-md-6"><label class="form-label" for="filterSearch">Destinatario</label><input class="form-control" id="filterSearch" type="search" placeholder="Buscar por correo"></div>
                    <div class="col-md-3"><label class="form-label" for="filterStatus">Estado</label><select class="form-select" id="filterStatus"><option value="">Todos</option><option value="queued">En cola</option><option value="sending">Enviando</option><option value="sent">Enviado</option><option value="failed">Falló</option></select></div>
                    <div class="col-md-3"><label class="form-label" for="filterCategory">Tipo</label><select class="form-select" id="filterCategory"><option value="">Todos</option><option value="credenciales">Credenciales</option><option value="codigo_registro">Código de registro</option><option value="recuperacion">Recuperación de contraseña</option><option value="general">General</option></select></div>
                </form>
            </div></section>

            <section class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Destinatario</th><th>Tipo</th><th>Estado</th><th>Registrado</th><th>Enviado</th><th>Copia en Gmail</th></tr></thead>
                        <tbody id="emailRows"><tr><td colspan="6" class="text-center text-muted py-4">Cargando...</td></tr></tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center p-3 d-none" id="emailPager">
                    <small class="text-muted" id="emailPagerText"></small>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" type="button" id="emailPrev" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" id="emailNext" aria-label="Siguiente"><i class="bi bi-chevron-right"></i></button>
                    </div>
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
const emailEscape = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
const emailDate = value => value ? new Date(value).toLocaleString('es-MX', {dateStyle: 'medium', timeStyle: 'short'}) : '—';
const EMAIL_CATEGORIES = {credenciales: 'Credenciales', codigo_registro: 'Código de registro', recuperacion: 'Recuperación de contraseña', general: 'General'};
const EMAIL_STATUS = {queued: ['En cola', 'secondary'], sending: ['Enviando', 'secondary'], sent: ['Enviado', 'success'], failed: ['Falló', 'danger']};
const SENT_COPY = {removed: ['Borrada', 'success'], pending: ['Por borrar', 'warning'], failed: ['No se pudo borrar', 'danger']};
let emailPage = 1;
let emailLastPage = 1;

function renderMailerStatus(stats) {
    const errors = [stats.account_error, stats.sent_copy_error].filter(Boolean);
    document.getElementById('mailerStatus').innerHTML = `
        <div class="d-flex flex-wrap align-items-center gap-4">
            <span class="display-6 text-${errors.length ? 'danger' : 'success'}"><i class="bi bi-envelope-${errors.length ? 'exclamation' : 'check'}"></i></span>
            <div class="flex-grow-1">
                <h2 class="h5 mb-2">${emailEscape(stats.account || 'Cuenta sin configurar')}</h2>
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    <span><strong>${Number(stats.sent_last_24h)}</strong> de ${Number(stats.max_per_day)} en las últimas 24 horas</span>
                    <span><strong>${Number(stats.queued)}</strong> en cola</span>
                    <span><strong>${Number(stats.failed)}</strong> ${Number(stats.failed) === 1 ? 'fallido' : 'fallidos'}</span>
                </div>
                ${errors.map(error => `<div class="small text-danger mt-2">${emailEscape(error)}</div>`).join('')}
            </div>
        </div>`;
}

function emailBadge(pair) {
    return pair ? `<span class="badge text-bg-${pair[1]}">${pair[0]}</span>` : '—';
}

async function loadEmails() {
    const body = document.getElementById('emailRows');
    const params = {_fresh: true, page: emailPage, search: document.getElementById('filterSearch').value.trim(), status: document.getElementById('filterStatus').value, category: document.getElementById('filterCategory').value};
    try {
        const response = await api.get('/admin/sent-emails', params);
        renderMailerStatus(response.stats || {});
        body.innerHTML = (response.data || []).map(email => `<tr>
            <td style="overflow-wrap:anywhere;white-space:normal;min-width:260px"><strong>${emailEscape(email.to)}</strong>${email.last_error ? `<div class="small text-danger">${emailEscape(email.last_error)}</div>` : ''}</td>
            <td>${emailEscape(EMAIL_CATEGORIES[email.category] || email.category)}</td>
            <td>${emailBadge(EMAIL_STATUS[email.status])}</td>
            <td class="text-nowrap">${emailEscape(emailDate(email.created_at))}</td>
            <td class="text-nowrap">${emailEscape(emailDate(email.sent_at))}</td>
            <td>${emailBadge(SENT_COPY[email.sent_copy])}</td>
        </tr>`).join('') || '<tr><td colspan="6" class="text-center text-muted py-4">No hay correos con esos filtros.</td></tr>';
        emailPage = Number(response.current_page || 1);
        emailLastPage = Number(response.last_page || 1);
        document.getElementById('emailPager').classList.toggle('d-none', emailLastPage <= 1);
        document.getElementById('emailPagerText').textContent = `Página ${emailPage} de ${emailLastPage} · ${Number(response.total)} registros`;
        document.getElementById('emailPrev').disabled = emailPage <= 1;
        document.getElementById('emailNext').disabled = emailPage >= emailLastPage;
    } catch (error) {
        document.getElementById('mailerStatus').innerHTML = `<div class="alert alert-danger mb-0">${emailEscape(error.message)}</div>`;
        body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Sin datos.</td></tr>';
    }
}

let emailSearchTimer;
document.getElementById('emailFilters').addEventListener('submit', event => event.preventDefault());
document.getElementById('filterSearch').addEventListener('input', () => {
    clearTimeout(emailSearchTimer);
    emailSearchTimer = setTimeout(() => { emailPage = 1; loadEmails(); }, 350);
});
['filterStatus', 'filterCategory'].forEach(id => document.getElementById(id).addEventListener('change', () => { emailPage = 1; loadEmails(); }));
document.getElementById('emailPrev').addEventListener('click', () => { emailPage--; loadEmails(); });
document.getElementById('emailNext').addEventListener('click', () => { emailPage++; loadEmails(); });
document.addEventListener('DOMContentLoaded', () => { loadEmails(); setInterval(loadEmails, 30000); });
</script>
</body>
</html>
