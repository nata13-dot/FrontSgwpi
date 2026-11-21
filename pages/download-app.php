<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descargar la app - <?= APP_NAME ?></title>
    <meta name="description" content="Descarga la app de SGPI para Android: avisos, entregas y evaluaciones en tu teléfono.">
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
<div class="d-flex content-wrapper">
    <?php if (is_authenticated()) require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
    <main class="main-content flex-grow-1">
        <div class="container-xl py-4 py-lg-5 download-app">
            <div class="mb-4">
                <span class="text-uppercase small fw-bold text-muted">App para Android</span>
                <h1 class="h2 mb-2">Descarga la app de SGPI</h1>
                <p class="text-muted mb-0">Avisos al instante, entregas, evaluaciones y tu proyecto en el teléfono. Usa la misma cuenta que en la web.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <section class="card border-0 shadow-sm h-100"><div class="card-body p-4" id="appRelease">
                        <div class="text-center text-muted py-3">Consultando la versión disponible…</div>
                    </div></section>
                </div>
                <div class="col-lg-5">
                    <section class="card border-0 shadow-sm h-100"><div class="card-body p-4 text-center">
                        <h2 class="h5 mb-2">¿Estás en la computadora?</h2>
                        <p class="text-muted small mb-3">Escanea el código con la cámara de tu teléfono Android para abrir esta página.</p>
                        <div class="download-qr-frame mx-auto" data-no-glass>
                            <div id="appQr" class="download-qr" role="img" aria-label="Código QR para abrir esta página en el teléfono"></div>
                            <img src="/assets/img/ITSSMT/ITSSMT-192.webp" alt="" class="download-qr-logo">
                        </div>
                        <p class="download-qr-caption"><i class="bi bi-camera"></i> Apunta la cámara al código</p>
                        <p class="small text-muted mb-0 text-break" id="appPageUrl"></p>
                    </div></section>
                </div>
            </div>

            <section class="card border-0 shadow-sm mt-4"><div class="card-body p-4">
                <h2 class="h5 mb-3">Cómo instalarla</h2>
                <ol class="download-steps mb-3">
                    <li><strong>Descarga el archivo</strong> desde tu teléfono con el botón «Descargar».</li>
                    <li><strong>Ábrelo</strong> desde la notificación de descarga o desde la carpeta Descargas.</li>
                    <li>Si Android avisa que es de <strong>origen desconocido</strong>, toca «Configuración», permite instalar desde tu navegador y regresa. La app viene del instituto y no de Play Store; por eso lo pregunta.</li>
                    <li>Toca <strong>Instalar</strong>, abre SGPI e inicia sesión con tu cuenta.</li>
                </ol>
                <div class="alert alert-light border small mb-0">
                    <i class="bi bi-arrow-repeat"></i> <strong>Actualizaciones:</strong> ya instalada, la app te avisa cuando haya versión nueva y se actualiza desde ella misma; no necesitas volver a esta página.
                    <br><i class="bi bi-apple"></i> <strong>¿iPhone?</strong> Por ahora la app es sólo para Android; en iPhone usa SGPI desde el navegador, funciona igual.
                </div>
            </div></section>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>const API_BASE_URL = <?= json_encode(API_BASE_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script>
(async () => {
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const box = document.getElementById('appRelease');
    // El QR lleva a esta página (no al archivo): así quien lo escanea ve también las instrucciones.
    const pageUrl = `${location.origin}${location.pathname}`;
    document.getElementById('appPageUrl').textContent = pageUrl;
    try { new QRCode(document.getElementById('appQr'), { text: pageUrl, width: 400, height: 400, colorDark: '#0f1b2d', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H }); } catch (e) { document.getElementById('appQr').textContent = pageUrl; }

    const isIos = /iPhone|iPad|iPod/i.test(navigator.userAgent);
    try {
        const response = await fetch(`${API_BASE_URL}/app/latest`, { headers: { Accept: 'application/json' } });
        const release = response.ok ? (await response.json()).data : null;
        if (!release) {
            box.innerHTML = '<h2 class="h5">La app todavía no está disponible</h2><p class="text-muted mb-0">Mientras tanto, usa SGPI desde el navegador con tu cuenta.</p>';
            return;
        }
        const date = new Date(release.publicada_en).toLocaleDateString('es-MX', { dateStyle: 'long' });
        box.innerHTML = `
            <div class="d-flex align-items-center gap-3 mb-3">
                <img src="/assets/img/app-icon.png" alt="" width="56" height="56" class="rounded-3 download-icon" onerror="this.remove()">
                <div><h2 class="h4 mb-0">SGPI ${escapeHtml(release.version_name)}</h2>
                <small class="text-muted">Android 7 o superior · ${(release.tamano_bytes / 1024 / 1024).toFixed(1)} MB · publicada el ${escapeHtml(date)}</small></div>
            </div>
            ${isIos ? '<div class="alert alert-warning small">Estás en un iPhone: esta app es para Android. Usa SGPI desde el navegador.</div>' : ''}
            <a class="btn btn-primary btn-lg w-100" href="${escapeHtml(release.url)}" download><i class="bi bi-android2"></i> Descargar para Android</a>
            <details class="mt-3 small text-muted"><summary>Verificar el archivo</summary><p class="mb-0 mt-2 text-break">Huella SHA-256: <code>${escapeHtml(release.sha256)}</code></p></details>`;
    } catch (error) {
        box.innerHTML = '<p class="text-muted mb-0">No fue posible consultar la versión disponible. Intenta de nuevo en unos minutos.</p>';
    }
})();
</script>
</body>
</html>
