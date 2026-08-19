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
                        <thead><tr><th>Fecha</th><th>Carrera</th><th>Actor</th><th>Operación</th><th>Ruta</th><th>Resultado</th><th>IP</th></tr></thead>
                        <tbody id="auditRows"><tr><td colspan="7" class="text-center text-muted py-5">Cargando auditoría...</td></tr></tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="auditPageInfo"></small>
                    <div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" id="auditPrevious"><i class="bi bi-chevron-left"></i></button><button class="btn btn-outline-secondary" id="auditNext"><i class="bi bi-chevron-right"></i></button></div>
                </div>
            </section>
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
                <td><small>${auditEscape(item.direccion_ip || '-')}</small></td>
            </tr>`;
        }).join('') || '<tr><td colspan="7" class="text-center text-muted py-5">No hay operaciones para estos filtros.</td></tr>';
        auditPageInfo.textContent = `Página ${auditPage} de ${auditLastPage} · ${Number(response.total || 0)} registros`;
        auditPrevious.disabled = auditPage <= 1;
        auditNext.disabled = auditPage >= auditLastPage;
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
auditPrevious.addEventListener('click', () => { if (auditPage > 1) { auditPage--; loadAudit(); } });
auditNext.addEventListener('click', () => { if (auditPage < auditLastPage) { auditPage++; loadAudit(); } });
document.addEventListener('DOMContentLoaded', initializeAudit);
</script>
</body>
</html>
