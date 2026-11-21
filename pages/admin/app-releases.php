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
    <title>Versiones de la app - <?= APP_NAME ?></title>
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
                <h1 class="h2 mb-2">Versiones de la app</h1>
                <p class="text-muted mb-0">Publica aquí cada APK nuevo. Al abrirse, la app avisa que hay versión nueva, la descarga, comprueba que sea la publicada y abre el instalador de Android.</p>
            </div>

            <section class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
                <h2 class="h5 mb-3">Publicar versión</h2>
                <div id="publishAlert"></div>
                <form class="row g-3" id="publishForm">
                    <div class="col-md-6"><label class="form-label" for="apkFile">Archivo APK</label><input class="form-control" id="apkFile" type="file" accept=".apk,application/vnd.android.package-archive" required></div>
                    <div class="col-md-3"><label class="form-label" for="versionCode">Número de versión (versionCode)</label><input class="form-control" id="versionCode" type="number" min="1" required></div>
                    <div class="col-md-3"><label class="form-label" for="versionName">Nombre de versión</label><input class="form-control" id="versionName" maxlength="40" pattern="[0-9A-Za-z.\-]+" placeholder="1.1.59-beta" required></div>
                    <div class="col-12"><label class="form-label" for="notes">Novedades (las verá el usuario)</label><textarea class="form-control" id="notes" rows="3" maxlength="3000" placeholder="Ej. Seguimiento de tesis por capítulos."></textarea></div>
                    <div class="col-md-8"><div class="form-check"><input class="form-check-input" type="checkbox" id="mandatory"><label class="form-check-label" for="mandatory">Obligatoria: quien tenga una versión anterior no podrá seguir usando la app hasta actualizar.</label></div></div>
                    <div class="col-md-4 text-md-end"><button class="btn btn-primary" type="submit" id="publishButton"><i class="bi bi-cloud-upload"></i> Publicar</button></div>
                    <div class="col-12"><small class="text-muted">El número y el nombre deben coincidir con los del APK (android/app/build.gradle). Android sólo instala el APK si está firmado con la misma llave que la app instalada.</small></div>
                </form>
            </div></section>

            <section class="card border-0 shadow-sm"><div class="card-body p-4">
                <h2 class="h5 mb-1">Publicadas</h2>
                <p class="small text-muted mb-3"><i class="bi bi-shield-check"></i> Por seguridad sólo se conservan las últimas 3 versiones: al publicar una nueva, las anteriores se eliminan junto con su archivo.</p>
                <div id="releases"><div class="text-center text-muted py-2">Cargando…</div></div>
            </div></section>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = <?= json_encode(API_BASE_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script>
(() => {
    const list = document.getElementById('releases');
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const size = bytes => `${(bytes / 1024 / 1024).toFixed(1)} MB`;
    const when = value => new Date(value).toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' });

    async function load() {
        try {
            const { data } = await api.request('GET', '/admin/app-releases', null, { _cache_ttl: 0, _fresh: true });
            if (!data.length) { list.innerHTML = '<p class="text-muted mb-0">Aún no se publica ninguna versión.</p>'; return; }
            const newest = Math.max(...data.filter(v => v.activa).map(v => v.version_code), 0);
            list.innerHTML = `<div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Versión</th><th>Publicada</th><th>Tamaño</th><th>Descargas</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>${data.map(v => `<tr>
                    <td><strong>${escapeHtml(v.version_name)}</strong> <small class="text-muted">(${Number(v.version_code)})</small>
                        ${v.notas ? `<div class="small text-muted" style="white-space:pre-line">${escapeHtml(v.notas)}</div>` : ''}
                        <div class="small text-muted text-break">SHA-256 ${escapeHtml(v.sha256)}</div></td>
                    <td>${when(v.publicada_en)}</td><td>${size(v.tamano_bytes)}</td><td>${Number(v.descargas)}</td>
                    <td>${v.activa ? (v.version_code === newest ? '<span class="badge text-bg-success">Vigente</span>' : '<span class="badge text-bg-secondary">Anterior</span>') : '<span class="badge text-bg-warning">Retirada</span>'}
                        ${v.obligatoria ? ' <span class="badge text-bg-danger">Obligatoria</span>' : ''}</td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="${escapeHtml(v.url)}" download><i class="bi bi-download"></i></a>
                        <button class="btn btn-sm btn-outline-primary" data-action="mandatory" data-id="${Number(v.id)}" data-value="${v.obligatoria ? 0 : 1}">${v.obligatoria ? 'Hacer opcional' : 'Hacer obligatoria'}</button>
                        <button class="btn btn-sm btn-outline-secondary" data-action="active" data-id="${Number(v.id)}" data-value="${v.activa ? 0 : 1}">${v.activa ? 'Retirar' : 'Reactivar'}</button>
                        <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${Number(v.id)}"><i class="bi bi-trash"></i></button>
                    </td></tr>`).join('')}</tbody></table></div>`;
            const next = Math.max(...data.map(v => v.version_code)) + 1;
            const code = document.getElementById('versionCode');
            if (!code.value) code.placeholder = String(next);
        } catch (error) {
            list.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(error.message || 'No fue posible cargar las versiones.')}</div>`;
        }
    }

    list.addEventListener('click', async event => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const id = Number(button.dataset.id);
        try {
            if (button.dataset.action === 'delete') {
                const ok = await confirmAction({ title: 'Eliminar versión', text: 'Se borra el APK del servidor. Las apps ya no podrán descargarla.', confirmButtonText: 'Eliminar', danger: true });
                if (!ok) return;
                await api.request('DELETE', `/admin/app-releases/${id}`);
            } else {
                await api.request('PUT', `/admin/app-releases/${id}`, { [button.dataset.action === 'active' ? 'activa' : 'obligatoria']: button.dataset.value === '1' });
            }
            await load();
        } catch (error) { showAlert('#publishAlert', 'danger', error.message); }
    });

    document.getElementById('publishForm').addEventListener('submit', async event => {
        event.preventDefault();
        const file = document.getElementById('apkFile').files[0];
        if (!file) return;
        const body = new FormData();
        body.append('apk', file);
        body.append('version_code', document.getElementById('versionCode').value);
        body.append('version_name', document.getElementById('versionName').value.trim());
        body.append('notas', document.getElementById('notes').value.trim());
        body.append('obligatoria', document.getElementById('mandatory').checked ? '1' : '0');
        const button = document.getElementById('publishButton');
        button.disabled = true;
        try {
            const result = await api.request('POST', '/admin/app-releases', body, { _timeout: 300000 });
            showAlert('#publishAlert', 'success', result.message);
            event.target.reset();
            await load();
        } catch (error) {
            showAlert('#publishAlert', 'danger', error.message);
        } finally { button.disabled = false; }
    });

    load();
})();
</script>
</body>
</html>
