<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !is_management_staff()) {
    header('Location: ' . dashboard_url());
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operación del periodo - <?= APP_NAME ?></title>
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
                <span class="text-uppercase small fw-bold text-muted">Periodo</span>
                <h1 class="h2 mb-2">Operación del periodo</h1>
                <p class="text-muted mb-0">Las etapas de la carrera en orden: abre cada una para ver sus pendientes y controlarla.</p>
            </div>
            <div id="periodCenter" class="period-center" aria-live="polite">Cargando…</div>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = <?= json_encode(API_BASE_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script type="module">
import { mountPeriodCenter } from '<?= asset('/assets/js/period-center.js') ?>';
// El mismo módulo que la app (React_Swgpi/src/shared/periodCenter.js).
mountPeriodCenter(document.getElementById('periodCenter'), {
    api: (method, url, body) => api.request(method, url, body, { _cache_ttl: 0 }),
    go: path => { window.location.href = path; },
    confirm: text => confirmAction({ title: 'Confirmar', text, confirmButtonText: 'Sí, continuar' }),
    notify: (type, text) => swalToast(type === 'error' ? 'error' : type, text, type === 'error' ? 8000 : 3500),
    links: {
        period: '/pages/academic-workflows.php?tab=periods',
        requests: '/pages/admin/registration-requests.php',
        proposals: '/pages/admin/proposal-config.php',
        rooms: '/pages/admin/evaluation-rooms.php',
        evaluations: '/pages/admin/evaluations.php',
    },
});
</script>
</body>
</html>
