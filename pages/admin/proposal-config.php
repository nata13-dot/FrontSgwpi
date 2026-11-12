<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !can_manage_projects()) { header('Location: /index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propuestas - <?= APP_NAME ?></title>
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
        <div class="container-xl mt-5 mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h1 class="mb-1">Propuestas</h1>
                    <p class="text-muted mb-0">Valida las propuestas para que pasen al resto del sistema y abre las ventanas de registro de la carrera.</p>
                </div>
                <button class="btn btn-primary" onclick="reloadActiveTab()" aria-label="Actualizar"><i class="bi bi-arrow-clockwise"></i></button>
            </div>

            <div class="nav nav-pills gap-2 mb-4" role="tablist">
                <button type="button" class="btn btn-primary" id="reviewTabButton" onclick="showTab('review')"><i class="bi bi-check2-square"></i> Validar</button>
                <button type="button" class="btn btn-outline-primary" id="settingsTabButton" onclick="showTab('settings')"><i class="bi bi-gear"></i> Validadores</button>
            </div>
            <section id="reviewTab">
                <div id="proposalFilters" class="d-flex flex-wrap gap-2 mb-3"></div>
                <div id="proposalList" class="row g-3"></div>
            </section>
            <section id="settingsTab" class="d-none">
                <div id="proposalSettings"></div>
            </section>
            </div>
        </div>
    </main>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>const API_BASE_URL = '<?= API_BASE_URL ?>';</script>
<script src="<?= asset('/assets/js/auth.js') ?>"></script>
<script src="<?= asset('/assets/js/api.js') ?>"></script>
<script src="<?= asset('/assets/js/app.js') ?>"></script>
<script src="<?= asset('/assets/js/proposals.js') ?>"></script>
<script>
let activeTab = 'review';
let settingsLoaded = false;
function showTab(tab) {
    activeTab = tab;
    ['review', 'settings'].forEach(name => {
        document.getElementById(`${name}Tab`).classList.toggle('d-none', name !== tab);
        document.getElementById(`${name}TabButton`).className = `btn ${name === tab ? 'btn-primary' : 'btn-outline-primary'}`;
    });
    if (tab === 'settings' && !settingsLoaded) { settingsLoaded = true; ProposalUI.loadSettings(); }
}
function reloadActiveTab() {
    activeTab === 'settings' ? ProposalUI.loadSettings() : ProposalUI.loadReview();
}
document.addEventListener('DOMContentLoaded', ProposalUI.loadReview);
window.onLiveChange?.(['proposals'], () => activeTab === 'settings' ? null : ProposalUI.loadReview());
</script>
</body>
</html>
