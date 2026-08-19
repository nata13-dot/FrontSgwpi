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
    <title>Integridad multicarrera - <?= APP_NAME ?></title>
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
                    <span class="text-uppercase small fw-bold text-muted">Diagnóstico institucional</span>
                    <h1 class="h2 mb-2">Integridad multicarrera</h1>
                    <p class="text-muted mb-0">Detecta relaciones operativas que atraviesen carreras o configuraciones incompletas.</p>
                </div>
                <button class="btn btn-primary" id="runIntegrityButton" type="button"><i class="bi bi-arrow-clockwise"></i> Ejecutar diagnóstico</button>
            </div>
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4" id="integritySummary">
                    <div class="text-center text-muted py-4">Preparando diagnóstico...</div>
                </div>
            </section>
            <div class="row g-3" id="integrityChecks"></div>
            <section class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 p-4">
                    <h2 class="h5 mb-1"><i class="bi bi-clock-history"></i> Historial de ejecuciones</h2>
                    <p class="text-muted small mb-0">Últimos 30 diagnósticos manuales, programados o ejecutados por consola.</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Fecha</th><th>Origen</th><th>Ejecutado por</th><th>Verificaciones</th><th>Incidencias</th><th>Estado</th></tr></thead>
                        <tbody id="integrityHistory"><tr><td colspan="6" class="text-center text-muted py-4">Sin ejecuciones almacenadas.</td></tr></tbody>
                    </table>
                </div>
            </section>
            <div class="alert alert-light border mt-4">
                <i class="bi bi-terminal"></i> También puede ejecutarse desde servidor con <code>php artisan sgpi:check-multicareer</code>.
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
function integrityEscape(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
}
async function loadIntegrity(store = false) {
    const button = document.getElementById('runIntegrityButton');
    button.disabled = true;
    try {
        const report = store
            ? await api.post('/admin/integrity/run', {})
            : await api.get('/admin/integrity', {_fresh: true});
        document.getElementById('integritySummary').innerHTML = `
            <div class="d-flex flex-wrap align-items-center gap-4">
                <span class="display-5 text-${report.healthy ? 'success' : 'danger'}"><i class="bi bi-${report.healthy ? 'shield-check' : 'exclamation-triangle'}"></i></span>
                <div class="flex-grow-1">
                    <h2 class="h4 mb-1">${report.healthy ? 'Integridad correcta' : 'Se requiere revisión'}</h2>
                    <p class="text-muted mb-0">${Number(report.checks_passed || 0)} de ${Number(report.checks_total || 0)} verificaciones correctas · ${Number(report.violations || 0)} incidencias.</p>
                </div>
                <small class="text-muted">${integrityEscape(new Date(report.generated_at).toLocaleString('es-MX'))}</small>
            </div>`;
        document.getElementById('integrityChecks').innerHTML = (report.checks || []).map(check => `
            <div class="col-md-6 col-xl-4">
                <article class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                            <h3 class="h6 mb-0">${integrityEscape(check.name)}</h3>
                            <span class="badge text-bg-${check.status === 'ok' ? 'success' : 'danger'}">${check.status === 'ok' ? 'OK' : Number(check.count)}</span>
                        </div>
                        <p class="small text-muted mb-0">${integrityEscape(check.description)}</p>
                    </div>
                </article>
            </div>`).join('');
        if (store) await loadIntegrityHistory();
    } catch (error) {
        document.getElementById('integritySummary').innerHTML = `<div class="alert alert-danger mb-0">${integrityEscape(error.message)}</div>`;
    } finally {
        button.disabled = false;
    }
}
async function loadIntegrityHistory() {
    try {
        const response = await api.get('/admin/integrity/history', {_fresh: true});
        document.getElementById('integrityHistory').innerHTML = (response.data || []).map(run => {
            const actor = [run.actor_nombres, run.actor_apellido].filter(Boolean).join(' ') || run.ejecutado_por || 'Sistema';
            return `<tr>
                <td>${integrityEscape(new Date(run.creado_en).toLocaleString('es-MX'))}</td>
                <td><span class="badge text-bg-light">${integrityEscape(run.origen)}</span></td>
                <td>${integrityEscape(actor)}</td>
                <td>${Number(run.verificaciones_correctas)}/${Number(run.verificaciones_totales)}</td>
                <td>${Number(run.incidencias)}</td>
                <td><span class="badge text-bg-${run.saludable ? 'success' : 'danger'}">${run.saludable ? 'Correcto' : 'Revisar'}</span></td>
            </tr>`;
        }).join('') || '<tr><td colspan="6" class="text-center text-muted py-4">Sin ejecuciones almacenadas.</td></tr>';
    } catch (error) {
        document.getElementById('integrityHistory').innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${integrityEscape(error.message)}</td></tr>`;
    }
}
document.getElementById('runIntegrityButton').addEventListener('click', () => loadIntegrity(true));
document.addEventListener('DOMContentLoaded', () => { loadIntegrity(); loadIntegrityHistory(); });
</script>
</body>
</html>
