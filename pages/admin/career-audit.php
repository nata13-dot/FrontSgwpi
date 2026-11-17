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
    <title>Auditoría institucional - <?= APP_NAME ?></title>
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
                    <h1 class="h2 mb-2">Auditoría multicarrera</h1>
                    <p class="text-muted mb-0">Consulta operaciones de escritura sin exponer contraseñas ni contenido de formularios.</p>
                </div>
                <span class="badge rounded-pill text-bg-primary px-3 py-2"><i class="bi bi-shield-lock"></i> Solo administrador general</span>
            </div>
            <div id="auditAlert"></div>
            <section class="card border-0 shadow-sm mb-4">
                <form class="card-body p-4 row g-3 align-items-end" id="auditFilters">
                    <div class="col-md-4 col-xl-2"><label class="form-label">Carrera</label><select class="form-select" id="auditCareer"><option value="">Todas</option></select></div>
                    <div class="col-md-4 col-xl-2"><label class="form-label">Actor</label><input class="form-control" id="auditActor" maxlength="20" placeholder="ID de usuario"></div>
                    <div class="col-md-4 col-xl-2"><label class="form-label">Método</label><select class="form-select" id="auditMethod"><option value="">Todos</option><option>POST</option><option>PUT</option><option>PATCH</option><option>DELETE</option></select></div>
                    <div class="col-md-4 col-xl-2"><label class="form-label">Estado HTTP</label><input class="form-control" id="auditStatus" type="number" min="100" max="599" placeholder="Ej. 200"></div>
                    <div class="col-md-4 col-xl-2"><label class="form-label">Desde</label><input class="form-control" id="auditFrom" type="date"></div>
                    <div class="col-md-4 col-xl-2"><label class="form-label">Hasta</label><input class="form-control" id="auditTo" type="date"></div>
                    <div class="col-12 text-end"><button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Aplicar filtros</button></div>
                </form>
            </section>
            <section class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Fecha</th><th>Carrera</th><th>Actor</th><th>Operación</th><th>Ruta</th><th>Resultado</th><th>IP y dispositivo</th></tr></thead>
                        <tbody id="auditRows"><tr><td colspan="7" class="text-center text-muted py-5">Cargando auditoría...</td></tr></tbody>
                    </table>
                </div>
                <div class="card-footer bg-white px-3 py-1" id="auditPager"></div>
            </section>
            <section class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">Retención de la auditoría</h2>
                            <p class="text-muted small mb-0">Cada noche (01:40, después del respaldo) los registros más antiguos que el plazo se copian a un archivo comprimido junto a los respaldos y se borran de aquí. El plazo se cambia en Operaciones → Política de continuidad.</p>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" type="button" id="auditPruneButton" disabled><i class="bi bi-archive"></i> Depurar ahora</button>
                    </div>
                    <div id="auditRetention" class="small text-muted">Cargando...</div>
                </div>
            </section>
            <section class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1">Así te ve el sistema</h2>
                    <p class="text-muted small mb-2">La IP con la que se registran tus cambios. Desde un teléfono con datos móviles es la IP pública que asigna la compañía; en Wi-Fi, la del módem de esa red.</p>
                    <div id="auditIpCheck" class="small text-muted">Cargando...</div>
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
let auditPage = 1;
let auditLastPage = 1;
function auditEscape(value) { return String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c])); }
function auditParams() {
    return {page: auditPage, per_page: 25, career_id: auditCareer.value, actor_id: auditActor.value.trim(), method: auditMethod.value, status: auditStatus.value, date_from: auditFrom.value, date_to: auditTo.value, _fresh: true};
}
async function loadAudit() {
    auditRows.innerHTML = '<tr><td colspan="7" class="text-center py-5">Cargando...</td></tr>';
    try {
        const response = await api.get('/admin/audit', auditParams());
        auditLastPage = Number(response.last_page || 1);
        auditPage = Number(response.current_page || 1);
        auditRows.innerHTML = (response.data || []).map(item => {
            const success = Number(item.estado_http) < 400;
            const actor = [item.actor_nombres, item.actor_apellido].filter(Boolean).join(' ');
            return `<tr>
                <td><small>${auditEscape(new Date(item.creado_en).toLocaleString('es-MX'))}</small></td>
                <td><span class="badge text-bg-light">${auditEscape(item.carrera_clave || 'Institucional')}</span></td>
                <td><strong>${auditEscape(actor || item.actor_id || 'Sistema')}</strong><small class="d-block text-muted">${auditEscape(item.actor_id || '')}</small></td>
                <td><span class="badge text-bg-secondary">${auditEscape(item.metodo)}</span></td>
                <td><code>${auditEscape(item.ruta)}</code></td>
                <td><span class="badge text-bg-${success ? 'success' : 'danger'}">${Number(item.estado_http)}</span></td>
                <td><strong class="small">${auditEscape(item.direccion_ip || '-')}</strong><small class="d-block text-muted">${auditEscape(item.dispositivo || '')}</small></td>
            </tr>`;
        }).join('') || '<tr><td colspan="7" class="text-center text-muted py-5">No hay operaciones para estos filtros.</td></tr>';
        SgpiPager.render('#auditPager', { current: auditPage, last: auditLastPage, total: response.total, from: response.from, to: response.to, noun: 'operaciones', onPage: (page) => { auditPage = page; loadAudit(); } });
    } catch (error) {
        auditRows.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-5">${auditEscape(error.message)}</td></tr>`;
    }
}
async function initializeAudit() {
    try {
        const response = await api.get('/admin/careers');
        auditCareer.innerHTML += (response.careers || []).map(career => `<option value="${Number(career.id)}">${auditEscape(career.clave)} · ${auditEscape(career.nombre_corto)}</option>`).join('');
    } catch (error) {}
    loadAudit();
}
auditFilters.addEventListener('submit', event => { event.preventDefault(); auditPage = 1; loadAudit(); });
let auditRetentionData = null;
async function loadAuditRetention() {
    try {
        auditRetentionData = await api.get('/admin/audit/retention', { _fresh: true });
        const preview = auditRetentionData.preview || {};
        const archives = auditRetentionData.archives || [];
        auditPruneButton.disabled = !preview.total;
        auditRetention.innerHTML = `
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge text-bg-info">Se conservan ${Number(preview.days || 0)} días</span>
                <span class="badge text-bg-${preview.total ? 'warning' : 'success'}">${preview.total ? `${Number(preview.total)} por depurar` : 'Nada por depurar'}</span>
            </div>
            ${archives.length ? `<ul class="list-group">${archives.map(archive => `
                <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <strong>${archive.status === 'completada' ? `${Number(archive.total)} registros hasta ${auditEscape(new Date(archive.cutoff).toLocaleDateString('es-MX'))}` : archive.status === 'fallida' ? 'Depuración fallida' : 'En proceso'}</strong>
                        <small class="d-block text-muted">${auditEscape(new Date(archive.created_at).toLocaleString('es-MX'))} · ${auditEscape(archive.source)}${archive.error ? ` · ${auditEscape(archive.error)}` : ''}</small>
                    </div>
                    ${archive.available ? `<button class="btn btn-sm btn-outline-primary" type="button" onclick="downloadAuditArchive(${Number(archive.id)})"><i class="bi bi-download"></i> Descargar copia</button>` : ''}
                </li>`).join('')}</ul>` : '<p class="mb-0">Todavía no se ha depurado la auditoría.</p>'}`;
    } catch (error) {
        auditRetention.innerHTML = `<span class="text-danger">${auditEscape(error.message)}</span>`;
    }
}
async function downloadAuditArchive(id) {
    const archive = (auditRetentionData?.archives || []).find(item => Number(item.id) === Number(id));
    try { await downloadApiFile(`/admin/audit/archives/${id}/download`, archive?.file || `auditoria_${id}.sql.gz`, 'No se pudo descargar la copia.'); }
    catch (error) { showAlert('#auditAlert', 'danger', error.message); }
}
auditPruneButton.addEventListener('click', async () => {
    const preview = auditRetentionData?.preview || {};
    const ok = await confirmAction({ title: 'Depurar auditoría ahora', text: `Se copiarán ${Number(preview.total || 0)} registros anteriores al ${new Date(preview.cutoff).toLocaleDateString('es-MX')} a un archivo junto a los respaldos y después se borrarán de la auditoría. Requiere un respaldo completo de los últimos 2 días.`, confirmButtonText: 'Copiar y depurar' });
    if (!ok) return;
    auditPruneButton.disabled = true;
    try {
        const response = await api.post('/admin/audit/retention/run', {}, { _timeout: 120000 });
        swalToast('success', response.message);
        loadAudit();
    } catch (error) {
        showAlert('#auditAlert', 'danger', error.message);
    }
    loadAuditRetention();
});
async function loadIpCheck() {
    try {
        const data = await api.get('/admin/audit/ip-check', { _fresh: true });
        auditIpCheck.innerHTML = `<span class="badge text-bg-info me-2">IP: ${auditEscape(data.ip || '-')}</span><span class="badge text-bg-light">${auditEscape(data.device || '')}</span>`;
    } catch (error) {
        auditIpCheck.innerHTML = `<span class="text-danger">${auditEscape(error.message)}</span>`;
    }
}
document.addEventListener('DOMContentLoaded', () => { initializeAudit(); loadAuditRetention(); loadIpCheck(); });
</script>
</body>
</html>
