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
    <title>Respaldos de base de datos - <?= APP_NAME ?></title>
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
                    <span class="text-uppercase small fw-bold text-muted">Continuidad operativa</span>
                    <h1 class="h2 mb-2">Respaldos de base de datos</h1>
                    <p class="text-muted mb-0">Copias institucionales privadas, comprimidas y verificadas mediante SHA-256 y simulacros de restauración.</p>
                </div>
                <button class="btn btn-primary" id="createBackupButton" type="button">
                    <i class="bi bi-database-add"></i> Crear respaldo ahora
                </button>
            </div>

            <div class="alert alert-info border-0 shadow-sm mb-4">
                <i class="bi bi-clock-history me-2"></i>
                El sistema programa una copia diaria a la 01:00. Los archivos permanecen fuera del directorio público.
            </div>

            <section class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4" id="backupHealth">
                    <div class="text-center text-muted py-3">Revisando almacenamiento...</div>
                </div>
            </section>

            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 p-4">
                    <h2 class="h5 mb-1">Historial de respaldos</h2>
                    <p class="text-muted small mb-0">Se muestran las últimas 50 ejecuciones manuales, programadas o de consola.</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr><th>Fecha</th><th>Origen</th><th>Ejecutado por</th><th>Tamaño</th><th>SHA-256</th><th>Restauración</th><th>Estado</th><th></th></tr>
                        </thead>
                        <tbody id="backupHistory">
                            <tr><td colspan="8" class="text-center text-muted py-4">Cargando respaldos...</td></tr>
                        </tbody>
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
function backupEscape(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
}
function formatBackupSize(bytes) {
    const value = Number(bytes || 0);
    if (!value) return '—';
    const units = ['B', 'KB', 'MB', 'GB'];
    const unit = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1);
    return `${(value / Math.pow(1024, unit)).toFixed(unit ? 1 : 0)} ${units[unit]}`;
}
async function loadBackupHealth() {
    const container = document.getElementById('backupHealth');
    try {
        const health = await api.get('/admin/database-backups-health', {_fresh: true});
        const retentionDays = Number(health.retention_days || 30);
        container.innerHTML = `
            <div class="d-flex flex-wrap align-items-center gap-4">
                <span class="display-6 text-${health.healthy ? 'success' : 'danger'}"><i class="bi bi-${health.healthy ? 'device-ssd' : 'exclamation-triangle'}"></i></span>
                <div class="flex-grow-1">
                    <h2 class="h5 mb-2">Almacenamiento ${health.healthy ? 'saludable' : 'requiere revisión'}</h2>
                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span><strong>${Number(health.available)}</strong> disponibles</span>
                        <span><strong>${Number(health.verified)}</strong> restaurables</span>
                        <span><strong>${Number(health.missing)}</strong> faltantes</span>
                        <span><strong>${Number(health.altered)}</strong> alterados</span>
                        <span><strong>${formatBackupSize(health.total_bytes)}</strong> usados</span>
                        <span><strong>${formatBackupSize(health.disk_free_bytes)}</strong> libres</span>
                    </div>
                </div>
                <div class="text-end">
                    <div class="small text-muted mb-2">${Number(health.eligible_count)} copias anteriores a ${retentionDays} días elegibles</div>
                    <button class="btn btn-sm btn-outline-danger" type="button" ${Number(health.eligible_count) ? '' : 'disabled'} onclick="cleanupBackups(${retentionDays})">
                        <i class="bi bi-trash3"></i> Aplicar retención
                    </button>
                </div>
            </div>`;
    } catch (error) {
        container.innerHTML = `<div class="alert alert-danger mb-0">${backupEscape(error.message)}</div>`;
    }
}
function backupDate(value) {
    return value ? new Date(String(value).replace(' ', 'T')).toLocaleString('es-MX', {dateStyle: 'medium', timeStyle: 'short'}) : '—';
}
function backupNotice(type, title, text) {
    if (window.Swal) return stableSwalFire({icon: type, title, text});
    alert(`${title}\n${text || ''}`);
}
let backupRows = [];
async function loadBackups() {
    const body = document.getElementById('backupHistory');
    try {
        const response = await api.get('/admin/database-backups', {_fresh: true});
        backupRows = response.data || [];
        body.innerHTML = backupRows.map(backup => {
            const actor = [backup.actor_nombres, backup.actor_apellido].filter(Boolean).join(' ') || backup.creado_por || 'Sistema';
            const completed = backup.estado === 'completado';
            const checksum = backup.checksum_sha256 || '';
            const verification = backup.estado_verificacion === 'correcto'
                ? `<span class="badge text-bg-success">Restaurable</span><div class="small text-muted mt-1">${Number(backup.tablas_encontradas)} tablas · ${Number(backup.filas_verificadas).toLocaleString('es-MX')} filas<br>${backupEscape(backupDate(backup.verificado_en))}</div>`
                : (backup.estado_verificacion === 'fallido'
                    ? `<button class="btn btn-link btn-sm p-0 text-danger text-decoration-none" type="button" onclick="showBackupError(${Number(backup.id)}, 'verification')"><span class="badge text-bg-danger">Falló prueba</span> <i class="bi bi-info-circle"></i></button>`
                    : '<span class="badge text-bg-secondary">Sin verificar</span>');
            return `<tr>
                <td class="text-nowrap">${backupEscape(backupDate(backup.creado_en))}</td>
                <td><span class="badge text-bg-light text-capitalize">${backupEscape(backup.origen)}</span></td>
                <td>${backupEscape(actor)}</td>
                <td class="text-nowrap">${formatBackupSize(backup.tamano_bytes)}</td>
                <td><code title="${backupEscape(checksum)}">${backupEscape(checksum ? `${checksum.slice(0, 12)}…` : '—')}</code></td>
                <td>${verification}</td>
                <td><span class="badge text-bg-${completed ? 'success' : 'danger'}">${completed ? 'Completado' : 'Fallido'}</span></td>
                <td class="text-end"><div class="d-flex justify-content-end gap-2">${completed
                    ? `<button class="btn btn-sm btn-outline-success text-nowrap" type="button" onclick="verifyBackup(${Number(backup.id)}, this)"><i class="bi bi-shield-check"></i> Verificar</button>
                       <button class="btn btn-sm btn-outline-primary text-nowrap" type="button" onclick="downloadBackup(${Number(backup.id)}, this)"><i class="bi bi-download"></i> Descargar</button>`
                    : `<button class="btn btn-sm btn-outline-danger text-nowrap" type="button" onclick="showBackupError(${Number(backup.id)}, 'backup')"><i class="bi bi-exclamation-octagon"></i> Ver error</button>`}
                </div></td>
            </tr>`;
        }).join('') || '<tr><td colspan="8" class="text-center text-muted py-4">Aún no existen respaldos.</td></tr>';
    } catch (error) {
        body.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">${backupEscape(error.message)}</td></tr>`;
    }
}
function refreshBackupPage() {
    return Promise.all([loadBackups(), loadBackupHealth()]);
}
function showBackupError(id, kind) {
    const backup = backupRows.find(row => Number(row.id) === Number(id)) || {};
    const message = kind === 'verification' ? backup.error_verificacion : backup.mensaje_error;
    backupNotice('error', kind === 'verification' ? 'La prueba de restauración falló' : 'El respaldo no se generó', message || 'No se registró el detalle del error.');
}
async function createBackup() {
    const button = document.getElementById('createBackupButton');
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generando...';
    try {
        const response = await api.post('/admin/database-backups', {}, {_timeout: 300000});
        swalToast('success', `Respaldo generado (${formatBackupSize(response.backup?.tamano_bytes)}). Verifícalo para comprobar que se puede restaurar.`, 6000);
    } catch (error) {
        backupNotice('error', 'No se pudo generar el respaldo', error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-database-add"></i> Crear respaldo ahora';
        refreshBackupPage();
    }
}
async function verifyBackup(id, button) {
    button.disabled = true;
    const original = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Probando...';
    try {
        const response = await api.post(`/admin/database-backups/${id}/verify`, {}, {_timeout: 300000});
        const result = response.verification || {};
        swalToast('success', `Restaurable: ${Number(result.tablas_encontradas)} tablas esenciales y ${Number(result.filas_verificadas).toLocaleString('es-MX')} filas comprobadas.`, 6000);
    } catch (error) {
        backupNotice('error', 'La prueba de restauración falló', error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = original;
        refreshBackupPage();
    }
}
async function cleanupBackups(days) {
    const phrase = `DEPURAR RESPALDOS ANTERIORES A ${days} DIAS`;
    const confirmation = await promptText({
        title: 'Aplicar retención',
        inputPlaceholder: phrase,
        confirmButtonText: 'Depurar',
        inputValidator: value => value === phrase ? null : `Escribe exactamente: ${phrase}`
    });
    if (confirmation === null) return;
    try {
        const response = await api.post('/admin/database-backups-cleanup', {retention_days: days, confirmation});
        swalToast('success', `Retención aplicada: ${Number(response.result?.deleted || 0)} copias eliminadas, ${formatBackupSize(response.result?.released_bytes)} liberados.`, 6000);
        await refreshBackupPage();
    } catch (error) {
        backupNotice('error', 'No se aplicó la retención', error.message);
    }
}
async function downloadBackup(id, button) {
    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Descargando...';
    try {
        const backup = backupRows.find(row => Number(row.id) === Number(id)) || {};
        await downloadApiFile(`/admin/database-backups/${id}/download`, backup.nombre_archivo || `respaldo_${id}.sql.gz`, 'No se pudo descargar el respaldo.');
    } catch (error) {
        backupNotice('error', 'No se pudo descargar', error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = original;
    }
}
document.getElementById('createBackupButton').addEventListener('click', createBackup);
document.addEventListener('DOMContentLoaded', refreshBackupPage);
</script>
</body>
</html>
