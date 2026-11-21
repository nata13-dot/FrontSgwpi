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
    <title>Administradores generales - <?= APP_NAME ?></title>
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
                    <h1 class="h2 mb-2">Administradores generales</h1>
                    <p class="text-muted mb-0">Designa administradores generales de forma definitiva o temporal, cambia su vigencia o retíralos. No aparecen como jefatura ni personal de ninguna carrera.</p>
                </div>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#gaModal" data-ga-new><i class="bi bi-person-plus"></i> Designar administrador</button>
            </div>

            <section class="ga-stats mb-4" id="gaStats" aria-label="Resumen"></section>

            <section class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="people-filters" id="gaFilters"></div>
                        <input class="form-control form-control-sm people-filter-input" id="gaFilterText" placeholder="Buscar por nombre o número…">
                    </div>
                    <div id="gaList" class="ga-list"><div class="text-muted small">Cargando…</div></div>
                </div>
            </section>

            <section class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 mb-1"><i class="bi bi-clock-history"></i> Historial</h2>
                    <p class="text-muted small mb-3">Designaciones, cambios de vigencia, retiros y vencimientos más recientes.</p>
                    <ol class="ga-history" id="gaHistory"><li class="text-muted small">Cargando…</li></ol>
                </div>
            </section>
        </div>
    </main>
</div>

<div class="modal fade" id="gaModal" tabindex="-1" aria-labelledby="gaTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="gaForm" autocomplete="off">
            <div class="modal-header">
                <h2 class="modal-title h5" id="gaTitle">Designar administrador general</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="gaAlert"></div>
                <div class="mb-3" id="gaPersonBlock">
                    <label class="form-label" for="gaSearch">Persona</label>
                    <div class="people-add-search">
                        <div class="position-relative">
                            <i class="bi bi-search people-add-icon"></i>
                            <input class="form-control ps-5" id="gaSearch" placeholder="Nombre o número de empleado…" maxlength="100">
                            <div class="people-suggest d-none" id="gaSuggest" role="listbox"></div>
                        </div>
                        <div class="people-picked d-none" id="gaPicked"></div>
                    </div>
                    <small class="text-muted">Sólo personal (no alumnos). Al designarlo deja de verse como personal de su carrera.</small>
                </div>
                <label class="form-label">Vigencia</label>
                <div class="btn-group w-100 mb-2" role="group">
                    <input type="radio" class="btn-check" name="gaKind" id="gaKindForever" value="forever" checked>
                    <label class="btn btn-outline-primary" for="gaKindForever"><i class="bi bi-infinity"></i> Definitiva</label>
                    <input type="radio" class="btn-check" name="gaKind" id="gaKindTemp" value="temp">
                    <label class="btn btn-outline-primary" for="gaKindTemp"><i class="bi bi-hourglass-split"></i> Temporal</label>
                </div>
                <div class="d-none" id="gaTempBlock">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-ga-days="1">1 día</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-ga-days="7">1 semana</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-ga-days="30">1 mes</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-ga-days="180">6 meses</button>
                    </div>
                    <label class="form-label small" for="gaUntil">Termina el</label>
                    <input type="datetime-local" class="form-control" id="gaUntil">
                    <small class="text-muted">Al vencer, se retira sola, se cierran sus sesiones de la app y se le avisa.</small>
                </div>
                <div class="mt-3">
                    <label class="form-label" for="gaPassword">Tu contraseña</label>
                    <input type="password" class="form-control" id="gaPassword" required autocomplete="current-password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="gaSubmit">Designar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = <?= json_encode(API_BASE_URL, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script src="<?= asset('/assets/js/general-admins.js') ?>"></script>
</body>
</html>
