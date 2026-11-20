<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated()) { header('Location: /pages/login.php'); exit; }
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seguimiento de tesis - <?= APP_NAME ?></title>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/visual-preferences.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"><link rel="stylesheet" href="<?= asset('/assets/css/app.css') ?>">
</head><body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navbar.php'; ?>
<div class="d-flex content-wrapper"><?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>
<main class="main-content flex-grow-1"><div class="container-xl mt-5 mb-5">
<div class="mb-4"><h1 class="mb-1">Seguimiento de tesis</h1>
<p class="text-muted mb-0">Capítulos, entregas en PDF y revisión de cada docente.</p></div>
<div id="thesisConsole" aria-live="polite">Cargando…</div>
</div></main></div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script>const API_BASE_URL = <?= json_encode(API_BASE_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script><script src="<?= asset('/assets/js/api.js') ?>"></script><script src="<?= asset('/assets/js/app.js') ?>"></script>
<script type="module">
import { mountThesisConsole } from '<?= asset('/assets/js/thesis-console.js') ?>';
const root = document.getElementById('thesisConsole');
try {
    const response = await api.get('/auth/me', { _cache_ttl: 0 });
    mountThesisConsole(root, async (method, url, body) => {
        if (method === 'BLOB') {
            const response = await fetch(API_BASE_URL + url, { headers: { Authorization: `Bearer ${auth.getToken()}` } });
            if (!response.ok) throw new Error('No fue posible abrir el documento.');
            return await response.blob();
        }
        return api.request(method, url, body, { _cache_ttl: 0, _fresh: true, ...(body instanceof FormData ? { _timeout: 120000 } : {}) });
    }, response.user, { confirm: text => confirmAction({ title: 'Confirmar', text, confirmButtonText: 'Sí, continuar' }) });
} catch (error) { root.textContent = error.message || 'No fue posible cargar tu sesión.'; }
</script></body></html>
