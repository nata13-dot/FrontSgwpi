<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
if (!is_authenticated() || !is_admin()) {
    header('Location: ' . dashboard_url());
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivo histórico - <?= APP_NAME ?></title>
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
                <span class="text-uppercase small fw-bold text-muted">Sistema</span>
                <h1 class="h2 mb-2">Archivo histórico</h1>
                <p class="text-muted mb-0">Lo archivado no aparece en el sistema hasta que lo restauras. Lo publicado en el repositorio público sigue disponible.</p>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3" role="tablist" aria-label="Tipo de registro" id="archiveTabs">
                <button type="button" class="btn btn-primary" data-type="usuarios"><i class="bi bi-people"></i> Usuarios</button>
                <button type="button" class="btn btn-outline-secondary" data-type="proyectos"><i class="bi bi-folder"></i> Proyectos</button>
                <button type="button" class="btn btn-outline-secondary" data-type="propuestas"><i class="bi bi-lightbulb"></i> Propuestas</button>
                <button type="button" class="btn btn-outline-secondary" data-type="grupos"><i class="bi bi-diagram-3"></i> Grupos</button>
            </div>

            <section class="card border-0 shadow-sm mb-3"><div class="card-body p-3 d-flex flex-wrap gap-2 align-items-center">
                <input class="form-control flex-grow-1" style="max-width:420px" id="archiveSearch" type="search" placeholder="Buscar por número, nombre o título" aria-label="Buscar">
                <button type="button" class="btn btn-primary d-none" id="archiveRestoreSelected"><i class="bi bi-arrow-counterclockwise"></i> Restaurar seleccionados</button>
            </div></section>

            <section class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th style="width:36px"><input class="form-check-input" type="checkbox" id="archiveAll" aria-label="Seleccionar página"></th><th>Registro</th><th>Archivado</th><th>Motivo</th><th></th></tr></thead>
                        <tbody id="archiveRows"><tr><td colspan="5" class="text-center text-muted py-4">Cargando...</td></tr></tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center p-3 d-none" id="archivePager">
                    <small class="text-muted" id="archivePagerText"></small>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary" type="button" id="archivePrev" aria-label="Anterior"><i class="bi bi-chevron-left"></i></button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" id="archiveNext" aria-label="Siguiente"><i class="bi bi-chevron-right"></i></button>
                    </div>
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
const archiveEscape = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
const archiveDate = value => value ? new Date(String(value).replace(' ', 'T')).toLocaleString('es-MX', {dateStyle: 'medium', timeStyle: 'short'}) : '—';
const ARCHIVE_NOUNS = {usuarios: ['usuario', 'usuarios'], proyectos: ['proyecto', 'proyectos'], propuestas: ['propuesta', 'propuestas'], grupos: ['grupo', 'grupos']};
let archiveType = 'usuarios';
let archivePage = 1;
let archiveLastPage = 1;
let archiveRows = [];
const archiveSelected = new Set();

function updateArchiveSelection() {
    const button = document.getElementById('archiveRestoreSelected');
    button.classList.toggle('d-none', archiveSelected.size === 0);
    button.innerHTML = `<i class="bi bi-arrow-counterclockwise"></i> Restaurar ${archiveSelected.size} seleccionado(s)`;
    document.getElementById('archiveAll').checked = archiveRows.length > 0 && archiveRows.every(row => archiveSelected.has(String(row.id)));
}

async function loadArchive() {
    const body = document.getElementById('archiveRows');
    const [, many] = ARCHIVE_NOUNS[archiveType];
    const params = {_fresh: true, tipo: archiveType, page: archivePage};
    const search = document.getElementById('archiveSearch').value.trim();
    if (search) params.buscar = search;
    try {
        const response = await api.get('/archive', params);
        archiveRows = response.data || [];
        body.innerHTML = archiveRows.map(row => `<tr>
            <td><input class="form-check-input" type="checkbox" data-id="${archiveEscape(row.id)}" ${archiveSelected.has(String(row.id)) ? 'checked' : ''} aria-label="Seleccionar"></td>
            <td style="white-space:normal;min-width:240px"><strong>${archiveEscape(row.name)}</strong><div class="small text-muted">${archiveEscape([archiveType === 'usuarios' ? row.id : null, row.detail].filter(Boolean).join(' · ') || '—')}</div></td>
            <td class="text-nowrap">${archiveEscape(archiveDate(row.archived_at))}${row.archived_by ? `<div class="small text-muted">por ${archiveEscape(row.archived_by)}</div>` : ''}</td>
            <td style="white-space:normal">${row.reason ? archiveEscape(row.reason) : '<span class="text-muted small">Sin motivo</span>'}</td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary" data-restore="${archiveEscape(row.id)}" title="Restaurar"><i class="bi bi-arrow-counterclockwise"></i></button></td>
        </tr>`).join('') || `<tr><td colspan="5" class="text-center text-muted py-4">Sin ${many} archivados.</td></tr>`;
        archivePage = Number(response.current_page || 1);
        archiveLastPage = Number(response.last_page || 1);
        document.getElementById('archivePager').classList.toggle('d-none', archiveLastPage <= 1);
        document.getElementById('archivePagerText').textContent = `Página ${archivePage} de ${archiveLastPage} · ${Number(response.total)} registros`;
        document.getElementById('archivePrev').disabled = archivePage <= 1;
        document.getElementById('archiveNext').disabled = archivePage >= archiveLastPage;
        updateArchiveSelection();
    } catch (error) {
        body.innerHTML = `<tr><td colspan="5"><div class="alert alert-danger mb-0">${archiveEscape(error.message)}</div></td></tr>`;
    }
}

async function restoreArchived(ids) {
    const [one, many] = ARCHIVE_NOUNS[archiveType];
    const text = ids.length === 1 ? `El ${one} volverá a aparecer en el sistema.` : `${ids.length} ${many} volverán a aparecer en el sistema.`;
    if (!(await confirmAction({title: 'Restaurar del archivo histórico', text, confirmButtonText: 'Sí, restaurar', icon: 'question'}))) return;
    try {
        const response = await api.post('/archive/restore', {tipo: archiveType, ids: ids.map(String)});
        swalToast('success', response.message);
        archiveSelected.clear();
        loadArchive();
    } catch (error) {
        Swal.fire('No se restauró', error.message, 'error');
    }
}

document.getElementById('archiveTabs').addEventListener('click', event => {
    const button = event.target.closest('[data-type]');
    if (!button || button.dataset.type === archiveType) return;
    archiveType = button.dataset.type;
    archivePage = 1;
    archiveSelected.clear();
    document.querySelectorAll('#archiveTabs [data-type]').forEach(item => {
        item.className = item.dataset.type === archiveType ? 'btn btn-primary' : 'btn btn-outline-secondary';
    });
    loadArchive();
});
document.getElementById('archiveRows').addEventListener('click', event => {
    const restore = event.target.closest('[data-restore]');
    if (restore) restoreArchived([restore.dataset.restore]);
});
document.getElementById('archiveRows').addEventListener('change', event => {
    const id = event.target.dataset.id;
    if (!id) return;
    if (event.target.checked) archiveSelected.add(id); else archiveSelected.delete(id);
    updateArchiveSelection();
});
document.getElementById('archiveAll').addEventListener('change', event => {
    archiveRows.forEach(row => event.target.checked ? archiveSelected.add(String(row.id)) : archiveSelected.delete(String(row.id)));
    loadArchive();
});
document.getElementById('archiveRestoreSelected').addEventListener('click', () => restoreArchived([...archiveSelected]));
let archiveSearchTimer;
document.getElementById('archiveSearch').addEventListener('input', () => {
    clearTimeout(archiveSearchTimer);
    archiveSearchTimer = setTimeout(() => { archivePage = 1; loadArchive(); }, 350);
});
document.getElementById('archivePrev').addEventListener('click', () => { archivePage--; loadArchive(); });
document.getElementById('archiveNext').addEventListener('click', () => { archivePage++; loadArchive(); });
document.addEventListener('DOMContentLoaded', loadArchive);
</script>
</body>
</html>
